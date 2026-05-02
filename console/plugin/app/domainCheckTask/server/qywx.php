<?php

// 获取Access Token
function getAccessToken($corpid, $corpsecret)
{
    $tokenFile = __DIR__.'/access_token_'.$corpid.'.php';

    // 读取缓存
    if (file_exists($tokenFile)) {

        $data = include $tokenFile;

        if (
            isset($data['access_token']) &&
            isset($data['expire_time']) &&
            $data['expire_time'] > time()
        ) {
            return $data['access_token'];
        }
    }

    // 请求新token
    $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$corpid}&corpsecret={$corpsecret}";
    $response = file_get_contents($url);
    $json = json_decode($response, true);

    $access_token = $json['access_token'] ?? '';

    if ($access_token) {

        $data = [
            'access_token' => $access_token,
            'expire_time' => time() + 7000
        ];

        // 写入PHP缓存文件
        $content = "<?php\nreturn " . var_export($data, true) . ";";

        file_put_contents($tokenFile, $content);
    }

    return $access_token;
}


// 发送企业微信消息
function sendApplicationMessage($accessToken, $postData)
{
    $url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token={$accessToken}";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}


// 引入数据库
include '../../../../Db.php';

try{

    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass']
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    

    // 获取任务ID
    $tasksId = intval($_GET['tasksId'] ?? 0);

    if(!$tasksId){
        echo json_encode(['error'=>'任务不存在']);
        exit;
    }


    // 查询任务信息
    $stmt = $pdo->prepare("
        SELECT actionUser 
        FROM ylbPlugin_domainCheckTasks 
        WHERE task_id = :task_id
        LIMIT 1
    ");

    $stmt->execute([
        'task_id' => $tasksId
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$row){
        echo json_encode(['error'=>'任务不存在']);
        exit;
    }

    // 获取当前任务用户
    $actionUser = $row['actionUser'];


    // 获取企业微信配置
    $stmt = $pdo->prepare("
        SELECT * FROM ylbPlugin_domainCheckTasks_notify 
        WHERE user_name = :user_name
        LIMIT 1
    ");

    $stmt->execute([
        'user_name' => $actionUser
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$row){
        echo json_encode(['error'=>'未配置企业微信']);
        exit;
    }
    
    // 获取到的数据库配置
    $corpid = $row['Appid'];
    $corpsecret = $row['AppSecret'];
    $agentid = $row['AgentId'];
    $touser = $row['toUser'];

    // 接收参数
    $content = trim($_GET['content'] ?? '');
    $msgtype = trim($_GET['msgtype'] ?? 'text');
    $title = trim($_GET['title'] ?? '域名检测通知');

    if (!$content) {
        echo json_encode(['error' => '请传递消息']);
        exit;
    }

    // 文本消息
    $postData_text = [
        'touser' => $touser,
        'msgtype' => 'text',
        'agentid' => $agentid,
        'text' => [
            'content' => $content
        ]
    ];


    // markdown消息
    $postData_markdown = [
        'touser' => $touser,
        'msgtype'=> 'markdown',
        'agentid' => intval($agentid),
        'markdown' => [
            'content' => "> **{$title}**\n>{$content}"
        ],
        'enable_duplicate_check' => 0,
        'duplicate_check_interval' => 1800
    ];


    // 根据类型选择
    $postData = $msgtype === 'markdown' ? $postData_markdown : $postData_text;


    // 获取token
    $accessToken = getAccessToken($corpid, $corpsecret);

    if(!$accessToken){
        echo json_encode(['error'=>'获取token失败']);
        exit;
    }


    // 发送消息
    $response = sendApplicationMessage($accessToken, $postData);

    echo $response;

}catch(PDOException $e){

    echo json_encode([
        'error'=>'数据库错误',
        'msg'=>$e->getMessage()
    ]);

}

?>