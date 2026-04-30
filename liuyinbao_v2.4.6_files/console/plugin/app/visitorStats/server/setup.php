<?php
// 访客画像插件 - 安装脚本
header('Content-Type: application/json');

try {
    include '../../../DbClass.php';
    $config = require '../../../Db.php';
    $db = new DB_API($config);
    
    // 创建访客记录表
    $db->pdo->exec("CREATE TABLE IF NOT EXISTS `ylbPlugin_visitorStats` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `ip` varchar(50) NOT NULL COMMENT 'IP地址',
        `region` varchar(100) DEFAULT NULL COMMENT '地区',
        `city` varchar(50) DEFAULT NULL COMMENT '城市',
        `isp` varchar(50) DEFAULT NULL COMMENT '运营商',
        `device` varchar(20) DEFAULT NULL COMMENT '设备类型 PC/Mobile/Tablet',
        `browser` varchar(50) DEFAULT NULL COMMENT '浏览器',
        `os` varchar(50) DEFAULT NULL COMMENT '操作系统',
        `target_type` varchar(50) DEFAULT NULL COMMENT '目标类型 qun/kf/channel/dwz/jumpWX',
        `target_id` varchar(50) DEFAULT NULL COMMENT '目标ID',
        `referer` varchar(500) DEFAULT NULL COMMENT '来源页面',
        `user_agent` text DEFAULT NULL COMMENT 'User-Agent',
        `create_time` int(11) DEFAULT NULL COMMENT '访问时间',
        PRIMARY KEY (`id`),
        KEY `idx_ip` (`ip`),
        KEY `idx_target` (`target_type`, `target_id`),
        KEY `idx_time` (`create_time`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    $result = [
        'code' => 200,
        'msg' => '访客画像插件安装成功'
    ];
} catch (Exception $e) {
    $result = [
        'code' => 202,
        'msg' => '安装失败: ' . $e->getMessage()
    ];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);