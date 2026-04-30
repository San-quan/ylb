<?php
// 数据库操作类
include 'DbClass.php';

// 从环境变量获取数据库密码，更安全
$db_pass = getenv('DB_PASSWORD') ?: '8dwZwDkGMaWEdijK';

// 数据库配置
$config = array (
 'db_host' => getenv('DB_HOST') ?: 'localhost',
 'db_port' => 3306,
 'db_name' => getenv('DB_NAME') ?: 'llk5g_com',
 'db_user' => getenv('DB_USER') ?: 'llk5g_com',
 'db_pass' => $db_pass,
 'db_prefix' => '',
 'folderNum' => '1',
 'version' => '2.4.6',
);
?>