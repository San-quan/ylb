<?php
/**
 * 配置管理模块测试
 */

// 加载配置模块
require_once 'Config.php';

echo "====================================\n";
echo "配置管理模块 - 测试脚本\n";
echo "====================================\n\n";

// 测试1: 加载配置
echo "[测试1] 加载配置...\n";
$config = Config::getInstance();
$result = $config->load();
echo "加载结果: " . ($result ? "成功" : "失败") . "\n";

// 测试2: 获取数据库配置
echo "\n[测试2] 获取数据库配置...\n";
$dbConfig = $config->getDatabase();
foreach ($dbConfig as $key => $value) {
    $display = ($key === 'db_pass') ? '******' : $value;
    echo "  {$key}: {$display}\n";
}

// 测试3: 获取应用配置
echo "\n[测试3] 获取应用配置...\n";
$appConfig = $config->getApp();
foreach ($appConfig as $key => $value) {
    echo "  {$key}: " . ($value === true ? 'true' : ($value === false ? 'false' : $value)) . "\n";
}

// 测试4: 使用便捷函数
echo "\n[测试4] 使用便捷函数 config()...\n";
echo "  APP_VERSION: " . config('APP_VERSION') . "\n";
echo "  APP_DEBUG: " . (config('APP_DEBUG') ? 'true' : 'false') . "\n";
echo "  API_RATE_LIMIT: " . config('API_RATE_LIMIT') . "\n";

// 测试5: 使用魔术方法
echo "\n[测试5] 使用魔术方法 \$config->key...\n";
echo "  DB_HOST: " . $config->DB_HOST . "\n";
echo "  DB_PORT: " . $config->DB_PORT . "\n";

// 测试6: 获取所有配置
echo "\n[测试6] 获取所有配置...\n";
$all = $config->all();
echo "  配置项数量: " . count($all) . "\n";
echo "  配置文件路径: " . realpath(dirname(__DIR__) . '/.env') . "\n";

// 测试7: 检查错误
echo "\n[测试7] 检查验证错误...\n";
$errors = $config->getErrors();
if (empty($errors)) {
    echo "  ✅ 无验证错误\n";
} else {
    foreach ($errors as $key => $error) {
        echo "  ❌ {$key}: {$error}\n";
    }
}

echo "\n====================================\n";
echo "测试完成\n";
echo "====================================\n";

// 便捷函数示例
echo "\n【使用示例】\n";
echo "
```php
// 方式1: 使用 config() 函数
\$version = config('APP_VERSION');
\$dbHost = config('DB_HOST', 'localhost');

// 方式2: 使用 Config 类
\$config = Config::getInstance();
\$dbConfig = \$config->getDatabase();

// 方式3: 使用魔术方法
echo \$config->DB_HOST;

// 方式4: 在模板中
<?= config('APP_NAME') ?>
```
";