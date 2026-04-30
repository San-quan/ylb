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
    
    // 定义常量
    define('USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36');
    define('HOST', 'nc.cli.im');
    define('ORIGIN', 'https://console.cli.im');
    define('REFERER', 'https://console.cli.im/active/activecode');
    
    // 请求的URL
    $url = 'https://nc.cli.im/scene/getMenuList';
    
    // POST 表单数据
    $postData = ['module_id' => 1];
    
    // 初始化 cURL 会话
    $ch = curl_init();
    
    // 设置公共的 cURL 请求参数
    function setCurlOptions($ch, $url, $postData, $cookie) {
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Cookie: ' . $cookie,
                'Host: ' . HOST,
                'Origin: ' . ORIGIN,
                'Referer: ' . REFERER,
                'User-Agent: ' . USER_AGENT,
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
    }
    
    // 设置Cooie缓存文件路径
    $filePath = './cookieFile/caoliaoCookie.php';
    
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
    
    // 执行第一个请求
    setCurlOptions($ch, $url, $postData, $Cookie);
    $response = curl_exec($ch);
    
    // 检查错误
    if (curl_errno($ch)) {
        
        // cURL错误
        echo json_encode([
            'code' => 500,
            'msg' => 'getMenuList接口请求错误，请检查接口，原因：' . curl_error($ch)
        ]);
    } else {
        $responseData = json_decode($response, true);
        
        if ($responseData && isset($responseData['code']) && $responseData['code'] == 1) {
            $getAll = $responseData['data']['menu_list'][0];
            $category_root_id = $getAll['category_root_id'];
            $org_module_id = $getAll['org_module_id'];
    
            // 第二个请求 URL
            $url2 = 'https://nc.cli.im/qrcode/getQrcodeList';
    
            // POST 表单数据
            $postData2 = [
                'category_converge' => 0,
                'category_id' => $category_root_id,
                'only_qrcode' => 0,
                'outside_option' => 0,
                'page' => 1,
                'page_size' => 50,
                'qrcode_disable_status' => '',
                'sort' => 0,
                'state_id' => 0,
                'state_option_id' => 0,
                'tag_id' => 0,
            ];
    
            // 执行第二个请求
            setCurlOptions($ch, $url2, $postData2, $Cookie);
            $response2 = curl_exec($ch);
    
            // 检查第二个请求
            if (curl_errno($ch)) {
                
                // cURL错误
                echo json_encode([
                    'code' => 500,
                    'msg' => 'getQrcodeList接口请求错误，请检查接口，原因：' . curl_error($ch)
                ]);
            } else {
                $responseData2 = json_decode($response2, true);
                
                if ($responseData2 && isset($responseData2['code']) && $responseData2['code'] == 1) {
                    
                    // 获取二维码列表成功
                    $qrcodeList = $responseData2['data']['list'];
    
                    // 筛选出 type 为 'qrcode' 的对象
                    $qrcodeData = array_filter($qrcodeList, function ($item) {
                        return isset($item['type']) && $item['type'] === 'qrcode';
                    });
    
                    // 将筛选结果索引重置
                    $qrcodeData = array_values($qrcodeData);
    
                    // 只保留 web_url 和 list_name 字段
                    $resultQr = array_map(function($item) {
                        return [
                            'web_url' => $item['web_url'],
                            'list_name' => $item['list_name']
                        ];
                    }, $qrcodeData);
    
                    // 输出结果
                    if (count($resultQr) > 0) {
                        echo json_encode([
                            'code' => 0,
                            'msg' => 'SUCCESS',
                            'data' => $resultQr,
                            'page' => 'https://console.cli.im/active/activecode?categoryId='.$category_root_id.'&org_module_id='.$org_module_id.'&p=1'
                        ]);
                    } else {
                        echo json_encode([
                            'code' => -2,
                            'msg' => '无数据'
                        ]);
                    }
                } else {
                    
                    // 处理第二个请求失败的情况
                    echo json_encode([
                        'code' => -2,
                        'msg' => '获取二维码列表失败，可能是Cookie失效。'
                    ]);
                }
            }
        } else {
            
            // 处理第一次请求失败的情况
            echo json_encode([
                'code' => -1,
                'msg' => '获取列表失败，可能是Cookie失效。'
            ]);
        }
    }
    
    // 关闭 cURL 会话
    curl_close($ch);
    
?>