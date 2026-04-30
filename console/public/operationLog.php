<?php
// 操作日志类
class OperationLog {
    private static $logFile = 'operation_log.txt';
    
    // 记录操作日志
    public static function log($action, $detail = '', $user = '') {
        if(empty($user) && isset($_SESSION['yinliubao'])) {
            $user = $_SESSION['yinliubao'];
        }
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $time = date('Y-m-d H:i:s');
        
        $logData = json_encode([
            'time' => $time,
            'ip' => $ip,
            'user' => $user,
            'action' => $action,
            'detail' => $detail,
            'url' => $_SERVER['REQUEST_URI'] ?? ''
        ], JSON_UNESCAPED_UNICODE);
        
        // 写入日志文件
        file_put_contents(self::$logFile, $logData . "\n", FILE_APPEND);
    }
    
    // 获取日志列表
    public static function getList($page = 1, $pageSize = 50) {
        if(!file_exists(self::$logFile)) {
            return [];
        }
        
        $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_reverse($lines); // 最新的在前
        
        $start = ($page - 1) * $pageSize;
        $logs = array_slice($lines, $start, $pageSize);
        
        $result = [];
        foreach($logs as $line) {
            $result[] = json_decode($line, true) ?: ['raw' => $line];
        }
        
        return $result;
    }
    
    // 获取日志总数
    public static function getCount() {
        if(!file_exists(self::$logFile)) {
            return 0;
        }
        return count(file(self::$logFile));
    }
    
    // 清理过期日志（保留30天）
    public static function clean($days = 30) {
        if(!file_exists(self::$logFile)) {
            return;
        }
        
        $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $keepLines = [];
        $cutoffTime = strtotime("-{$days} days");
        
        foreach($lines as $line) {
            $data = json_decode($line, true);
            if($data && isset($data['time'])) {
                $logTime = strtotime($data['time']);
                if($logTime > $cutoffTime) {
                    $keepLines[] = $line;
                }
            }
        }
        
        file_put_contents(self::$logFile, implode("\n", $keepLines) . "\n");
    }
    
    // 常用操作记录
    public static function login($user) {
        self::log('用户登录', '登录成功', $user);
    }
    
    public static function create($type, $name) {
        self::log('创建' . $type, '名称: ' . $name);
    }
    
    public static function update($type, $id, $changes = '') {
        self::log('更新' . $type, 'ID: ' . $id . ', 变更: ' . $changes);
    }
    
    public static function delete($type, $id) {
        self::log('删除' . $type, 'ID: ' . $id);
    }
}