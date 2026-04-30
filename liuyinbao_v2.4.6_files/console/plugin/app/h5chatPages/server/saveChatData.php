<?php

    // 页面编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $chatData = trim($_GET['chatData']);
        $page_create_user = trim($_SESSION["yinliubao"]);
        $page_id = trim($_GET['page_id']);
        
        // 过滤参数
        if(empty($chatData) || !isset($chatData)){
            
            $result = array(
                'code' => 203,
                'msg' => '对话数据获取失败'
            );
        }else if(empty($page_id) || !isset($page_id)){
            
            $result = array(
                'code' => 203,
                'msg' => '发生错误'
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
                
                // 执行更新
                $updateSQL = $db->set_table('ylbPlugin_h5chatPages')->update(['page_id' => $page_id, 'page_create_user' => $page_create_user],['chatData' => $chatData]);
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