<?php

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
    	$kf_id = trim($_GET['kf_id']);
    	
        // 过滤参数
        if(empty($kf_id) || !isset($kf_id)){
            
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
                $cleanAll = $db->set_table('huoma_kf_iplist')->delete(['kf_id' => $kf_id]);
                
                if($cleanAll) {
                    
                    // 删除成功
                    $result = array(
        			    'code' => 200,
                        'msg' => '已清空'
        		    );
                }else {
                    
                    // 删除失败
                    $result = array(
        			    'code' => 200,
                        'msg' => '清空失败，数据库操作错误~'
        		    );
                }
            }else{
                
                // 用户不一致：禁止操作
                $result = array(
        			'code' => 202,
                    'msg' => '禁止操作'
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