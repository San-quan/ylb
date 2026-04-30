<?php
// API频率限制类
class RateLimit {
    private static $storage = []; // 内存存储（生产环境建议用Redis）
    private static $ipStorageFile = '/tmp/api_rate_limit.json';
    
    // 检查请求频率
    public static function check($key, $maxRequests = 60, $timeWindow = 60) {
        $ip = self::getClientIp();
        $cacheKey = $key . '_' . $ip;
        
        // 从文件读取
        self::loadFromFile();
        
        $now = time();
        
        // 初始化
        if(!isset(self::$storage[$cacheKey])) {
            self::$storage[$cacheKey] = [
                'count' => 0,
                'resetTime' => $now + $timeWindow
            ];
        }
        
        // 检查是否过期
        if($now > self::$storage[$cacheKey]['resetTime']) {
            self::$storage[$cacheKey] = [
                'count' => 0,
                'resetTime' => $now + $timeWindow
            ];
        }
        
        // 增加计数
        self::$storage[$cacheKey]['count']++;
        
        // 保存到文件
        self::saveToFile();
        
        // 检查是否超限
        if(self::$storage[$cacheKey]['count'] > $maxRequests) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'resetTime' => self::$storage[$cacheKey]['resetTime']
            ];
        }
        
        return [
            'allowed' => true,
            'remaining' => $maxRequests - self::$storage[$cacheKey]['count'],
            'resetTime' => self::$storage[$cacheKey]['resetTime']
        ];
    }
    
    // 获取客户端IP
    private static function getClientIp() {
        $ip = '';
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif(isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip ?: 'unknown';
    }
    
    // 从文件加载
    private static function loadFromFile() {
        if(file_exists(self::$ipStorageFile)) {
            $content = file_get_contents(self::$ipStorageFile);
            self::$storage = json_decode($content, true) ?: [];
            
            // 清理过期数据
            $now = time();
            foreach(self::$storage as $k => $v) {
                if($v['resetTime'] < $now) {
                    unset(self::$storage[$k]);
                }
            }
        }
    }
    
    // 保存到文件
    private static function saveToFile() {
        file_put_contents(self::$ipStorageFile, json_encode(self::$storage));
    }
    
    // 验证请求（简化的中间件方法）
    public static function verify($apiName, $maxRequests = 30, $timeWindow = 60) {
        $result = self::check($apiName, $maxRequests, $timeWindow);
        
        if(!$result['allowed']) {
            header('Content-Type: application/json');
            echo json_encode([
                'code' => 429,
                'msg' => '请求过于频繁，请稍后再试',
                'retryAfter' => $result['resetTime'] - time()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        return $result;
    }
}