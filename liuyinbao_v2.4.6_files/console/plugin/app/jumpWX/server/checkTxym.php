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
    $filePath = './cookieFile/txymCookie.php';
    
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
    
    // 初始化 CURL
    $ch = curl_init();
    
    // 设置上传地址
    $url = 'https://file.ucode.qq.com/file/api/UploadFile';
    
    // 构建 POST 数据
    $postData = [
        'file' => '666',    // 文件字段
        'length' => 1       // 其他字段
    ];
    
    // 可配置 header（根据需要自定义）
    $headers = [
        'Cookie: ' . $Cookie,
        'Host: file.ucode.qq.com',
        'Origin: https://qr.qq.com',
        'Referer: https://qr.qq.com/image',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'
    ];
    
    // 设置 CURL 参数
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false, // 如果目标是 https，防止证书问题
    ]);
    
    // 执行请求
    $response = curl_exec($ch);
    
    // 检查错误
    if (curl_errno($ch)) {
        echo curl_error($ch);
    } else {
        if(json_decode($response)->Response->Error->Message == 'File required') {
            echo json_encode([
                'code' => 0,
                'msg' => '请求正常'
            ]);
        }else if(json_decode($response)->Response->Error->Message == '请先登录账号') {
            echo json_encode([
                'code' => -1,
                'msg' => 'Cookie失效，请先登录账号'
            ]);
        }else{
            echo json_encode([
                'code' => -1,
                'msg' => 'Cookie失效'
            ]);
        }
    }
    
    // 关闭 CURL
    curl_close($ch);