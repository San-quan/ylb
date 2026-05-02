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
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    	
        // 定制功能-2025-03-22
    	$checkExitsSQL = "SHOW COLUMNS FROM huoma_kf LIKE 'kf_title_broswer'";
        $checkExits = $db->set_table('huoma_kf')->findSql($checkExitsSQL);
        if(!$checkExits) {
            
            // 不存在这个字段
            // 新增字段
            
            $Add_kf_title_broswer = "ALTER TABLE huoma_kf ADD kf_title_broswer VARCHAR(64) DEFAULT NULL COMMENT '浏览器标题'";
            $db->set_table('huoma_kf')->findSql($Add_kf_title_broswer);
            
            $Add_kf_hmbz = "ALTER TABLE huoma_kf ADD kf_hmbz VARCHAR(64) DEFAULT NULL COMMENT '活码备注'";
            $db->set_table('huoma_kf')->findSql($Add_kf_hmbz);
            
            $Add_djsStatus = "ALTER TABLE huoma_kf_zima ADD djsStatus INT(1) DEFAULT '1' COMMENT '倒计时跳转 1开 2关'";
            $db->set_table('huoma_kf_zima')->findSql($Add_djsStatus);
            
            $Add_djs = "ALTER TABLE huoma_kf_zima ADD djs VARCHAR(3) DEFAULT NULL COMMENT '倒计时秒数'";
            $db->set_table('huoma_kf_zima')->findSql($Add_djs);
            
            $Add_clickimgStatus = "ALTER TABLE huoma_kf_zima ADD clickimgStatus INT(1) DEFAULT '1' COMMENT '点击图片跳转 1开 2关'";
            $db->set_table('huoma_kf_zima')->findSql($Add_clickimgStatus);
            
            $Add_clickimgJumpUrl = "ALTER TABLE huoma_kf_zima ADD clickimgJumpUrl TEXT DEFAULT NULL COMMENT '点击图片跳转的链接'";
            $db->set_table('huoma_kf_zima')->findSql($Add_clickimgJumpUrl);
        }
    
    	// 数据库huoma_kf表
    	$huoma_kf = $db->set_table('huoma_kf');
    
    	// 获取总数
    	$kfNum = $huoma_kf->getCount(['kf_creat_user'=>$LoginUser]);
    
    	// 每页数量
    	$lenght = 10;
    
    	// 每页第一行
    	$offset = ($page-1)*$lenght;
    
    	// 总页码
    	$allpage = ceil($kfNum/$lenght);
    
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
    
    	// 获取当前登录用户创建的客服码，每页10个DESC排序
    	$getkfList = $huoma_kf->findAll(
    	    $conditions = ['kf_creat_user' => $LoginUser],
    	    $order = 'ID DESC',
    	    $fields = null,
    	    $limit = ''.$offset.','.$lenght.''
    	);
    	
        // 判断获取结果
    	if($getkfList && $getkfList > 0){
    	    
    	    // 获取成功
    		$result = array(
    		    'kfList' => $getkfList,
    		    'kfNum' => $kfNum,
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
                'msg' => '暂无客服码'
            );
    	}
    }else{
        
        // 未登录
        $result = array(
			'code' => 201,
            'msg' => '未登录或登录过期'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>