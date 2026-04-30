<?php

header("Content-type:application/json");

session_start();

if(isset($_SESSION["yinliubao"])){

    $domainOrLinks = trim($_POST['domainOrLinks'] ?? '');
    $otherdomainOrLinks = trim($_POST['otherdomainOrLinks'] ?? '');
    $notiType = trim($_POST['notiType'] ?? '');
    $actionUser = $_SESSION["yinliubao"];

    if(!$domainOrLinks && $domainOrLinks !== 'customize'){

        $result = [
            'code'=>203,
            'msg'=>'请选择要检测的域名'
        ];

    }else if($domainOrLinks == 'customize' && !$otherdomainOrLinks){

        $result = [
            'code'=>203,
            'msg'=>'请填写你要检测的域名或链接'
        ];

    }else if(!$notiType){

        $result = [
            'code'=>203,
            'msg'=>'请选择通知方式'
        ];

    }else{

        include '../../../../Db.php';

        try{

            $pdo = new PDO(
                "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
                $config['db_user'],
                $config['db_pass']
            );

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // 检查是否存在通知配置表
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM information_schema.tables 
                WHERE table_schema = :dbname 
                AND table_name = 'ylbPlugin_domainCheckTasks_notify'
            ");
            
            $stmt->execute([
                'dbname' => $config['db_name']
            ]);
            $exists = $stmt->fetchColumn();
            if(!$exists){
            
                // 表不存在
                // 创建表
                $pdo->exec("
                    CREATE TABLE `ylbPlugin_domainCheckTasks_notify` (
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
            }
            
            // 获取通知渠道
            $stmt = $pdo->prepare("
                SELECT Appid, BarkUrl 
                FROM ylbPlugin_domainCheckTasks_notify 
                WHERE user_name = :user_name 
                LIMIT 1
            ");
            
            $stmt->execute([
                'user_name' => $actionUser
            ]);
            
            $notify = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $Appid = $notify['Appid'] ?? '';
            $BarkUrl = $notify['BarkUrl'] ?? '';
            
            if((empty($BarkUrl) || !isset($BarkUrl)) && $notiType == 'bark'){
                echo json_encode(['code'=>201,'msg'=>'bark通知渠道未配置'],JSON_UNESCAPED_UNICODE);
                exit;
            }

            if((empty($Appid) || !isset($Appid)) && $notiType == 'qywx'){
                echo json_encode(['code'=>201,'msg'=>'企业微信通知渠道未配置'],JSON_UNESCAPED_UNICODE);
                exit;
            }

            // 生成任务ID
            $task_id = '10'.mt_rand(100000,999999);

            // 检测ID是否存在
            $stmt = $pdo->prepare("SELECT id FROM ylbPlugin_domainCheckTasks WHERE task_id=? LIMIT 1");
            $stmt->execute([$task_id]);

            if($stmt->fetch()){
                $task_id = '10'.mt_rand(123456,989796);
            }

            // 任务对象
            if($domainOrLinks && $domainOrLinks !== 'customize'){
                $task_object = $domainOrLinks;
            }else{
                $task_object = $otherdomainOrLinks;
            }

            // URL校验
            if(filter_var($task_object,FILTER_VALIDATE_URL) == false){
                echo json_encode([
                    'code'=>201,
                    'msg'=>'域名或链接不规范，需要以 http:// 或 https:// 开头'
                ],JSON_UNESCAPED_UNICODE);
                exit;
            }

            // 检查是否已存在
            $stmt = $pdo->prepare("SELECT id FROM ylbPlugin_domainCheckTasks WHERE task_object=? LIMIT 1");
            $stmt->execute([$task_object]);

            if($stmt->fetch()){
                echo json_encode([
                    'code'=>201,
                    'msg'=>'当前域名或链接已在检测任务中'
                ],JSON_UNESCAPED_UNICODE);
                exit;
            }

            // 生成检测链接
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            $requestUri = $_SERVER['REQUEST_URI'];
            $currentUrl = $protocol.$host.$requestUri;

            $check_link = dirname(dirname($currentUrl)).'/check/index.php?id='.$task_id;

            // 插入任务
            $stmt = $pdo->prepare("
                INSERT INTO ylbPlugin_domainCheckTasks
                (task_id,wx_check_result,task_object,check_link,noti_type,actionUser)
                VALUES
                (:task_id,'-1',:task_object,:check_link,:noti_type,:actionUser)
            ");

            $createTask = $stmt->execute([
                'task_id'=>$task_id,
                'task_object'=>$task_object,
                'check_link'=>$check_link,
                'noti_type'=>$notiType,
                'actionUser'=>$actionUser
            ]);

            if($createTask){

                $result = [
                    'code'=>200,
                    'msg'=>'创建成功'
                ];

            }else{

                $result = [
                    'code'=>202,
                    'msg'=>'创建失败'
                ];
            }

        }catch(PDOException $e){

            $result = [
                'code'=>500,
                'msg'=>'数据库错误',
                'error'=>$e->getMessage()
            ];
        }
    }

}else{

    $result = [
        'code'=>201,
        'msg'=>'未登录'
    ];
}

echo json_encode($result,JSON_UNESCAPED_UNICODE);

?>