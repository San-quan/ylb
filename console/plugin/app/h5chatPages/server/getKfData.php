<?php

    // 设置返回为 JSON 格式
    header('Content-Type: application/json');
    
    // 获取 GET 参数
    $appid = isset($_GET['appid']) ? $_GET['appid'] : '';
    
    if (empty($appid)) {
        echo json_encode(['code' => -1, 'msg' => '缺少 appid 参数']);
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
    
        // 查询记录
        $sql = "SELECT kfQrUrl, kfQrPagePv, KfQrBtnClickCount, kfQrBgimg 
                FROM ylbPlugin_h5chatPages_qywxKfXcxConfig 
                WHERE appid = :appid 
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':appid', $appid);
        $stmt->execute();
    
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($result && !empty($result['kfQrUrl'])) {

            $updateSql = "UPDATE ylbPlugin_h5chatPages_qywxKfXcxConfig 
                          SET kfQrPagePv = kfQrPagePv + 1 
                          WHERE appid = :appid";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->bindParam(':appid', $appid);
            $updateStmt->execute();
    
            // 返回内容
            echo json_encode([
                'code' => 0,
                'msg' => '获取成功',
                'urls' => [$result['kfQrUrl']],
                'KfQrBtnText' => '点击联系我',
                'kfQrBgimg' => $result['kfQrBgimg']
            ]);
        } else {
            echo json_encode([
                'code' => -1,
                'msg' => '未找到记录或二维码链接为空'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'code' => -2,
            'msg' => '数据库错误',
            'error' => $e->getMessage()
        ]);
    }
