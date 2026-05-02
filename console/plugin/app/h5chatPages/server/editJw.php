<?php

    // 页面编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $page_title = trim($_POST['page_title']);
        $page_banner = trim($_POST['page_banner']);
        $page_expire_time = trim($_POST['page_expire_time']);
        $customer_avatar = trim($_POST['customer_avatar']);
        $my_avatar = trim($_POST['my_avatar']);
        $welcome_msg = trim($_POST['welcome_msg']);
        $end_msg = trim($_POST['end_msg']);
        $reject_msg = trim($_POST['reject_msg']);
        $limitation = trim($_POST['limitation']);
        $jump_btn_text = trim($_POST['jump_btn_text']);
        $jump_btn_bg_color = trim($_POST['jump_btn_bg_color']);
        $jump_btn_border_radius = trim($_POST['jump_btn_border_radius']);
        $jump_btn_animation = trim($_POST['jump_btn_animation']);
        $page_dlym = trim($_POST['page_dlym']);
        $page_rkym = trim($_POST['page_rkym']);
        $page_ldym = trim($_POST['page_ldym']);
        $page_create_user = trim($_SESSION["yinliubao"]);
        $page_id = trim($_POST['page_id']);
        $targetlink_mode = trim($_POST['targetlink_mode']);
        
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
                'msg' => '模式未选择'
            );
        }else if(empty($page_id) || !isset($page_id)){
            
            $result = array(
                'code' => 203,
                'msg' => '非法请求~'
            );
        }else{
            
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
            include '../../../../Db.php';
        
            // 实例化类
            $db = new DB_API($config);
            
            // 验证当前要编辑的 page_id 的创建者是否为当前登录的用户
            $getCreatUser = $db->set_table('ylbPlugin_h5chatPages')->find(['page_id' => $page_id]);
            $page_create_user = $getCreatUser['page_create_user'];
            
            // 用户一致：允许操作
            if($page_create_user == $LoginUser){
                
                // 需更新的字段
                $updateData = [
                    'page_title' => $page_title,
                    'page_banner' => $page_banner,
                    'page_expire_time' => $page_expire_time,
                    'customer_avatar' => $customer_avatar,
                    'my_avatar' => $my_avatar,
                    'welcome_msg' => $welcome_msg,
                    'end_msg' => $end_msg,
                    'reject_msg' => $reject_msg,
                    'limitation' => $limitation,
                    'jump_btn_text' => $jump_btn_text,
                    'jump_btn_bg_color' => $jump_btn_bg_color,
                    'jump_btn_border_radius' => $jump_btn_border_radius,
                    'jump_btn_link' => $jump_btn_link,
                    'jump_btn_animation' => $jump_btn_animation,
                    'page_dlym' => $page_dlym,
                    'page_rkym' => $page_rkym,
                    'page_ldym' => $page_ldym,
                    'targetlink_mode' => $targetlink_mode
                ];
                
                // 执行更新
                $updateSQL = $db->set_table('ylbPlugin_h5chatPages')->update(['page_id' => $page_id, 'page_create_user' => $page_create_user],$updateData);
                if($updateSQL){
                    
                    // 更新成功
                    $result = array(
                        'code' => 200,
                        'msg' => '已保存'
                    );
                }else{
                    
                    // 更新失败
                    $result = array(
                        'code' => 202,
                        'msg' => '更新失败'
                    );
                }
                
            }else{
                
                // 用户不一致
                $result = array(
                    'code' => 202,
                    'msg' => '非法操作'
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