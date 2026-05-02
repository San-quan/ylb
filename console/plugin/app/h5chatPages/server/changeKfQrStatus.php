<?php

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
    	$qr_id = trim($_GET['qr_id']);
    	
        // 过滤参数
        if(empty($qr_id) || !isset($qr_id)){
            
            $result = array(
			    'code' => 203,
                'msg' => '非法请求'
		    );
        }else{
            
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
        	include '../../../../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 验证用户
            $checkUser = $db->set_table('ylbPlugin_kflandpageQrs')->find(['qr_id' => $qr_id]);
            $qr_adduser = $checkUser['qr_adduser'];
            if($qr_adduser == $LoginUser){
                
                // 用户一致：允许操作
                // 获取当前状态
                $qr_status = $checkUser['qr_status'];
                
                if($qr_status == 1){
                    
                    // 更新的数据
                    $updateData = ['qr_status' => 2];
                    $statusText = '已停用';
                }else{
                    
                    // 更新的数据
                    $updateData = ['qr_status' => 1];
                    $statusText = '已启用';
                }
                
                // 提交更新
                $changeStatus = $db->set_table('ylbPlugin_kflandpageQrs')->update(
                    [
                        'qr_id' => $qr_id,
                        'qr_adduser' => $LoginUser
                    ],
                    $updateData
                );
                
                // 结果
                if($changeStatus){
                    
                    // 操作成功
                    $result = array(
			            'code' => 200,
                        'msg' => $statusText,
                        'kf_id' => $checkUser['kf_id']
		            );
                }else{
                    
                    // 操作失败
                    $result = array(
			            'code' => 202,
                        'msg' => '操作失败'
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