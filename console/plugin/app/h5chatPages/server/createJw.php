<?php
    
	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $page_title = trim($_POST['page_title']);
        $page_banner = trim($_POST['page_banner']);
        $customer_avatar = trim($_POST['customer_avatar']);
        $my_avatar = trim($_POST['my_avatar']);
        $welcome_msg = trim($_POST['welcome_msg']);
        $end_msg = trim($_POST['end_msg']);
        $reject_msg = trim($_POST['reject_msg']);
        $limitation = trim($_POST['limitation']);
        $jump_btn_text = trim($_POST['jump_btn_text']);
        $jump_btn_bg_color = trim($_POST['jump_btn_bg_color']);
        $jump_btn_border_radius = trim($_POST['jump_btn_border_radius']);
        $targetlink_mode = trim($_POST['targetlink_mode']);
        $jump_btn_animation = trim($_POST['jump_btn_animation']);
        $page_expire_time = trim($_POST['page_expire_time']);
        $page_dlym = trim($_POST['page_dlym']);
        $page_rkym = trim($_POST['page_rkym']);
        $page_ldym = trim($_POST['page_ldym']);
        $page_create_user = trim($_SESSION["yinliubao"]);
        
        // 到期时间格式转换
        $page_expire_time_format = new DateTime($page_expire_time);
        $page_expire_time_formatted = $page_expire_time_format->format("Y-m-d H:i:s");
        
        // 过滤参数
        if(empty($page_title) || !isset($page_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '落地页标题未填写'
            );
        }else if(empty($page_banner) || !isset($page_banner)){
            
            $result = array(
                'code' => 203,
                'msg' => '请上传banner图'
            );
        }else if(empty($customer_avatar) || !isset($customer_avatar)){
            
            $result = array(
                'code' => 203,
                'msg' => '请上传客户头像'
            );
        }else if(empty($my_avatar) || !isset($my_avatar)){
            
            $result = array(
                'code' => 203,
                'msg' => '请上传你的头像'
            );
        }else if(empty($page_dlym) || empty($page_rkym) || empty($page_ldym)){
            
            $result = array(
                'code' => 203,
                'msg' => '还有域名未选择'
            );
        }else if(empty($welcome_msg) || empty($end_msg) || empty($reject_msg)){
            
            $result = array(
                'code' => 203,
                'msg' => '请完善欢迎语、结束语、拒绝语'
            );
        }else if(empty($jump_btn_text) || !isset($jump_btn_text)){
            
            $result = array(
                'code' => 203,
                'msg' => '请填写跳转按钮的文字'
            );
        }else if(empty($targetlink_mode) || !isset($targetlink_mode)){
            
            $result = array(
                'code' => 203,
                'msg' => '请选择模式'
            );
        }else{
            
            // ID生成
            $page_id = '10'.rand(101112,989898);
            
            // 数据库配置
        	include '../../../../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 参数
            $createJw = [
                'page_id' => $page_id,
                'page_title' => $page_title,
                'page_key' => createKey(5),
                'page_banner' => $page_banner,
                'customer_avatar' => $customer_avatar,
                'my_avatar' => $my_avatar,
                'welcome_msg' => $welcome_msg,
                'end_msg' => $end_msg,
                'reject_msg' => $reject_msg,
                'limitation' => $limitation,
                'jump_btn_text' => $jump_btn_text,
                'jump_btn_bg_color' => $jump_btn_bg_color,
                'jump_btn_border_radius' => $jump_btn_border_radius,
                'targetlink_mode' => $targetlink_mode, // 模式
                'jump_btn_animation' => $jump_btn_animation,
                'page_expire_time' => $page_expire_time_formatted,
                'page_dlym' => $page_dlym,
                'page_rkym' => $page_rkym,
                'page_ldym' => $page_ldym,
                'chatData' => '{
                  "questions": [
                    {
                      "questionid": 1,
                      "text": "默认文案，默认文案。",
                      "options": [
                        {
                          "text": "按钮文字",
                          "response_type": "text",
                          "response_content": "你点击了按钮。",
                          "next_questionid": ""
                        }
                      ]
                    }
                  ]
                }',
                'page_create_user' => $page_create_user
            ];

            // 执行SQL
            $createJwSQL = $db->set_table('ylbPlugin_h5chatPages')->add($createJw);
            
            // 执行结果
            if($createJwSQL){
                
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