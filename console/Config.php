<?php
/**
 * 配置管理模块
 * 负责加载、验证、管理环境变量配置
 * 
 * @author AutoDev
 * @version 1.0.0
 */

class Config {
    
    // 配置实例（单例模式）
    private static $instance = null;
    
    // 配置缓存
    private $config = [];
    
    // 验证规则
    private $rules = [];
    
    // 错误信息
    private $errors = [];
    
    // 是否已加载
    private $loaded = false;
    
    /**
     * 私有构造函数（单例模式）
     */
    private function __construct() {
        $this->initRules();
    }
    
    /**
     * 获取单例实例
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 初始化验证规则
     */
    private function initRules() {
        $this->rules = [
            // ========== 数据库配置 ==========
            'DB_HOST' => [
                'type' => 'string',
                'required' => true,
                'default' => 'localhost',
                'pattern' => '/^[a-zA-Z0-9\.\-]+$/',
                'message' => '数据库主机地址格式不正确'
            ],
            'DB_PORT' => [
                'type' => 'int',
                'required' => true,
                'default' => 3306,
                'min' => 1,
                'max' => 65535,
                'message' => '数据库端口必须在1-65535之间'
            ],
            'DB_NAME' => [
                'type' => 'string',
                'required' => true,
                'pattern' => '/^[a-zA-Z0-9_\-]+$/',
                'message' => '数据库名称只能包含字母、数字、下划线和连字符'
            ],
            'DB_USER' => [
                'type' => 'string',
                'required' => true,
                'pattern' => '/^[a-zA-Z0-9_\-]+$/',
                'message' => '数据库用户名格式不正确'
            ],
            'DB_PASSWORD' => [
                'type' => 'string',
                'required' => false, // 生产环境必须设置
                'sanitize' => 'raw',
                'message' => '数据库密码不能为空'
            ],
            'DB_PREFIX' => [
                'type' => 'string',
                'required' => false,
                'default' => '',
                'pattern' => '/^[a-zA-Z0-9_]*$/',
                'message' => '表前缀只能包含字母、数字和下划线'
            ],
            
            // ========== 应用配置 ==========
            'APP_VERSION' => [
                'type' => 'string',
                'required' => true,
                'default' => '2.4.6',
                'pattern' => '/^\d+\.\d+\.\d+$/',
                'message' => '版本号格式必须为 X.Y.Z'
            ],
            'APP_DEBUG' => [
                'type' => 'bool',
                'required' => false,
                'default' => false,
                'message' => '调试模式只能是 true 或 false'
            ],
            'APP_NAME' => [
                'type' => 'string',
                'required' => false,
                'default' => '私域引流宝',
                'maxLength' => 50,
                'message' => '应用名称不能超过50个字符'
            ],
            
            // ========== 安全配置 ==========
            'SESSION_LIFETIME' => [
                'type' => 'int',
                'required' => false,
                'default' => 604800,
                'min' => 60,        // 最小60秒
                'max' => 31536000,  // 最大365天
                'message' => '会话生命周期必须在60秒到365天之间'
            ],
            'CSRF_TOKEN' => [
                'type' => 'bool',
                'required' => false,
                'default' => true,
                'message' => 'CSRF令牌只能是 true 或 false'
            ],
            'API_SECRET_KEY' => [
                'type' => 'string',
                'required' => false,
                'sanitize' => 'raw',
                'minLength' => 32,
                'message' => 'API密钥长度至少32位'
            ],
            
            // ========== API配置 ==========
            'API_RATE_LIMIT' => [
                'type' => 'int',
                'required' => false,
                'default' => 60,
                'min' => 1,
                'max' => 10000,
                'message' => 'API频率限制必须在1-10000之间'
            ],
            'API_TIMEOUT' => [
                'type' => 'int',
                'required' => false,
                'default' => 30,
                'min' => 1,
                'max' => 300,
                'message' => 'API超时时间必须在1-300秒之间'
            ],
            
            // ========== 通知配置 ==========
            'NOTIFICATION_EMAIL' => [
                'type' => 'email',
                'required' => false,
                'message' => '通知邮箱格式不正确'
            ],
            'NOTIFICATION_WEBHOOK' => [
                'type' => 'url',
                'required' => false,
                'message' => 'WebHook地址格式不正确'
            ],
            
            // ========== 对象存储配置 ==========
            'OSS_ENDPOINT' => [
                'type' => 'string',
                'required' => false,
                'pattern' => '/^https?:\/\/.+$/',
                'message' => 'OSS端点必须是有效的URL'
            ]
        ];
    }
    
