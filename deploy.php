<?php
/**
 * 私域引流宝 - PHP版一键部署脚本
 * 
 * 使用方法:
 *   php deploy.php              # 完整部署
 *   php deploy.php --check      # 仅检查配置
 *   php deploy.php --status     # 查看状态
 *   php deploy.php --start      # 启动服务
 *   php deploy.php --stop       # 停止服务
 *   php deploy.php --restart    # 重启服务
 */

class Deployer
{
    // 配置
    private $baseDir;
    private $logDir;
    private $pidFile;
    private $envFile;
    private $envExample;
    private $logFile;
    
    public function __construct()
    {
        $this->baseDir = dirname(__DIR__);
        $this->logDir = $this->baseDir . '/logs';
        $this->pidFile = $this->logDir . '/server.pid';
        $this->envFile = $this->baseDir . '/.env';
        $this->envExample = $this->baseDir . '/.env.example';
        $this->logFile = $this->logDir . '/deploy_' . date('Ymd_His') . '.log';
        
        $this->init();
    }
    
    /**
     * 初始化
     */
    private function init()
    {
        // 创建日志目录
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
        
        // 初始化日志
        $this->log('INFO', '私域引流宝部署脚本');
        $this->log('INFO', '时间: ' . date('Y-m-d H:i:s'));
    }
    
    /**
     * 日志输出
     */
    private function log($level, $message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logLine = "[$timestamp] [$level] $message\n";
        
        // 写入日志文件
        file_put_contents($this->logFile, $logLine, FILE_APPEND);
        
        // 输出到终端
        $colors = [
            'INFO' => "\033[34m",
            'SUCCESS' => "\033[32m",
            'WARNING' => "\033[33m",
            'ERROR' => "\033[31m",
            'NC' => "\033[0m"
        ];
        
        $color = $colors[$level] ?? $colors['INFO'];
        echo "$color[$level]$colors[NC] $message\n";
    }
    
