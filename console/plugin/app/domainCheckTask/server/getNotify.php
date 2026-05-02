<?php
    
header("Content-type:application/json");

session_start();

if(isset($_SESSION["yinliubao"])){

    $currentUser = $_SESSION["yinliubao"];

    include '../../../../Db.php';

    try {

        $pdo = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
            $config['db_user'],
            $config['db_pass']
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        // ================================
        // 如果表不存在就创建
        // ================================
        $pdo->exec("
        CREATE TABLE IF NOT EXISTS `ylbPlugin_domainCheckTasks_notify` (
          `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
          `user_name` varchar(32) DEFAULT NULL COMMENT '用户',
          `Appid` varchar(255) DEFAULT NULL COMMENT '企业微信Appid',
          `AppSecret` text COMMENT '企业微信AppSecret',
          `AgentId` varchar(32) DEFAULT NULL COMMENT '企业微信AgentId',
          `toUser` varchar(255) DEFAULT NULL COMMENT '企业微信toUser',
          `BarkUrl` varchar(255) DEFAULT NULL COMMENT 'BarkURL',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='域名检测任务插件通知渠道配置';
        ");


        // ================================
        // 查询当前用户配置
        // ================================
        $stmt = $pdo->prepare("
            SELECT * 
            FROM ylbPlugin_domainCheckTasks_notify 
            WHERE user_name = :user_name 
            LIMIT 1
        ");

        $stmt->execute([
            'user_name' => $currentUser
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);


        if($row){

            $result = [
                'code' => 200,
                'msg' => '获取成功',
                'data' => $row
            ];

        }else{

            // 不存在则创建
            $stmt = $pdo->prepare("
                INSERT INTO ylbPlugin_domainCheckTasks_notify (user_name) 
                VALUES (:user_name)
            ");

            $stmt->execute([
                'user_name' => $currentUser
            ]);

            // 再读取
            $stmt = $pdo->prepare("
                SELECT * 
                FROM ylbPlugin_domainCheckTasks_notify 
                WHERE user_name = :user_name 
                LIMIT 1
            ");

            $stmt->execute([
                'user_name' => $currentUser
            ]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $result = [
                'code' => 200,
                'msg' => '已创建记录',
                'data' => $row
            ];
        }

    }catch(PDOException $e){

        $result = [
            'code' => 500,
            'msg' => '数据库错误',
            'error' => $e->getMessage()
        ];

    }

}else{

    $result = [
        'code' => 201,
        'msg' => '未登录'
    ];

}

echo json_encode($result,JSON_UNESCAPED_UNICODE);

?>