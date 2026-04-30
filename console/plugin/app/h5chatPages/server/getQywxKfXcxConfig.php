<?php

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $appid = trim($_GET['appid']);
        
        // 数据库配置
    	include '../../../../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    	
        // 获取详情
        $getQywxKfXcxConfig = $db->set_table('ylbPlugin_h5chatPages_qywxKfXcxConfig')->find(['appid' => $appid]);
        
        // 返回数据
        if($getQywxKfXcxConfig){
            
            // 返回数据
            $result = array(
    		    'code' => 200,
    		    'msg' => '获取成功',
    		    'qywxKfXcxConfig' => $getQywxKfXcxConfig
		    );
        }else{
            
            // 无结果
            $result = array(
    		    'code' => 204,
    		    'msg' => '获取失败或未提交设置'
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