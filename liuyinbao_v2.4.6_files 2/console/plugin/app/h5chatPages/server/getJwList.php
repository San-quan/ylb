<?php

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        // 接收参数
    	@$page = $_GET['p']?$_GET['p']:1;
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../../../../Db.php';
        
        // 实例化类
    	$db = new DB_API($config);
    	
        // 新增一个字段
    	$checkExitsSQL = "SHOW COLUMNS FROM ylbPlugin_h5chatPages LIKE 'targetlink_mode'";
        $checkExits = $db->set_table('ylbPlugin_h5chatPages')->findSql($checkExitsSQL);
        if(!$checkExits) {
            
            // 不存在这个字段
            // 新增字段
            $Add_user_expire_time = "ALTER TABLE ylbPlugin_h5chatPages ADD targetlink_mode INT(1) DEFAULT '1' COMMENT '目标链接跳转模式'";
            $db->set_table('ylbPlugin_h5chatPages')->findSql($Add_user_expire_time);
        }
        
        // 自动升级
    	// 需要操作的表
        $table_1 = 'ylbPlugin_h5chatPages_qywxKfXcxConfig';
        
        // 检查表 1 是否存在
        $checkExits_table_1 = "SHOW TABLES LIKE '$table_1'";
        $check_table_1 = $db->set_table($table_1)->findSql($checkExits_table_1);
        
        if(!$check_table_1) {
            
            // 如果不存在
            // 创建表 1
            $table_1_create = "CREATE TABLE `$table_1` (
              `id` int(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `appid` varchar(32) DEFAULT NULL COMMENT '小程序Appid',
              `banner` text COMMENT '首页海报图',
              `bannerClickUrl` text COMMENT '首页海报图点击跳转的链接',
              `bannerClickNum` int(9) NOT NULL DEFAULT '0' COMMENT '首页海报图点击次数',
              `service_content` text COMMENT '首页服务介绍文案',
              `kfQrUrl` text COMMENT '客服二维码',
              `kfQrBgimg` text COMMENT '客服页面卡片背景图',
              `kfQrPagePv` int(9) NOT NULL DEFAULT '0' COMMENT '客服页面访问次数',
              `KfQrBtnClickCount` int(9) NOT NULL DEFAULT '0' COMMENT '客服按钮点击次数',
              `action_User` varchar(32) DEFAULT NULL COMMENT '操作人'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='企微客服小程序配置'";
            
            // 执行
            $db->set_table($table_1)->findSql($table_1_create);
        }
    	
    	// 获取当前登录用户
        // 创建的总数
    	$jwNum = $db->set_table('ylbPlugin_h5chatPages')->getCount(['page_create_user' => $LoginUser]);
    
    	// 每页数量
    	$lenght = 10;
    
    	// 每页第一行
    	$offset = ($page-1)*$lenght;
    
    	// 总页码
    	$allpage = ceil($jwNum/$lenght);
    
    	// 上一页     
    	$prepage = $page-1;
    	if($page == 1){
    		$prepage=1;
    	}
    
    	// 下一页
    	$nextpage = $page+1;
    	if($page == $allpage){
    		$nextpage=$allpage;
    	}
    
    	// 获取当前登录用户创建的
        // 每页10个，DESC排序
    	$getJwList = $db->set_table('ylbPlugin_h5chatPages')->findAll(
    	    $conditions = ['page_create_user' => $LoginUser],
    	    $order = 'ID DESC',
    	    $fields = null,
    	    $limit = ''.$offset.','.$lenght.''
    	);
    	
        // 获取appid
        $getAppid = $db->set_table('ylbPlugin_h5chatPages_qywxKfXcxConfig')->find(['action_User' => $LoginUser]);
    	
        // 判断获取结果
    	if($getJwList){
    	    
    	    // 获取成功
    		$result = array(
    		    'jwList' => $getJwList,
    		    'jwNum' => $jwNum,
    		    'prepage' => $prepage,
    		    'nextpage' => $nextpage,
    		    'allpage' => $allpage,
    		    'page' => $page,
    		    'code' => 200,
    		    'msg' => '获取成功',
    		    'appid' => $getAppid['appid']
    		);
    	}else{
    	    
    	    // 获取失败
            $result = array(
                'code' => 204,
                'msg' => '暂无落地页',
                'appid' => $getAppid['appid']
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