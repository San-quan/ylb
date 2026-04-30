<?php
/**
 * 数据库配置
 * 使用 Config 模块加载环境变量
 */

// 加载配置管理模块
include 'Config.php';

// 获取数据库配置（使用Config模块统一接口）
$config = Config::getInstance()->getDatabase();

// 添加额外配置
$config['folderNum'] = '1';
$config['version'] = Config::getInstance()->get('APP_VERSION', '2.4.6');

// 返回配置数组
return $config;