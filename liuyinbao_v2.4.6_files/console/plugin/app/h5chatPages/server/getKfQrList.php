<?php

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        // 接收参数
    	$kf_id = trim($_GET['kf_id']);
    	
    	if(!$kf_id) {
    	    $result = array(
                'code' => 208,
                'msg' => '非法请求！'
            );
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
            exit;
    	}
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../../../../Db.php';
        
        // 实例化类
    	$db = new DB_API($config);
    	
    	// 需要操作的表
        $table_1 = 'ylbPlugin_kflandpageQrs';
        
        // 检查表 1 是否存在
        $checkExits_table_1 = "SHOW TABLES LIKE '$table_1'";
        $check_table_1 = $db->set_table($table_1)->findSql($checkExits_table_1);
        
        if(!$check_table_1) {
            
            // 如果不存在
            // 创建表 1
            $table_1_create = "CREATE TABLE `$table_1` (
              `id` int(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `kf_id` int(9) DEFAULT NULL COMMENT '客服id',
              `qr_img` TEXT DEFAULT NULL COMMENT '客服二维码',
              `qr_id` int(9) DEFAULT NULL COMMENT '客服二维码id',
              `qr_yz` varchar(5) DEFAULT NULL COMMENT '阈值',
              `qr_bz` varchar(32) DEFAULT NULL COMMENT '备注',
              `qr_pv` int(5) DEFAULT '0' COMMENT '访问量',
              `qr_addtime` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
              `qr_status` int(1) DEFAULT '1' COMMENT '状态 1正常 2停用',
              `qr_adduser` varchar(32) DEFAULT NULL COMMENT '添加人'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='客服二维码表'";
            
            // 执行
            $db->set_table($table_1)->findSql($table_1_create);
        }
    	
        // 获取当前kfid的所有目标链接
    	$getQrs = $db->set_table($table_1)->findAll(
    	    $conditions = ['kf_id' => $kf_id],
    	    $order = 'ID ASC',
    	    $fields = null,
    	    $limit = null
    	);
    	
        // 判断获取结果
    	if($getQrs){
    	    
    	    // 获取成功
    		$result = array(
    		    'kfQrList' => $getQrs,
    		    'code' => 200,
    		    'msg' => '获取成功'
    		);
    	}else{
    	    
    	    // 获取失败
            $result = array(
                'code' => 204,
                'msg' => '暂无客服二维码'
            );
    	}
    
    }else{
        
        // 未登录
        $result = array(
			'code' => 201,
            'msg' => '未登录'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>