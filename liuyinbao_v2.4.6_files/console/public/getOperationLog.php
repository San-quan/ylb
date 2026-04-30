<?php
// 获取操作日志接口
header('Content-Type: application/json');

// 验证登录
session_start();
if(!isset($_SESSION['yinliubao'])) {
    echo json_encode(['code' => 201, 'msg' => '未登录']);
    exit;
}

$page = intval($_GET['page'] ?? 1);
$pageSize = 20;

// 引入日志类
require_once 'operationLog.php';

$logs = OperationLog::getList($page, $pageSize);
$count = OperationLog::getCount();

echo json_encode([
    'code' => 200,
    'msg' => 'success',
    'data' => $logs,
    'total' => $count,
    'page' => $page,
    'pageSize' => $pageSize
], JSON_UNESCAPED_UNICODE);