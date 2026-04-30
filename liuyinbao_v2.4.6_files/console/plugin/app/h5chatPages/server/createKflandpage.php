<?php
    
	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $kf_title = trim($_POST['kf_title']);
        $kf_model = trim($_POST['kf_model']);
        $kf_scantips = trim($_POST['kf_scantips']);
        $kf_ldym = trim($_POST['kf_ldym']);
        
        // 当前登录的用户
        $LoginUser = trim($_SESSION["yinliubao"]);
        
        // 过滤参数
        if(empty($kf_title) || !isset($kf_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '标题未填写'
            );
        }else if(empty($kf_model) || !isset($kf_model)){
            
            $result = array(
                'code' => 203,
                'msg' => '请选择模式'
            );
        }else if(empty($kf_ldym) || !isset($kf_ldym)){
            
            $result = array(
                'code' => 203,
                'msg' => '请选择落地域名'
            );
        }else{
            
            // ID生成
            $kf_id = '10'.rand(100000,999999);
            
            // 数据库配置
        	include '../../../../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 参数
            $datas = [
                'kf_title' => $kf_title,
                'kf_model' => $kf_model,
                'kf_scantips' => $kf_scantips,
                'kf_ldym' => $kf_ldym,
                'kf_key' => createKey(5),
                'kf_id' => $kf_id,
                'kf_create_user' => $LoginUser
            ];

            // 执行SQL
            $createKflandpage = $db->set_table('ylbPlugin_kflandpages')->add($datas);
            
            // 执行结果
            if($createKflandpage){
                
                // 成功
                $result = array(
                    'code' => 200,
                    'msg' => '创建成功'
                );
            }else{
                
                // 失败
                $result = array(
                    'code' => 202,
                    'msg' => '创建失败'
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
    
    // 生成Key
    function createKey($length){
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $randStr = str_shuffle($str);
        $rands= substr($randStr,0,$length);
        return $rands;
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>