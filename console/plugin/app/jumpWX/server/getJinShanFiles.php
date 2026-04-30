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
    
    // 设置Cooie缓存文件路径
    $filePath = './cookieFile/jinshanCookie.php';
    
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
    
    $url = "https://drive.kdocs.cn/api/v5/groups/special/files?linkgroup=true&include=pic_thumbnail&with_link=true&review_pic_thumbnail=true&with_sharefolder_type=true&offset=0&count=100&order=DESC&orderby=mtime&exclude_exts=&include_exts=";
    
    // 初始化 cURL
    $ch = curl_init();
    
    // 设置 cURL 选项
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 返回响应内容
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 禁用 SSL 证书验证（仅用于测试环境）
    
    // 设置 Cookie 头部
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Cookie: " . $Cookie
    ));
    
    // 执行请求
    $response = curl_exec($ch);
    
    // 检查是否有错误发生
    if (curl_errno($ch)) {
        echo json_encode(
            array(
                'code' => -1,
                'msg' => curl_error($ch)
            )
        );
    } else {
        if(json_decode($response)->result == 'ok') {
            
            // 接口请求正常
            echo json_encode(
                array(
                    'code' => 0,
                    'msg' => 'success',
                    'data' => json_decode($response)
                )
            );
        }else {
            
            // 异常
            echo json_encode(
                array(
                    'code' => -2,
                    'msg' => json_decode($response)->msg
                )
            );
        }
        
    }
    
    // 关闭 cURL 资源
    curl_close($ch);
?>