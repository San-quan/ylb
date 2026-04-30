<?php
// 获取访客统计数据
header('Content-Type: application/json');

try {
    include '../../../DbClass.php';
    $config = require '../../../Db.php';
    $db = new DB_API($config);
    $db->set_table('ylbPlugin_visitorStats');
    
    // 总访客数
    $total = $db->getCount(null);
    
    // 今日访客
    $todayStart = strtotime(date('Y-m-d'));
    $today = $db->getCount("create_time >= $todayStart");
    
    // PC端
    $pc = $db->getCount("device = 'PC'");
    
    // 移动端
    $mobile = $db->getCount("device = 'Mobile'");
    
    $result = [
        'code' => 200,
        'msg' => 'success',
        'data' => [
            'total' => $total,
            'today' => $today,
            'pc' => $pc,
            'mobile' => $mobile
        ]
    ];
} catch (Exception $e) {
    $result = [
        'code' => 202,
        'msg' => $e->getMessage()
    ];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);