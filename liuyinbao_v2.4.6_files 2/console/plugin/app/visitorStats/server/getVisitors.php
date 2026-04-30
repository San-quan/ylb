<?php
// 获取访客列表
header('Content-Type: application/json');

$page = intval($_GET['page'] ?? 1);
$pageSize = 20;
$type = $_GET['type'] ?? '';
$date = $_GET['date'] ?? '';
$ip = trim($_GET['ip'] ?? '');

try {
    include '../../../DbClass.php';
    $config = require '../../../Db.php';
    $db = new DB_API($config);
    
    // 构建查询条件
    $conditions = [];
    if ($type) {
        $conditions['target_type'] = $type;
    }
    if ($ip) {
        $conditions['ip'] = $ip;
    }
    
    // 日期条件
    $where = '';
    if ($date) {
        $startTime = strtotime($date);
        $endTime = $startTime + 86400;
        $where = "create_time >= $startTime AND create_time < $endTime";
    }
    
    // 获取列表
    if ($where) {
        $sql = "SELECT * FROM ylbPlugin_visitorStats WHERE $where ORDER BY create_time DESC LIMIT " . (($page - 1) * $pageSize) . ", $pageSize";
        $list = $db->findSql($sql);
        
        $countSql = "SELECT COUNT(*) as cnt FROM ylbPlugin_visitorStats WHERE $where";
        $count = $db->findSql($countSql);
        $total = $count[0]['cnt'] ?? 0;
    } else {
        $list = $db->findAll($conditions, 'id DESC', '*', $pageSize);
        $total = $db->getCount($conditions);
    }
    
    $result = [
        'code' => 200,
        'msg' => 'success',
        'data' => [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'hasMore' => ($page * $pageSize) < $total
        ]
    ];
} catch (Exception $e) {
    $result = [
        'code' => 202,
        'msg' => $e->getMessage()
    ];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);