    /**
     * 检查环境变量
     */
    public function checkEnv()
    {
        $this->log('INFO', '检查环境变量配置...');
        
        // 检查.env文件
        if (!file_exists($this->envFile)) {
            $this->log('WARNING', '.env文件不存在，正在创建...');
            
            if (file_exists($this->envExample)) {
                copy($this->envExample, $this->envFile);
                $this->log('SUCCESS', '已基于.env.example创建.env文件');
                $this->log('WARNING', '请编辑.env文件填写数据库密码等敏感配置');
            } else {
                $this->log('ERROR', '.env.example文件不存在');
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 验证配置
     */
    public function validateConfig()
    {
        $this->log('INFO', '验证配置文件...');
        
        require_once $this->baseDir . '/console/Config.php';
        
        $config = Config::getInstance();
        $config->load($this->envFile);
        
        if ($config->hasErrors()) {
            $errors = $config->getErrors();
            $this->log('ERROR', '配置验证失败:');
            foreach ($errors as $key => $error) {
                $this->log('ERROR', "  - $key: $error");
            }
            return false;
        }
        
        $this->log('SUCCESS', '配置文件验证通过');
        return true;
    }
    
    /**
     * 健康检查
     */
    public function healthCheck()
    {
        $this->log('INFO', '执行健康检查...');
        
        $checks = 0;
        $passed = 0;
        
        // 检查1: PHP环境
        $checks++;
        if (PHP_VERSION_ID >= 70400) {
            $this->log('SUCCESS', "[$checks] PHP版本: OK (" . PHP_VERSION . ")");
            $passed++;
        } else {
            $this->log('ERROR', "[$checks] PHP版本: 需要PHP 7.4+");
        }
        
        // 检查2: 必需扩展
        $checks++;
        $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'curl', 'openssl'];
        $missing = [];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }
        if (empty($missing)) {
            $this->log('SUCCESS', "[$checks] PHP扩展: OK");
            $passed++;
        } else {
            $this->log('ERROR', "[$checks] PHP扩展: 缺少 " . implode(', ', $missing));
        }
        
        // 检查3: 配置文件
        $checks++;
        if (file_exists($this->envFile)) {
            $this->log('SUCCESS', "[$checks] 配置文件: OK");
            $passed++;
        } else {
            $this->log('ERROR', "[$checks] 配置文件: 不存在");
        }
        
        // 检查4: 目录权限
        $checks++;
        $uploadDir = $this->baseDir . '/static/upload';
        if (is_dir($uploadDir) && is_writable($uploadDir)) {
            $this->log('SUCCESS', "[$checks] 上传目录权限: OK");
            $passed++;
        } else {
            $this->log('WARNING', "[$checks] 上传目录权限: 需要检查或创建");
            $passed++;
        }
        
        // 检查5: 数据库连接
        $checks++;
        try {
            require_once $this->baseDir . '/console/Config.php';
            require_once $this->baseDir . '/console/DbClass.php';
            
            $dbConfig = Config::getInstance()->getDatabase();
            $pdo = new PDO(
                'mysql:host=' . $dbConfig['db_host'] . ';port=' . $dbConfig['db_port'] . ';dbname=' . $dbConfig['db_name'],
                $dbConfig['db_user'],
                $dbConfig['db_pass']
            );
            $this->log('SUCCESS', "[$checks] 数据库连接: OK");
            $passed++;
        } catch (PDOException $e) {
            $this->log('ERROR', "[$checks] 数据库连接: FAILED - " . $e->getMessage());
        }
        
        // 汇总
        $this->log('', '');
        $this->log('INFO', '======================================');
        
        if ($passed === $checks) {
            $this->log('SUCCESS', "健康检查完成: {$passed}/{$checks} 通过");
            return true;
        } elseif ($passed >= $checks / 2) {
            $this->log('WARNING', "健康检查完成: {$passed}/{$checks} 通过");
            return true;
        } else {
            $this->log('ERROR', "健康检查未通过: {$passed}/{$checks} 通过");
            return false;
        }
    }
    
    /**
     * 获取进程PID
     */
    private function getPid()
    {
        if (file_exists($this->pidFile)) {
            return (int)file_get_contents($this->pidFile);
        }
        return null;
    }
    
    /**
     * 检查进程是否运行
     */
    private function isRunning()
    {
        $pid = $this->getPid();
        if ($pid && posix_kill($pid, 0)) {
            return true;
        }
        return false;
    }
    
    /**
     * 平滑关闭
     */
    public function stop()
    {
        $this->log('INFO', '开始停止服务...');
        
        $pid = $this->getPid();
        if (!$pid || !$this->isRunning()) {
            $this->log('INFO', '没有运行中的服务');
            return true;
        }
        
        // 发送SIGTERM
        $this->log('INFO', "发送SIGTERM信号到进程 {$pid}");
        posix_kill($pid, SIGTERM);
        
        // 等待进程退出
        $maxWait = 30;
        $waited = 0;
        while ($waited < $maxWait) {
            if (!$this->isRunning()) {
                $this->log('SUCCESS', '服务已停止');
                unlink($this->pidFile);
                return true;
            }
            sleep(1);
            $waited++;
        }
        
        // 强制终止
        $this->log('WARNING', '进程未响应，发送SIGKILL');
        posix_kill($pid, SIGKILL);
        unlink($this->pidFile);
        
        $this->log('SUCCESS', '服务已强制停止');
        return true;
    }
    
    /**
     * 启动服务
     */
    public function start()
    {
        $this->log('INFO', '启动服务...');
        
        // 检查端口是否被占用
        $port = 8080;
        $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
        if ($socket) {
            fclose($socket);
            $this->log('ERROR', "端口 {$port} 已被占用");
            return false;
        }
        
        // 启动PHP内置服务器
        $logFile = $this->logDir . '/server_' . date('Ymd') . '.log';
        $cmd = sprintf(
            'cd %s && nohup php -S 127.0.0.1:%d > %s 2>&1 & echo $!',
            escapeshellarg($this->baseDir),
            $port,
            escapeshellarg($logFile)
        );
        
        $pid = (int)shell_exec($cmd);
        
        if ($pid > 0) {
            file_put_contents($this->pidFile, $pid);
            $this->log('SUCCESS', "服务已启动 (PID: {$pid})");
            $this->log('INFO', "访问地址: http://127.0.0.1:{$port}");
            return true;
        } else {
            $this->log('ERROR', '服务启动失败');
            return false;
        }
    }
    
    /**
     * 重启服务
     */
    public function restart()
    {
        $this->stop();
        sleep(1);
        return $this->start();
    }
    
    /**
     * 查看状态
     */
    public function status()
    {
        $pid = $this->getPid();
        
        if ($pid && $this->isRunning()) {
            $this->log('SUCCESS', "服务运行中 (PID: {$pid})");
            
            // 获取进程信息
            $processFile = '/proc/' . $pid . '/cmdline';
            if (file_exists($processFile)) {
                $cmdline = file_get_contents($processFile);
                $cmdline = str_replace("\0", " ", $cmdline);
                $this->log('INFO', "命令: {$cmdline}");
            }
            
            return true;
        } else {
            $this->log('INFO', '服务未运行');
            return false;
        }
    }
    
    /**
     * 完整部署流程
     */
    public function deploy()
    {
        $this->log('INFO', '======================================');
        $this->log('INFO', '私域引流宝 - 一键部署');
        $this->log('INFO', '======================================');
        
        // 1. 检查环境变量
        if (!$this->checkEnv()) {
            return false;
        }
        
        // 2. 验证配置
        if (!$this->validateConfig()) {
            return false;
        }
        
        // 3. 停止旧服务
        $this->stop();
        
        // 4. 清理
        $this->log('INFO', '清理临时文件...');
        $this->cleanup();
        
        // 5. 启动服务
        if (!$this->start()) {
            return false;
        }
        
        // 6. 健康检查
        if (!$this->healthCheck()) {
            $this->log('WARNING', '健康检查未完全通过，但服务已启动');
        }
        
        $this->log('INFO', '======================================');
        $this->log('INFO', '部署完成');
        $this->log('INFO', '日志文件: ' . $this->logFile);
        
        return true;
    }
    
    /**
     * 清理临时文件
     */
    private function cleanup()
    {
        $this->log('INFO', '清理临时文件...');
        
        // 清理过期日志
        $logFiles = glob($this->logDir . '/deploy_*.log');
        foreach ($logFiles as $file) {
            if (filemtime($file) < time() - 30 * 86400) {
                unlink($file);
            }
        }
        
        $this->log('SUCCESS', '清理完成');
    }
}

/**
 * CLI入口
 */
$options = getopt('h', ['check', 'status', 'start', 'stop', 'restart', 'health', 'help']);

if (isset($options['h']) || isset($options['help'])) {
    echo "私域引流宝 - 一键部署脚本\n\n";
    echo "使用方法:\n";
    echo "  php deploy.php              完整部署\n";
    echo "  php deploy.php --check      检查配置\n";
    echo "  php deploy.php --status    查看状态\n";
    echo "  php deploy.php --start      启动服务\n";
    echo "  php deploy.php --stop       停止服务\n";
    echo "  php deploy.php --restart    重启服务\n";
    echo "  php deploy.php --health     健康检查\n";
    exit(0);
}

$deployer = new Deployer();

if (isset($options['check'])) {
    $deployer->checkEnv();
    $deployer->validateConfig();
} elseif (isset($options['status'])) {
    $deployer->status();
} elseif (isset($options['start'])) {
    $deployer->start();
} elseif (isset($options['stop'])) {
    $deployer->stop();
} elseif (isset($options['restart'])) {
    $deployer->restart();
} elseif (isset($options['health'])) {
    $deployer->healthCheck();
} else {
    $deployer->deploy();
}