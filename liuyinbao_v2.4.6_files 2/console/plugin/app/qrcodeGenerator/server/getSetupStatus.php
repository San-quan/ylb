<?php
// 获取插件状态
header('Content-Type: application/json');

$result = [
    'code' => 200,
    'msg' => 'success',
    'data' => [
        'pluginId' => 'qrcodeGenerator',
        'name' => '二维码生成器',
        'version' => '1.0.0',
        'state' => 1
    ]
];

echo json_encode($result, JSON_UNESCAPED_UNICODE);