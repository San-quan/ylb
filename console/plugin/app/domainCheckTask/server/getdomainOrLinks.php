<?php

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../../../../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
        
        // 获取当前登录账号的管理权限
    	$user_admin = $db->set_table('huoma_user')->getField(['user_name'=>$LoginUser],'user_admin');
    	if($user_admin == 1){
    	    
            // 获取可检测的域名或链接
            $getdomainOrLinks = $db->set_table('huoma_domain')->findAll(
                $conditions=null,
                $order='id asc',
                $fields='domain',
                $limit=null
            );
            
            // 定义一个数组来存储过滤结果
            $domainOrLinks = array();
            foreach ($getdomainOrLinks as $domain) {
                
                // 过滤泛解析的域名
                if (strpos($domain['domain'], '*') !== false) {
                    continue;
                }
                
                // 过滤重复的域名
                if (!in_array($domain, $domainOrLinks)) {
                    $domainOrLinks[] = $domain;
                }
            }
            
            // 获取结果
            $result = array(
                'code' => 200,
                'msg' => '获取成功',
                'domainOrLinks' => $domainOrLinks
            );
    	}else{
    	    
    	   // 没有管理权限
    	   $result = array(
                'code' => 204,
                'msg' => '没有管理权限'
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