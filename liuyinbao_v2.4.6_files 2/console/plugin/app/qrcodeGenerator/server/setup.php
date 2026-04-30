<?php
// 二维码生成器插件 - 安装脚本
header('Content-Type: application/json');

// 获取当前版本号
$version = require '../../../Db.php';
$version = $config['version'] ?? '2.4.6';

// 检查数据库是否存在插件表，不存在则创建
try {
    include '../../../DbClass.php';
    $db = new DB_API($config);
    
    // 创建插件配置表
    $db->pdo->exec("CREATE TABLE IF NOT EXISTS `ylbPlugin_qrcodeGenerator` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `qr_id` varchar(50) NOT NULL COMMENT '二维码ID',
        `qr_content` text NOT NULL COMMENT '内容',
        `qr_style` varchar(50) DEFAULT NULL COMMENT '样式',
        `qr_color_dark` varchar(20) DEFAULT '#000000' COMMENT '前景色',
        `qr_color_light` varchar(20) DEFAULT '#ffffff' COMMENT '背景色',
        `qr_size` int(11) DEFAULT 200 COMMENT '尺寸',
        `qr_margin` int(11) DEFAULT 10 COMMENT '边距',
        `qr_logo` text DEFAULT NULL COMMENT 'Logo',
        `create_time` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    $result = [
        'code' => 200,
        'msg' => '二维码生成器插件安装成功'
    ];
} catch (Exception $e) {
    $result = [
        'code' => 202,
        'msg' => '安装失败: ' . $e->getMessage()
    ];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);