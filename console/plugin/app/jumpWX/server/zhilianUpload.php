<?php
    
    header("Content-type:application/json");
    
    // 获取远程文件URL
    if (!isset($_GET['imgurl'])) {
        exit(json_encode(['code' => -5, 'message' => '未提供线上图片文件URL']));
    }
    
    // 登录验证
    session_start();
    if(!isset($_SESSION["yinliubao"])) {
        
        // 未登录
        echo json_encode([
            'code' => 408,
            'msg' => '未登录，无法操作！'
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
    
    $fileUrl = trim($_GET['imgurl']);
    $filename = basename($fileUrl); // 从URL中获取文件名
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    // 允许的文件类型
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($ext, $allowed)) {
        exit(json_encode(['code' => -4, 'message' => '不支持的文件类型']));
    }
    
    // 下载远程文件到本地临时文件夹
    $tempDir = sys_get_temp_dir();
    $tempFilePath = $tempDir . '/' . uniqid() . '.' . $ext;
    
    $fileContent = file_get_contents($fileUrl);
    if ($fileContent === false) {
        exit(json_encode(['code' => -3, 'message' => '无法下载远程文件，下载失败！']));
    }
    
    file_put_contents($tempFilePath, $fileContent);
    
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
    
    // MIME类型映射
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
            'content' => new CURLFile($tempFilePath, $mime, basename($tempFilePath))
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
    
    // 处理返回结果
    if ($err) {
        echo json_encode(['code' => -2, 'message' => '上传失败', 'error' => $err]);
        exit;
    }
    
    $response = json_decode($result, true);
    
    if ($response['data']['message'] == '图片上传成功,合规鉴定通过') {
        $imgURL = 'https://mypics.zhaopin.com/' . $response['data']['url'];
        echo json_encode([
            'code' => 0,
            'message' => '上传成功',
            'image' => $imgURL
        ]);
    } else {
        echo json_encode([
            'code' => -1,
            'message' => '上传失败'
        ]);
    }
    
    // 删除本地临时文件
    @unlink($tempFilePath);
    
?>
