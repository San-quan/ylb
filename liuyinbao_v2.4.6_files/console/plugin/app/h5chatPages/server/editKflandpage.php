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
        $kf_id = trim($_POST['kf_id']);
        
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
        }else if(empty($kf_id) || !isset($kf_id)){
            
            $result = array(
                'code' => 203,
                'msg' => '服务错误'
            );
        }else{
            
            // 数据库配置
            include '../../../../Db.php';
        
            // 实例化类
            $db = new DB_API($config);
            
            // 验证当前要编辑的 kf_id 的创建者是否为当前登录的用户
            $getCreatUser = $db->set_table('ylbPlugin_kflandpages')->find(['kf_id' => $kf_id]);
            $kf_create_user = $getCreatUser['kf_create_user'];
            
            // 用户一致：允许操作
            if($kf_create_user == $LoginUser){
                
                // 执行更新
                $updateSQL = $db->set_table('ylbPlugin_kflandpages')->update(
                    [
                        'kf_id' => $kf_id,
                        'kf_create_user' => $kf_create_user
                    ],
                    [
                        'kf_title' => $kf_title,
                        'kf_model' => $kf_model,
                        'kf_scantips' => $kf_scantips,
                        'kf_ldym' => $kf_ldym
                    ]
                );
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