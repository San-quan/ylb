<?php

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
    	$ipAddress = trim($_GET['ip_address']);
    	$kf_id = trim($_GET['kf_id']);
    	
        // 过滤参数
        if(!isset($ipAddress) || !isset($kf_id)){
            
            $result = array(
			    'code' => 203,
                'msg' => '非法请求'
		    );
        }else{
            
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 验证用户
            $checkUser = $db->set_table('huoma_kf')->find(['kf_id' => $kf_id]);
            $kf_creat_user = $checkUser['kf_creat_user'];
            
            // 判断操作结果
            if($kf_creat_user == $LoginUser){
                
                // 用户一致：允许操作
                // 获取当前的状态
                $getStatus = $db->set_table('huoma_kf_iplist')->find(['ip_address' => $ipAddress]);
                
                if($getStatus) {
                    
                    // 当前状态
                    $status = $getStatus['ip_ban'];
                    
                    if($status == 1) {
                        
                        // 修改为2
                        // 设置为解封状态
                        $updateBAN = $db->set_table('huoma_kf_iplist')->update(['ip_address' => $ipAddress],['ip_ban' => '2']);
                        $text = '已解封';
                    }else {
                        
                        // 修改为1
                        // 设置为封禁状态
                        $updateBAN = $db->set_table('huoma_kf_iplist')->update(['ip_address' => $ipAddress],['ip_ban' => '1']);
                        $text = '已封禁';
                    }
                    
                    if($updateBAN) {
                        
                        $result = array(
			                'code' => 200,
                            'msg' => $text
		                );
                    }else {
                        
                        $result = array(
			                'code' => 202,
                            'msg' => '操作失败！'
		                );
                    }
                }else {
                    
                    // 无法获取到状态
                    $result = array(
			            'code' => 202,
                        'msg' => '无法获取到IP封禁状态'
		            );
                }
            }else{
                
                // 用户不一致：禁止操作
                $result = array(
        			'code' => 202,
                    'msg' => '非法请求'
        		);
            }
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