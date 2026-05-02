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
        $kf_id = $_GET["kf_id"];
        
        // 数据库配置
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    	
    	// 需要操作的表
        $table_1 = 'huoma_kf_iplist';
        
        // 检查表 1 是否存在
        $checkExits_table_1 = "SHOW TABLES LIKE '$table_1'";
        $check_table_1 = $db->set_table($table_1)->findSql($checkExits_table_1);
        
        if(!$check_table_1) {
            
            // 如果不存在
            // 创建表 1
            $table_1_create = "CREATE TABLE `$table_1` (
              `id` int(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `kf_id` int(9) DEFAULT NULL COMMENT '客服ID',
              `ip_address` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ip地址',
              `ip_device` varchar(32) DEFAULT NULL COMMENT '访问设备',
              `ip_platform` varchar(32) DEFAULT NULL COMMENT '访问平台',
              `ip_location` varchar(64) DEFAULT NULL COMMENT '访问位置',
              `ip_pv` int(9) NOT NULL DEFAULT '0' COMMENT '访问量',
              `ip_ban` int(1) NOT NULL DEFAULT '2' COMMENT '封禁 1封 2解',
              `ip_addtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '访问时间'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ip列表'";
            
            // 执行
            $db->set_table($table_1)->findSql($table_1_create);
        }
    
    	// 数据库huoma_kf表
    	$huoma_kf = $db->set_table('huoma_kf_iplist');
    
    	// 获取总数
    	$ipNum = $huoma_kf->getCount(['kf_id'=>$kf_id]);
    
    	// 每页数量
    	$lenght = 10;
    
    	// 每页第一行
    	$offset = ($page-1)*$lenght;
    
    	// 总页码
    	$allpage = ceil($ipNum/$lenght);
    
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
    
    	// 获取当前id的列表
    	$getipList = $huoma_kf->findAll(
    	    $conditions = ['kf_id' => $kf_id],
    	    $order = 'ID DESC',
    	    $fields = null,
    	    $limit = ''.$offset.','.$lenght.''
    	);
    	
        // 获取标题
        $titleSQL = $db->set_table('huoma_kf')->find(['kf_id' => $kf_id]);
    	
        // 判断获取结果
    	if($getipList){
    	    
    	    // 获取成功
    		$result = array(
    		    'ipList' => $getipList,
    		    'ipNum' => $ipNum,
    		    'prepage' => $prepage,
    		    'nextpage' => $nextpage,
    		    'allpage' => $allpage,
    		    'page' => $page,
    		    'code' => 200,
    		    'msg' => '获取成功',
    		    'kf_title' => $titleSQL['kf_title']
    		);
    	}else{
    	    
    	    // 获取失败
            $result = array(
                'code' => 204,
                'msg' => '暂无IP记录'
            );
    	}
    	
    	// 更新UV
        // 只需要统计 huoma_kf_iplist 里面 ip_address 有多少个 ip_addtime 是今天的就行了
        $tongjiToday = "SELECT COUNT(DISTINCT ip_address) AS today_unique_count FROM huoma_kf_iplist WHERE DATE(ip_addtime) = CURDATE() AND kf_id =".$kf_id;
        $uvTotal = $db->set_table('huoma_kf_iplist')->findSql($tongjiToday);
        $newJSON = array(
            'uv' => $uvTotal[0]['today_unique_count'],
            'date' => date('Y-m-d')
        );
        $db->set_table('huoma_kf')->update(
            ['kf_id' => $kf_id],
            ['kf_ip_today' => json_encode($newJSON)]
        );
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