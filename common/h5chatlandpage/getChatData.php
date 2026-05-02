<?php

    // 设置响应的 Content-Type 为 JSON
    header('Content-Type: application/json');
    
    try {
        
        // 数据库配置
        include '../../console/Db.php';
        
        // 数据库连接配置
        $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
        $username = $config['db_user'];
        $password = $config['db_pass'];
    
        // 创建 PDO 实例
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    
        // 获取参数 key
        $page_key = $_GET['key'] ?? '';
    
        if (empty($page_key)) {
            echo json_encode(['code' => '1', 'msg' => '缺少 page_key 参数'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    
        // 查询数据
        $stmt = $pdo->prepare("SELECT * FROM ylbPlugin_h5chatPages WHERE page_key = :page_key");
        $stmt->execute(['page_key' => $page_key]);
        $page_data = $stmt->fetch();
    
        if (!$page_data) {
            echo json_encode(['code' => '1', 'msg' => '未找到对应数据'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 获取目标链接
        $stmt = $pdo->prepare("SELECT targetlink_id,targetlink,targetlink_yz,targetlink_pv FROM ylbPlugin_h5chatPages_targetlinks WHERE page_id = :page_id");
        $stmt->execute(['page_id' => $page_data['page_id']]);
        $targetlinks_data = $stmt->fetchAll();
        
        if(count($targetlinks_data) == 0) {
            
            // 未配置跳转链接，默认返回weixin://
            $jump_btn_link = 'weixin://';
        }else {
            
            // 根据模式获取 jump_btn_link
            if($page_data['targetlink_mode'] == 1) {
                
                // 随机模式
                $randomKey = array_rand($targetlinks_data); // 获取随机索引
                $randomItem = $targetlinks_data[$randomKey]; // 获取随机项
                
                // 更新显示次数 targetlink_pv
                $stmt = $pdo->prepare("UPDATE ylbPlugin_h5chatPages_targetlinks 
                           SET targetlink_pv = targetlink_pv + 1 
                           WHERE targetlink_id = :targetlink_id");
                $stmt->execute(['targetlink_id' => $randomItem['targetlink_id']]);
                
                // 返回随机选取的
                $jump_btn_link = $randomItem['targetlink'];
            }else {
                
                // 阈值模式
                $selected = null;
                $backup = null;
                
                foreach ($targetlinks_data as $item) {
                    
                    // 如果符合阈值
                    if ($item['targetlink_yz'] > $item['targetlink_pv']) {
                        $selected = $item;
                        $tid = $selected['targetlink_id'];
                        break;
                    }
                    
                    // 所有都不符合阈值，默认使用第一条记录
                    if ($backup === null && $item['targetlink_yz'] <= $item['targetlink_pv']) {
                        $backup = $item;
                        $tid = $backup['targetlink_id'];
                    }
                }
                
                // 更新显示次数 targetlink_pv
                $stmt = $pdo->prepare("UPDATE ylbPlugin_h5chatPages_targetlinks 
                           SET targetlink_pv = targetlink_pv + 1 
                           WHERE targetlink_id = :targetlink_id");
                $stmt->execute(['targetlink_id' => $tid]);
                
                // 返回根据阈值选取的
                $jump_btn_link = $selected['targetlink'] ?? $backup['targetlink'];
            }
        }
    
        // 构造返回数据
        $response = [
            "code" => "0",
            "msg" => "请求成功",
            "welcome_msg" => $page_data['welcome_msg'],
            "end_msg" => $page_data['end_msg'],
            "reject_msg" => $page_data['reject_msg'],
            "page_status" => $page_data['page_status'],
            "jump_btn_animation" => $page_data['jump_btn_animation'],
            "jump_btn_text" => $page_data['jump_btn_text'],
            "jump_btn_bg_color" => $page_data['jump_btn_bg_color'],
            "jump_btn_border_radius" => $page_data['jump_btn_border_radius'],
            "jump_btn_link" => $jump_btn_link,
            "page_banner" => $page_data['page_banner'],
            "customer_avatar" => $page_data['customer_avatar'],
            "my_avatar" => $page_data['my_avatar'],
            "questions" => json_decode($page_data['chatData'])->questions
        ];
    
        // 返回 JSON 数据
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        
        echo json_encode(['code' => '1', 'msg' => '数据库错误: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
?>