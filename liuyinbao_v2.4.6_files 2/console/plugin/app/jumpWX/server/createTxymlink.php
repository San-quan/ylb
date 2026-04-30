<?php
    
    header("Content-type:application/json");
    
    // 登录验证
    session_start();
    if(!isset($_SESSION["yinliubao"])) {
        
        // 未登录
        echo json_encode([
            'code' => 408,
            'msg' => '私域引流宝未登录，无法操作！'
        ]);
        exit;
    }
    
    // 下载远程图片并上传的函数
    function downloadAndUploadImage($fileUrl, $uploadUrl, $Cookie) {
        
        // 临时存储路径
        $tmpFile = sys_get_temp_dir() . '/temp.jpg';
    
        // 下载远程图片到本地临时文件
        if (file_put_contents($tmpFile, file_get_contents($fileUrl)) === false) {
            die("文件下载失败！");
        }
    
        // 初始化 CURL 会话
        $ch = curl_init();
    
        // 构建 POST 数据
        $postData = [
            'file' => new CURLFile($tmpFile, 'image/jpeg', 'image.jpg'),
            'length' => 1  // 其他字段
        ];
    
        // 可配置 header（根据需要自定义）
        $headers = getCurlHeaders($Cookie, 'file.ucode.qq.com');
    
        // 设置 CURL 参数
        curl_setopt_array($ch, [
            CURLOPT_URL => $uploadUrl,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false, // 防止证书问题
        ]);
    
        // 执行上传请求
        $response = curl_exec($ch);
    
        // 错误处理
        if (curl_errno($ch)) {
            echo '上传失败：' . curl_error($ch);
            return null; // 上传失败时返回 null
        }
    
        // 关闭 CURL
        curl_close($ch);
    
        return json_decode($response, true); // 返回解析后的响应
    }
    
    // 获取 CURL 请求的 Headers
    function getCurlHeaders($Cookie, $host) {
        return [
            'Cookie: ' . $Cookie,
            'Host: ' . $host,
            'Origin: https://qr.qq.com',
            'Referer: https://qr.qq.com/image',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'
        ];
    }
    
    // 上传二维码并获取结果
    function createQRCode($fileList, $Cookie) {
        
        // 创建二维码的 URL
        $url = 'https://api.ucode.qq.com/api/CreateQRCodes';
    
        // 构建 JSON 请求体
        $postData = [
            "ClientType" => 2,
            "IsBatch" => 0,
            "RandomMode" => false,
            "CodeType" => 5,
            "Dynamic" => 1,
            "TextList" => [],
            "FileList" => $fileList, // 从上一步获得的 Files 数组
            "BusinessCardList" => []
        ];
    
        // 将数组转为 JSON 字符串
        $jsonData = json_encode($postData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
        // 获取 Headers
        $headers = getCurlHeaders($Cookie, 'api.ucode.qq.com');
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Content-Length: ' . strlen($jsonData);
    
        // 初始化 CURL 会话
        $ch = curl_init();
    
        // 设置 CURL 参数
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $jsonData, // 使用 JSON 字符串
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
    
        // 执行请求
        $response = curl_exec($ch);
    
        // 错误处理
        if (curl_errno($ch)) {
            
            echo curl_error($ch);
        } else {
            
            // 验证生成结果
            if(json_decode($response)->Response->Error->Message == '请先注册登录后再操作' || json_decode($response)->Response->Error->Code == 'FailedOperation') {
                
                // 未登录
                echo json_encode([
                    'code' => -1,
                    'msg' => 'cookie失效，请重新授权新的cookie再试!'
                ]);
            }else{
                
                // 生成URL成功
                echo json_encode([
                    'code' => 0,
                    'msg' => 'SUCCESS',
                    'data' => json_decode($response)
                ]);
            }
        }
    
        // 关闭 CURL
        curl_close($ch);
    }
        
    // 主逻辑
    $fileUrl = trim($_GET['imgurl']);  // 远程图片URL
    $uploadUrl = 'https://file.ucode.qq.com/file/api/UploadFile';
    
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
                'msg' => 'Cookie缓存文件无数据'
            ]);
            exit;
        }
    } else {
        
        // 文件不存在
        echo json_encode([
            'code' => 404,
            'msg' => 'Cookie缓存文件不存在'
        ]);
        exit;
    }
    
    // 执行下载并上传图片
    $response = downloadAndUploadImage($fileUrl, $uploadUrl, $Cookie);
    
    // 获取状态码
    $RetCode = json_encode($response['Response']['Error']['Code']);
    
    // 验证Cookie是否失效
    if($RetCode == '"Login.InvalidSession"') {
        echo json_encode([
            'code' => 405,
            'msg' => 'Cookie失效，请重新授权Cookie'
        ]);
        exit;
    }
    
    // 验证图片是否上传成功
    if ($response && count($response['Response']['Files']) > 0) {
        
        // 上传成功
        // 获取图片文件并创建二维码
        createQRCode($response['Response']['Files'], $Cookie);
    } else {
        
        // 图片上传失败
        echo $response;
    }
    
    // 删除临时文件
    unlink(sys_get_temp_dir() . '/temp.jpg');

?>
