<?php
    
	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $targetlink = trim($_POST['targetlink']);
        $yz = trim($_POST['yz']);
        $mode = trim($_POST['mode']);
        $page_id = trim($_POST['page_id']);
        $add_user = trim($_SESSION["yinliubao"]);
        
        // 过滤参数
        if(empty($targetlink) || !isset($targetlink)){
            
            $result = array(
                'code' => 203,
                'msg' => '请输入或粘贴目标链接'
            );
        }else if(!is_url($targetlink)){
            
            $result = array(
                'code' => 203,
                'msg' => '目标链接貌似不是正常的链接结构'
            );
        }else if($mode == 2 && empty($yz)){
            
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
        }else if(empty($page_id) || !isset($page_id)){
            
            $result = array(
                'code' => 203,
                'msg' => '非法请求'
            );
        }else{
            
            // ID生成
            $targetlink_id = '10'.rand(101112,989898);
            
            // 数据库配置
        	include '../../../../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 检查这条链接是不是添加过了
            $checkLink = $db->set_table('ylbPlugin_h5chatPages_targetlinks')->find(['targetlink' => $targetlink]);
            if($checkLink) {
                
                // 添加过了
                $result = array(
                    'code' => 209,
                    'msg' => '这条已经添加过'
                );
                echo json_encode($result,JSON_UNESCAPED_UNICODE);
                exit;
            }
        	
            // 参数
            $datas = [
                'page_id' => $page_id,
                'targetlink_id' => $targetlink_id,
                'targetlink' => $targetlink,
                'targetlink_yz' => $yz,
                'add_user' => $add_user
            ];

            // 执行SQL
            $addSQL = $db->set_table('ylbPlugin_h5chatPages_targetlinks')->add($datas);
            
            // 执行结果
            if($addSQL){
                
                // 成功
                $result = array(
                    'code' => 200,
                    'msg' => '添加成功',
                    'page_id' => $page_id
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