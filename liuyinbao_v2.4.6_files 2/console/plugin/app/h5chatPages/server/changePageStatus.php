<?php

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
    	$page_id = trim($_GET['page_id']);
    	
        // 过滤参数
        if(empty($page_id) || !isset($page_id)){
            
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
            $checkUser = $db->set_table('ylbPlugin_h5chatPages')->find(['page_id' => $page_id]);
            $page_create_user = $checkUser['page_create_user'];
            if($page_create_user == $LoginUser){
                
                // 用户一致：允许操作
                // 获取当前状态
                $page_status = $checkUser['page_status'];
                
                if($page_status == 1){
                    
                    // 更新的数据
                    $updateData = ['page_status' => 2];
                    $statusText = '已停用';
                }else{
                    
                    // 更新的数据
                    $updateData = ['page_status' => 1];
                    $statusText = '已启用';
                }
                
                // 提交更新
                $changePagePayStatus = $db->set_table('ylbPlugin_h5chatPages')->update(
                    [
                        'page_id' => $page_id,
                        'page_create_user' => $LoginUser
                    ],
                    $updateData
                );
                
                // 结果
                if($changePagePayStatus){
                    
                    // 操作成功
                    $result = array(
			            'code' => 200,
                        'msg' => $statusText
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