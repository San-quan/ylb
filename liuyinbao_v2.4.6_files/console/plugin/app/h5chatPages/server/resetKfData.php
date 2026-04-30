<?php
    
	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $loginUser = trim($_SESSION["yinliubao"]);
        
        // 数据库配置
    	include '../../../../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    	
        // 执行重置
        $reset = $db->set_table('ylbPlugin_h5chatPages_qywxKfXcxConfig')->update(
            ['action_User' => $loginUser],
            [
                'bannerClickNum' => 0,
                'kfQrPagePv' => 0,
                'KfQrBtnClickCount' => 0
            ]
        );
        
        // 执行结果
        if($reset){
            
            // 成功
            $result = array(
                'code' => 200,
                'msg' => '已重置'
            );
        }else{
            
            // 失败
            $result = array(
                'code' => 202,
                'msg' => '重置失败'
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