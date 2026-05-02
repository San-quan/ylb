<?php

    // 设置返回为 JSON 格式
    header('Content-Type: application/json');
    
    // 获取 GET 参数
    $appid = isset($_GET['appid']) ? $_GET['appid'] : '';
    
    // 判断参数是否为空
    if (empty($appid)) {
        echo json_encode(['error' => '缺少 appid 参数']);
        exit;
    }
    
    try {
        
        // 引入数据库配置
        include '../../../../Db.php';
        $pdo = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8",
            $config['db_user'],
            $config['db_pass']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // 查询语句
        $sql = "SELECT service_content, banner, bannerClickNum, bannerClickUrl FROM ylbPlugin_h5chatPages_qywxKfXcxConfig WHERE appid = :appid LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':appid', $appid);
        $stmt->execute();
    
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($result) {
    
            // 返回结果
            $result['code'] = 0;
            $result['msg'] = '加载成功';
            echo json_encode($result);
        } else {
            echo json_encode(['code' => -1, 'msg' => '无法从服务器获取到数据']);
        }
    } catch (PDOException $e) {
        echo json_encode(['code' => -2, 'msg' => $e->getMessage()]);
    }