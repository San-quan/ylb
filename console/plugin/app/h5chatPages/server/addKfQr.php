<?php
    
	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $qr_img = trim($_POST['qr_img']);
        $yz = trim($_POST['yz']);
        $beizhu = trim($_POST['beizhu']);
        $kf_model = trim($_POST['kf_model']);
        $kf_id = trim($_POST['kf_id']);
        $add_user = trim($_SESSION["yinliubao"]);
        
        // 过滤参数
        if(empty($qr_img) || !isset($qr_img)){
            
            $result = array(
                'code' => 203,
                'msg' => '请上传客服二维码'
            );
        }else if(!is_url($qr_img)){
            
            $result = array(
                'code' => 203,
                'msg' => '客服二维码链接貌似不是正常的链接结构'
            );
        }else if($kf_model == 2 && empty($yz)){
            
            // 2就是阈值模式，阈值模式需要输入阈值
            $result = array(
                'code' => 203,
                'msg' => '你选择了阈值模式，需要输入阈值'
            );
        }else if(!empty($yz) && !preg_match('/^-?\d+$/', $yz)){
            
            $result = array(
                'code' => 203,
                'msg' => '阈值需要是一个大于0的整数'
            );
        }else if(empty($kf_id) || !isset($kf_id)){
            
            $result = array(
                'code' => 203,
                'msg' => '非法请求'
            );
        }else if(mb_strlen($beizhu) > 12){
            
            $result = array(
                'code' => 203,
                'msg' => '备注最多12个中文字符'
            );
        }else{
            
            // ID生成
            $qr_id = '10'.rand(101112,989898);
            
            // 数据库配置
        	include '../../../../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        
            // 参数
            $datas = [
                'kf_id' => $kf_id,
                'qr_id' => $qr_id,
                'qr_img' => $qr_img,
                'qr_yz' => $yz,
                'qr_bz' => $beizhu,
                'qr_adduser' => $add_user
            ];

            // 执行SQL
            $addSQL = $db->set_table('ylbPlugin_kflandpageQrs')->add($datas);
            
            // 执行结果
            if($addSQL){
                
                // 成功
                $result = array(
                    'code' => 200,
                    'msg' => '添加成功',
                    'kf_id' => $kf_id
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
    
    // 支持 http、https 和 weixin:// 协议的正则表达式
    function is_url($url) {
        $r = "/^(http[s]?:\/\/|weixin:\/\/)[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is";
        if (preg_match($r, $url)) {
            return true;
        } else {
            return false;
        }
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>