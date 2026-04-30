<?php
    
    header("Content-type:application/json");
    
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
    
    // 获取参数
    $cookie = trim($_GET['cookie']);
    $platform = trim($_GET['platform']);
    
    if ($cookie && $platform) {
        
        // 生成一个 PHP 数组
        $data = [
            'cookie' => $cookie,
            'platform' => $platform,
        ];
    
        // 将数组转换为 PHP 可执行代码的格式
        $phpCode = '<?php return ' . var_export($data, true) . ';';
    
        // 定义文件路径
        $filePath = './cookieFile/' . $platform . 'Cookie.php';
    
        // 将生成的 PHP 代码保存到文件
        file_put_contents($filePath, $phpCode);
    
        // 检查文件是否存在
        if (file_exists($filePath)) {
            
            // 读取文件内容
            $fileContent = file_get_contents($filePath);
    
            // 生成的文件代码
            $expectedCode = '<?php return ' . var_export($data, true) . ';';
    
            // 比较文件内容与预期的代码
            if ($fileContent === $expectedCode) {
                echo json_encode(['code' => 0, 'msg' => 'Cookie设置成功']);
            } else {
                echo json_encode(['code' => -1, 'msg' => 'Cookie设置失败，未能写入本地缓存文件！']);
            }
        } else {
            echo json_encode(['code' => -2, 'msg' => 'Cookie设置失败，未能生成缓存文件！']);
        }
    }else {
        echo json_encode(['code' => -3, 'msg' => '请提交Cookie']);
    }
?>