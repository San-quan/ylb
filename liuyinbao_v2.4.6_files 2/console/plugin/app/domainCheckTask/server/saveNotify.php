<?php
    
header("Content-type:application/json");

session_start();

if(isset($_SESSION["yinliubao"])){

    $currentUser = $_SESSION["yinliubao"];
    
    // 接收参数
    $Appid = trim($_POST['Appid'] ?? '');
    $AppSecret = trim($_POST['AppSecret'] ?? '');
    $AgentId = trim($_POST['AgentId'] ?? '');
    $toUser = trim($_POST['toUser'] ?? '');
    $BarkUrl = trim($_POST['BarkUrl'] ?? '');

    // 判断企业微信参数
    $qywx_fields = [$Appid,$AppSecret,$AgentId,$toUser];
    $filled = 0;

    foreach($qywx_fields as $v){
        if($v !== ''){
            $filled++;
        }
    }

    // 如果填写了部分但没填全
    if($filled > 0 && $filled < 4){
        echo json_encode([
            'code'=>400,
            'msg'=>'企业微信通知参数必须全部填写'
        ],JSON_UNESCAPED_UNICODE);
        exit;
    }

    // BarkUrl 简单过滤
    if($BarkUrl && !filter_var($BarkUrl, FILTER_VALIDATE_URL)){
        echo json_encode([
            'code'=>400,
            'msg'=>'BarkUrl 格式不正确'
        ],JSON_UNESCAPED_UNICODE);
        exit;
    }

    include '../../../../Db.php';

    try {

        $pdo = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
            $config['db_user'],
            $config['db_pass']
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 更新数据
        $stmt = $pdo->prepare("
            UPDATE ylbPlugin_domainCheckTasks_notify
            SET 
                Appid = :Appid,
                AppSecret = :AppSecret,
                AgentId = :AgentId,
                toUser = :toUser,
                BarkUrl = :BarkUrl
            WHERE user_name = :user_name
        ");

        $stmt->execute([
            'Appid'=>$Appid,
            'AppSecret'=>$AppSecret,
            'AgentId'=>$AgentId,
            'toUser'=>$toUser,
            'BarkUrl'=>$BarkUrl,
            'user_name'=>$currentUser
        ]);

        $result = [
            'code'=>200,
            'msg'=>'保存成功'
        ];

    }catch(PDOException $e){

        $result = [
            'code'=>500,
            'msg'=>'数据库错误',
            'error'=>$e->getMessage()
        ];

    }

}else{

    $result = [
        'code'=>201,
        'msg'=>'未登录'
    ];

}

echo json_encode($result,JSON_UNESCAPED_UNICODE);

?>