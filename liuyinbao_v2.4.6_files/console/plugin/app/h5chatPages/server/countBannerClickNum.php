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
    
        // 查询语句：查询当前的 bannerClickNum（点击次数）
        $sql = "SELECT bannerClickNum FROM ylbPlugin_h5chatPages_qywxKfXcxConfig WHERE appid = :appid LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':appid', $appid);
        $stmt->execute();
    
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($result) {
            // 增加点击次数
            $newClickNum = $result['bannerClickNum'] + 1;
    
            // 更新点击次数到数据库
            $updateSql = "UPDATE ylbPlugin_h5chatPages_qywxKfXcxConfig SET bannerClickNum = :newClickNum WHERE appid = :appid";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->bindParam(':newClickNum', $newClickNum);
            $updateStmt->bindParam(':appid', $appid);
            $updateStmt->execute();
    
            // 返回更新后的结果
            $response = [
                'bannerClickNum' => $newClickNum,
                'code' => 0,
                'msg' => '点击记录成功'
            ];
            echo json_encode($response);
        } else {
            // 如果没有找到相关记录
            echo json_encode(['code' => -1, 'msg' => '未找到记录']);
        }
    } catch (PDOException $e) {
        // 捕获数据库错误
        echo json_encode(['error' => '数据库错误: ' . $e->getMessage(), 'code' => -2, 'msg' => $e->getMessage()]);
    }
