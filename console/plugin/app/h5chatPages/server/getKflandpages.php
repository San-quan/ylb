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
        
        // 自动升级
    	// 需要操作的表
        $table_1 = 'ylbPlugin_kflandpages';
        
        // 检查表 1 是否存在
        $checkExits_table_1 = "SHOW TABLES LIKE '$table_1'";
        $check_table_1 = $db->set_table($table_1)->findSql($checkExits_table_1);
        
        if(!$check_table_1) {
            
            // 如果不存在
            // 创建表 1
            $table_1_create = "CREATE TABLE `$table_1` (
              `id` int(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `kf_id` varchar(32) DEFAULT NULL COMMENT '客服落地页id',
              `kf_title` varchar(32) DEFAULT NULL COMMENT '标题',
              `kf_model` int(1) NOT NULL DEFAULT '1' COMMENT '模式 1随机 2阈值',
              `kf_scantips` int(1) NOT NULL DEFAULT '1' COMMENT '扫码提示1开 2关',
              `kf_ldym` TEXT DEFAULT NULL COMMENT '落地域名',
              `kf_key` varchar(10) DEFAULT NULL COMMENT '短网址Key',
              `kf_create_user` varchar(32) DEFAULT NULL COMMENT '创建人',
              `kf_pv` int(9) DEFAULT '0' COMMENT '访问次数',
              `kf_clickNum` int(9) DEFAULT '0' COMMENT '扫码次数',
              `kf_status` int(1) DEFAULT '1' COMMENT '状态 1开 2关',
              `kf_create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='客服落地页'";
            
            // 执行
            $db->set_table($table_1)->findSql($table_1_create);
        }
    	
    	// 获取当前登录用户
        // 创建的总数
    	$allNum = $db->set_table('ylbPlugin_kflandpages')->getCount(['kf_create_user' => $LoginUser]);
    
    	// 每页数量
    	$lenght = 10;
    
    	// 每页第一行
    	$offset = ($page-1)*$lenght;
    
    	// 总页码
    	$allpage = ceil($allNum/$lenght);
    
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
    	$getKflandpages = $db->set_table('ylbPlugin_kflandpages')->findAll(
    	    $conditions = ['kf_create_user' => $LoginUser],
    	    $order = 'ID DESC',
    	    $fields = null,
    	    $limit = ''.$offset.','.$lenght.''
    	);
    	
    	if($getKflandpages){
    	    
    	    // 获取成功
    		$result = array(
    		    'kflandpages' => $getKflandpages,
    		    'allNum' => $allNum,
    		    'prepage' => $prepage,
    		    'nextpage' => $nextpage,
    		    'allpage' => $allpage,
    		    'page' => $page,
    		    'code' => 200,
    		    'msg' => '获取成功'
    		);
    	}else{
    	    
    	    // 获取失败
            $result = array(
                'code' => 204,
                'msg' => '暂无客服落地页'
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