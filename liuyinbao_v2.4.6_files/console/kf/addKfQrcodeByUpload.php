<?php

    // 定制功能-2025-03-22
    
	// 页面编码
	header("Content-type:application/json");

	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        // 接收参数
        $djsStatus = trim($_GET['djsStatus']);
        $djs = trim($_GET['djs']);
        $clickimgStatus = trim($_GET['clickimgStatus']);
        $clickimgJumpUrl = trim($_GET['clickimgJumpUrl']);
        $kfqrcode_imgurl = trim($_GET['kfqrcode_imgurl']);
        $kf_id = trim($_GET['kf_id']);

        // 过滤参数
        if($djsStatus == 1 && empty($djs)){
            
            $result = array(
                'code' => 203,
                'msg' => '请设置倒计时的秒数！'
            );
        }else if(empty($clickimgJumpUrl) || !isset($clickimgJumpUrl)){
            
            $result = array(
                'code' => 203,
                'msg' => '请上传二维码图片'
            );
        }else if(empty($kfqrcode_imgurl) || !isset($kfqrcode_imgurl)){
            
            $result = array(
                'code' => 203,
                'msg' => '请上传二维码图片'
            );
        }else if(empty($kf_id) || !isset($kf_id)){
            
            $result = array(
                'code' => 203,
                'msg' => '服务错误'
            );
        }else{
            
            $loginUser = trim($_SESSION["yinliubao"]);
            
            // 子码ID生成
            $zm_id = mt_rand(100000,999999);
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
            
            date_default_timezone_set('Asia/Shanghai'); // 设置为中国时区
            
        	// 参数
            $datas = [
                'kf_id' => $kf_id,
                'zm_id' => $zm_id,
                'zm_update_time' => date('Y-m-d H:i:s'),
                'zm_qrcode' => $kfqrcode_imgurl,
                'djsStatus' => $djsStatus,
                'djs' => $djs,
                'clickimgStatus' => $clickimgStatus,
                'clickimgJumpUrl' => $clickimgJumpUrl
            ];
            
            // 执行SQL
            $add = $db->set_table('huoma_kf_zima')->add($datas);
            
            if($add){
                
                // 成功
                $result = array(
                    'code' => 200,
                    'kf_title' => $kf_title,
                    'kf_id' => $kf_id,
                    'msg' => '添加成功'
                );
            }else{
                
                // 失败
                $result = array(
                    'code' => 202,
                    'msg' => '添加失败'
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