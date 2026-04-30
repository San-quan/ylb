<?php
/**
 * 环境变量加载类
 * 加载 .env 文件中的配置到 $_ENV 和 getenv()
 */

class Env {
    private static $loaded = false;
    private static $path = '';
    
    /**
     * 加载环境变量文件
     */
    public static function load($path = null) {
        if (self::$loaded) {
            return;
        }
        
        self::$path = $path ?? dirname(__DIR__) . '/.env';
        
        if (!file_exists(self::$path)) {
            return;
        }
        
        $lines = file(self::$path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // 跳过注释
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // 解析 KEY=VALUE 格式
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // 移除引号
                if (preg_match('/^["\'](.*)["\']\s*$/', $value, $matches)) {
                    $value = $matches[1];
                }
                
                // 设置到 $_ENV 和 getenv()
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                putenv("$key=$value");
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * 获取环境变量
     */
    public static function get($key, $default = null) {
        $value = getenv($key);
        
        if ($value === false) {
            $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        }
        
        return $value;
    }
    
    /**
     * 检查环境变量是否存在
     */
    public static function has($key) {
        return getenv($key) !== false || isset($_ENV[$key]);
    }
}

// 自动加载环境变量
Env::load();