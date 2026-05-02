<?php

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        // 接收参数
    	$page_id = trim($_GET['page_id']);
    	
    	if(!$page_id) {
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
    	
        // 获取当前页面id的所有目标链接
    	$getLinks = $db->set_table('ylbPlugin_h5chatPages_targetlinks')->findAll(
    	    $conditions = ['page_id' => $page_id],
    	    $order = 'ID ASC',
    	    $fields = null,
    	    $limit = null
    	);
    	
        // 判断获取结果
    	if($getLinks){
    	    
    	    // 获取成功
    		$result = array(
    		    'targetlinks' => $getLinks,
    		    'code' => 200,
    		    'msg' => '获取成功'
    		);
    	}else{
    	    
    	    // 获取失败
            $result = array(
                'code' => 204,
                'msg' => '暂无目标链接'
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