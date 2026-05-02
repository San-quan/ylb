<?php

    $fileId = trim($_GET['fileid']);
    
    $url = "https://drive.kdocs.cn/api/v5/files/pic/thumbnail?fileids=$fileId&max_edge=600&review=true";
    
    // 初始化 cURL
    $ch = curl_init();
    
    // 设置 cURL 选项
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 返回响应内容
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 禁用 SSL 证书验证（仅用于测试环境）
    
    // 设置 Cookie 头部
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Cookie: wps_sid=V02SH6kvrEGackzD1NJVZ7Lj9I2ApNE00ad2556700144f4ae8;"
    ));
    
    // 执行请求
    $response = curl_exec($ch);
    
    // 检查是否有错误发生
    if (curl_errno($ch)) {
        echo '请求失败：' . curl_error($ch);
    } else {
        echo $response;
    }
    
    // 关闭 cURL 资源
    curl_close($ch);
?>