<?php
    
    // 用来检测Cookie是否有效的
    
    // 返回JSON
    header("Content-type:application/json");
    
    // 登录验证
    session_start();
    if(!isset($_SESSION["yinliubao"])) {
        
        // 未登录
        echo json_encode([
            'code' => -3,
            'msg' => '私域引流宝未登录，无法操作！'
        ]);
        exit;
    }
    
    // --------------------------------------------------------------------
    // 设置Cooie缓存文件路径
    $filePath = './cookieFile/zhaopinCookie.php';
    
    // 检查文件是否存在
    if (file_exists($filePath)) {
        
        // 加载 PHP 文件并获取返回的数组
        $config = include($filePath);
    
        // 直接获取 'cookie' 的值
        if (isset($config['cookie'])) {
            
            // 获取成功
            $Cookie = $config['cookie'];
        } else {
            
            // 无数据
            echo json_encode([
                'code' => 405,
                'msg' => 'Cookie缓存文件无数据，请重新授权。'
            ]);
            exit;
        }
    } else {
        
        // 文件不存在
        echo json_encode([
            'code' => 404,
            'msg' => 'Cookie缓存文件不存在，请重新授权。'
        ]);
        exit;
    }
    // --------------------------------------------------------------------------
    
    // 生成上传参数
    function randomHex($len = 32) {
        return bin2hex(random_bytes($len / 2));
    }
    function uuidv4() {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    $at = randomHex(32);
    $rt = randomHex(32);
    $requestId = uuidv4() . '-' . (time() * 1000) . '-' . rand(10000, 99999);
    $clientId = uuidv4();
    
    $url = "https://fe-api.zhaopin.com/c/i/attachment/upload?at={$at}&rt={$rt}&x-zp-page-request-id={$requestId}&x-zp-client-id={$clientId}";
    
    // MIME
    $mimeTypes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
    ];
    $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => [
            'content' => '666'
        ],
        CURLOPT_HTTPHEADER => [
            'origin: https://i.zhaopin.com',
            'referer: https://i.zhaopin.com/resume',
            'user-agent: Mozilla/5.0',
            'cookie: ' . $Cookie
        ]
    ]);
    
    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if(json_decode($result)->data->message == '未登录，已终止上传') {
        
        // Cookie失效
        echo json_encode([
            'code' => -1,
            'msg' => 'Cookie失效'
        ]);
    }else {
        
        // 请求正常
        echo json_encode([
            'code' => 0,
            'msg' => '请求正常'
        ]);
    }