    /**
     * 加载环境变量文件
     * @param string $path .env文件路径
     * @return bool 是否加载成功
     */
    public function load($path = null) {
        if ($this->loaded) {
            return true;
        }
        
        $envPath = $path ?? $this->findEnvFile();
        
        if (!file_exists($envPath)) {
            $this->errors[] = "环境变量文件不存在: {$envPath}";
            // 尝试使用默认值
            $this->applyDefaults();
            return false;
        }
        
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // 跳过注释和空行
            if (empty($line) || $line[0] === '#') {
                continue;
            }
            
            // 解析 KEY=VALUE 格式
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // 移除引号
                $value = $this->stripQuotes($value);
                
                // 设置到配置
                $this->config[$key] = $value;
            }
        }
        
        // 验证配置
        $this->validate();
        
        // 注入到进程环境
        $this->injectToEnv();
        
        $this->loaded = true;
        return empty($this->errors);
    }
    
    /**
     * 查找.env文件
     */
    private function findEnvFile() {
        $dirs = [
            dirname(__DIR__),           // console/
            dirname(__DIR__) . '/..',    // 项目根目录
            getcwd()
        ];
        
        foreach ($dirs as $dir) {
            $envPath = $dir . '/.env';
            if (file_exists($envPath)) {
                return $envPath;
            }
        }
        
        return end($dirs) . '/.env';
    }
    
    /**
     * 移除引号
     */
    private function stripQuotes($value) {
        if (preg_match('/^["\'](.*)["\']\s*$/', $value, $matches)) {
            return $matches[1];
        }
        return $value;
    }
    
    /**
     * 应用默认值
     */
    private function applyDefaults() {
        foreach ($this->rules as $key => $rule) {
            if (!isset($this->config[$key]) && isset($rule['default'])) {
                $this->config[$key] = $rule['default'];
            }
        }
    }
    
    /**
     * 验证配置
     */
    private function validate() {
        $this->errors = [];
        
        foreach ($this->rules as $key => $rule) {
            $value = $this->config[$key] ?? null;
            
            // 检查必填项
            if ($value === null) {
                if (!empty($rule['required'])) {
                    $this->errors[$key] = "{$key} 是必填项";
                }
                continue;
            }
            
            // 类型验证
            if (!$this->validateType($key, $value, $rule)) {
                $this->errors[$key] = $rule['message'] ?? "{$key} 验证失败";
            }
        }
    }
    
    /**
     * 类型验证
     */
    private function validateType($key, $value, $rule) {
        $type = $rule['type'] ?? 'string';
        
        switch ($type) {
            case 'int':
                if (!is_numeric($value)) {
                    return false;
                }
                $num = (int)$value;
                if (isset($rule['min']) && $num < $rule['min']) return false;
                if (isset($rule['max']) && $num > $rule['max']) return false;
                break;
                
            case 'bool':
                if (!in_array(strtolower($value), ['true', 'false', '1', '0', 'yes', 'no'], true)) {
                    return false;
                }
                break;
                
            case 'string':
                if (isset($rule['minLength']) && strlen($value) < $rule['minLength']) return false;
                if (isset($rule['maxLength']) && strlen($value) > $rule['maxLength']) return false;
                if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) return false;
                break;
                
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !empty($value)) {
                    return false;
                }
                break;
                
            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL) && !empty($value)) {
                    return false;
                }
                break;
        }
        
        return true;
    }
    
    /**
     * 注入到进程环境
     */
    private function injectToEnv() {
        foreach ($this->config as $key => $value) {
            // 转换为对应类型
            $rule = $this->rules[$key] ?? [];
            $type = $rule['type'] ?? 'string';
            
            switch ($type) {
                case 'int':
                    $value = (int)$value;
                    break;
                case 'bool':
                    $value = in_array(strtolower($value), ['true', '1', 'yes'], true);
                    break;
            }
            
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
    
    /**
     * 获取配置值
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed 配置值
     */
    public function get($key, $default = null) {
        if (!$this->loaded) {
            $this->load();
        }
        
        if (isset($this->config[$key])) {
            $rule = $this->rules[$key] ?? [];
            $type = $rule['type'] ?? 'string';
            
            switch ($type) {
                case 'int':
                    return (int)$this->config[$key];
                case 'bool':
                    return in_array(strtolower($this->config[$key]), ['true', '1', 'yes'], true);
                default:
                    return $this->config[$key];
            }
        }
        
        // 返回默认值
        if ($default !== null) {
            return $default;
        }
        
        // 返回规则中的默认值
        return $this->rules[$key]['default'] ?? null;
    }
    
    /**
     * 获取所有配置
     */
    public function all() {
        if (!$this->loaded) {
            $this->load();
        }
        return $this->config;
    }
    
    /**
     * 检查配置是否存在
     */
    public function has($key) {
        if (!$this->loaded) {
            $this->load();
        }
        return isset($this->config[$key]);
    }
    
    /**
     * 获取验证错误
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * 检查是否有错误
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * 获取数据库配置
     */
    public function getDatabase() {
        return [
            'db_host' => $this->get('DB_HOST'),
            'db_port' => $this->get('DB_PORT'),
            'db_name' => $this->get('DB_NAME'),
            'db_user' => $this->get('DB_USER'),
            'db_pass' => $this->get('DB_PASSWORD'),
            'db_prefix' => $this->get('DB_PREFIX', '')
        ];
    }
    
    /**
     * 获取应用配置
     */
    public function getApp() {
        return [
            'version' => $this->get('APP_VERSION'),
            'debug' => $this->get('APP_DEBUG'),
            'name' => $this->get('APP_NAME'),
            'session_lifetime' => $this->get('SESSION_LIFETIME')
        ];
    }
    
    /**
     * 获取API配置
     */
    public function getApi() {
        return [
            'rate_limit' => $this->get('API_RATE_LIMIT'),
            'timeout' => $this->get('API_TIMEOUT'),
            'secret_key' => $this->get('API_SECRET_KEY')
        ];
    }
    
    /**
     * 魔术方法 - 支持 $config->db_host 方式访问
     */
    public function __get($key) {
        return $this->get($key);
    }
    
    /**
     * 魔术方法 - 检查属性是否存在
     */
    public function __isset($key) {
        return $this->has($key);
    }
}

// 便捷函数
if (!function_exists('config')) {
    /**
     * 获取配置值
     */
    function config($key = null, $default = null) {
        $config = Config::getInstance();
        
        if ($key === null) {
            return $config->all();
        }
        
        return $config->get($key, $default);
    }
}

if (!function_exists('db_config')) {
    /**
     * 获取数据库配置
     */
    function db_config() {
        return Config::getInstance()->getDatabase();
    }
}

if (!function_exists('app_config')) {
    /**
     * 获取应用配置
     */
    function app_config() {
        return Config::getInstance()->getApp();
    }
}