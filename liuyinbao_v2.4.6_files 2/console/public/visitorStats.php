<?php
// 访客统计工具类 - 可在其他模块中调用

class VisitorStats {
    private static $api = 'http://ip-api.com/json/'; // 免费IP查询API
    
    // 记录访客访问
    public static function record($targetType, $targetId, $referer = '') {
        try {
            $config = require '../../../Db.php';
            $db = new DB_API($config);
            $db->set_table('ylbPlugin_visitorStats');
            
            // 获取访客信息
            $ip = self::getClientIp();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            // 解析设备类型
            $device = self::getDevice($userAgent);
            
            // 解析浏览器
            $browser = self::getBrowser($userAgent);
            
            // 解析操作系统
            $os = self::getOS($userAgent);
            
            // 获取IP地区（使用缓存或API）
            $regionInfo = self::getRegion($ip);
            
            // 插入数据
            $data = [
                'ip' => $ip,
                'region' => $regionInfo['region'] ?? '',
                'city' => $regionInfo['city'] ?? '',
                'isp' => $regionInfo['isp'] ?? '',
                'device' => $device,
                'browser' => $browser,
                'os' => $os,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'referer' => $referer,
                'user_agent' => $userAgent,
                'create_time' => time()
            ];
            
            $db->add($data);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // 获取客户端IP
    private static function getClientIp() {
        $ip = '';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // 过滤内网IP
        if (preg_match('/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1]))/', $ip)) {
            return $ip; // 返回内网IP，实际项目中可跳过或使用固定值
        }
        return $ip ?: '0.0.0.0';
    }
    
    // 识别设备类型
    private static function getDevice($userAgent) {
        if (preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent)) {
            return 'Mobile';
        } elseif (preg_match('/Tablet|iPad/i', $userAgent)) {
            return 'Tablet';
        }
        return 'PC';
    }
    
    // 识别浏览器
    private static function getBrowser($userAgent) {
        if (preg_match('/MicroMessenger/i', $userAgent)) return 'WeChat';
        if (preg_match('/Chrome/i', $userAgent)) return 'Chrome';
        if (preg_match('/Firefox/i', $userAgent)) return 'Firefox';
        if (preg_match('/Safari/i', $userAgent)) return 'Safari';
        if (preg_match('/Edge/i', $userAgent)) return 'Edge';
        if (preg_match('/MSIE/i', $userAgent)) return 'IE';
        return 'Other';
    }
    
    // 识别操作系统
    private static function getOS($userAgent) {
        if (preg_match('/Windows/i', $userAgent)) return 'Windows';
        if (preg_match('/Mac/i', $userAgent)) return 'macOS';
        if (preg_match('/Linux/i', $userAgent)) return 'Linux';
        if (preg_match('/Android/i', $userAgent)) return 'Android';
        if (preg_match('/iOS|iPhone|iPad/i', $userAgent)) return 'iOS';
        return 'Other';
    }
    
    // 获取IP地区（简单的本地识别，更准确可用付费API）
    private static function getRegion($ip) {
        // 跳过内网IP
        if (preg_match('/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1]))/', $ip)) {
            return ['region' => '内网', 'city' => '', 'isp' => ''];
        }
        
        // 使用免费API（注意：生产环境建议使用付费API或本地数据库）
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::$api . $ip);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $response = curl_exec($ch);
            curl_close($ch);
            
            $data = json_decode($response, true);
            if ($data && $data['status'] === 'success') {
                return [
                    'region' => $data['country'] ?? '',
                    'city' => $data['city'] ?? '',
                    'isp' => $data['isp'] ?? ''
                ];
            }
        } catch (Exception $e) {
            // 忽略错误
        }
        
        return ['region' => '', 'city' => '', 'isp' => ''];
    }
    
    // 便捷方法：在群活码页面调用
    public static function recordQun($qunId) {
        return self::record('qun', $qunId, $_SERVER['HTTP_REFERER'] ?? '');
    }
    
    // 便捷方法：在客服码页面调用
    public static function recordKf($kfId) {
        return self::record('kf', $kfId, $_SERVER['HTTP_REFERER'] ?? '');
    }
    
    // 便捷方法：在渠道码页面调用
    public static function recordChannel($channelId) {
        return self::record('channel', $channelId, $_SERVER['HTTP_REFERER'] ?? '');
    }
    
    // 便捷方法：在短链接页面调用
    public static function recordDwz($dwzId) {
        return self::record('dwz', $dwzId, $_SERVER['HTTP_REFERER'] ?? '');
    }
    
    // 便捷方法：在抖音卡片页面调用
    public static function recordJumpWX($jwId) {
        return self::record('jumpWX', $jwId, $_SERVER['HTTP_REFERER'] ?? '');
    }
}