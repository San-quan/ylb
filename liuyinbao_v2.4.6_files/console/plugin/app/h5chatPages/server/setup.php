<?php

    /**
     * 状态码说明
     * 状态码：200 操作成功
     * 其它状态码自己定义就行
     * 源码用途：安装程序，修改app.json的install=2就是安装成功
     * 作者：TANKING
     */

	// 编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        $LoginUser = $_SESSION["yinliubao"];
        
        // 读取JSON文件内容
        $jsonFile = '../app.json';
        $jsonData = file_get_contents($jsonFile);
        
        // 检测配置文件目录权限
        if(!installPermission('../../')) {
            
            // 无755权限
            $result = array(
        		'code' => 202,
                'msg' => '安装失败，失败原因：console/plugin/app 目录没有755权限！请前往服务器修改权限！'
        	);
        	echo json_encode($result,JSON_UNESCAPED_UNICODE);
        	exit;
        }
        
        // 解码JSON数据
        $data = json_decode($jsonData, true);
        
        // 连接数据库
        include '../../../../Db.php';
        $conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
        
        // 验证当前登录用户是否为管理员
        $check_admin = "SELECT user_admin FROM huoma_user WHERE user_name = '$LoginUser'";
        $check_admin_result = $conn->query($check_admin)->fetch_assoc();
        
        // 如果不是管理员，不允许安装
        if($check_admin_result['user_admin'] == 2) {
            
            $result = array(
        		'code' => 202,
                'msg' => '安装失败：没有安装权限！'
        	);
        	echo json_encode($result,JSON_UNESCAPED_UNICODE);
        	exit;
        }
        
        // 获取安装状态
        $status = $data['install'];
        
        // 1 为未安装
        if($status == 1) {
            
            // 未安装
            // 设置为已安装
            $data['install'] = 2;
            $data['install_time'] = date('Y-m-d H:i:s');
            $data['current_status'] = "已安装";
            
            // 编码为JSON格式
            // JSON_PRETTY_PRINT：格式化JSON
            // JSON_UNESCAPED_UNICODE：不对中文编码
            // JSON_UNESCAPED_SLASHES：不对斜杠进行反斜杠编码
            $appJsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            // 写回JSON文件
            file_put_contents($jsonFile, $appJsonData);
            
            // 如果你需要操作数据库
            // 请在这里编写你操作数据库的逻辑

            // ylbPlugin_h5chatPages 表
            $ylbPlugin_h5chatPages = "CREATE TABLE `ylbPlugin_h5chatPages` (
              `id` int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `page_id` int(10) DEFAULT NULL COMMENT '页面ID',
              `page_title` varchar(64) DEFAULT NULL COMMENT '页面标题',
              `page_banner` text DEFAULT NULL COMMENT 'banner图片地址',
              `customer_avatar` text DEFAULT NULL COMMENT '客户头像',
              `my_avatar` text DEFAULT NULL COMMENT '我的头像',
              `welcome_msg` text DEFAULT NULL COMMENT '欢迎语',
              `end_msg` text DEFAULT NULL COMMENT '结束语',
              `reject_msg` text DEFAULT NULL COMMENT '拒绝语',
              `limitation` int(1) DEFAULT '1' COMMENT '访问限制 1不限制 2仅限手机 3仅限微信 4仅限QQ 5仅限抖音',
              `jump_btn_text` varchar(32) DEFAULT NULL COMMENT '跳转按钮文字',
              `jump_btn_bg_color` varchar(10) DEFAULT NULL COMMENT '跳转按钮背景颜色',
              `jump_btn_border_radius` varchar(10) DEFAULT NULL COMMENT '跳转按钮圆角',
              `jump_btn_mode` int(1) DEFAULT '1' COMMENT '跳转按钮链接模式 1随机 2阈值',
              `jump_btn_link1` text DEFAULT NULL COMMENT '跳转按钮链接1',
              `yz1` int(3) DEFAULT NULL COMMENT '跳转按钮链接1阈值',
              `jump_btn_link2` text DEFAULT NULL COMMENT '跳转按钮链接2',
              `yz2` int(3) DEFAULT NULL COMMENT '跳转按钮链接2阈值',
              `jump_btn_link3` text DEFAULT NULL COMMENT '跳转按钮链接3',
              `yz3` int(3) DEFAULT NULL COMMENT '跳转按钮链接3阈值',
              `jump_btn_link4` text DEFAULT NULL COMMENT '跳转按钮链接4',
              `yz4` int(3) DEFAULT NULL COMMENT '跳转按钮链接4阈值',
              `jump_btn_animation` int(1) DEFAULT '1' COMMENT '跳转按钮动画频率 1高频 2中频 3低频 4无动画',
              `page_pv` int(10) DEFAULT '0' COMMENT '访问次数',
              `page_status` int(1) DEFAULT '1' COMMENT '状态 1正常 2停用',
              `page_dlym` text DEFAULT NULL COMMENT '短链域名',
              `page_rkym` text DEFAULT NULL COMMENT '入口域名',
              `page_ldym` text DEFAULT NULL COMMENT '落地域名',
              `chatData` text DEFAULT NULL COMMENT '对话数据',
              `page_expire_time` varchar(32) DEFAULT NULL COMMENT '落地页到期时间',
              `page_create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `page_key` varchar(10) DEFAULT NULL COMMENT '短网址Key',
              `page_create_user` varchar(32) DEFAULT NULL COMMENT '创建者'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='对话式落地页'";
            
            $ylbPlugin_h5chatPages_targetlinks = "CREATE TABLE `ylbPlugin_h5chatPages_targetlinks` (
              `id` int(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `targetlink_id` int(9) DEFAULT NULL COMMENT '链接id',
              `targetlink` TEXT DEFAULT NULL COMMENT '链接',
              `page_id` int(9) DEFAULT NULL COMMENT '页面id',
              `targetlink_yz` varchar(5) DEFAULT NULL COMMENT '阈值',
              `targetlink_pv` int(5) NOT NULL DEFAULT '0' COMMENT '访问量',
              `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
              `targetlink_status` int(1) NOT NULL DEFAULT '1' COMMENT '状态 1正常 2停用',
              `add_user` varchar(32) DEFAULT NULL COMMENT '添加人'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='目标链接表'";
            
            
            if($conn->query($ylbPlugin_h5chatPages) === TRUE && $conn->query($ylbPlugin_h5chatPages_targetlinks) === TRUE) {
                
                // 创建表成功
                // 安装成功
                $result = array(
        			'code' => 200,
                    'msg' => '安装成功'
        		);
            }else {
                
                // 创建表失败
                // 安装失败
                $result = array(
        			'code' => 201,
                    'msg' => '安装失败：' . $conn->error
        		);
            }
            
        }else {
            
            // 已安装
            $result = array(
    			'code' => 201,
                'msg' => '安装失败：当前插件已安装！'
    		);
        }
    }else {
        
        $result = array(
			'code' => 201,
            'msg' => '未登录'
		);
    }
    
    // 检测配置文件目录权限
    function installPermission($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $perms = fileperms($dir);
        return ($perms & 0x1FF) >= 0755;
    }
	
    // 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>