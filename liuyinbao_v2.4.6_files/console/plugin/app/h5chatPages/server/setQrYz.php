<?php

    // 页面编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $qr_id = trim($_GET['qr_id']);
        $yuzhi = trim($_GET['yuzhi']);
        
        // 过滤参数
        if(empty($yuzhi) || !isset($yuzhi)){
            
            $result = array(
                'code' => 203,
                'msg' => '请输入阈值'
            );
        }else if(!preg_match('/^-?\d+$/', $yuzhi)){
            
            $result = array(
                'code' => 203,
                'msg' => '阈值需要是一个大于0的整数'
            );
        }else if(empty($qr_id) || !isset($qr_id)){
            
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
            
            // 验证当前要操作的 qr_id 的添加人是不是当前登录的用户
            $checkUser = $db->set_table('ylbPlugin_kflandpageQrs')->find(['qr_id' => $qr_id]);
            $qr_adduser = $checkUser['qr_adduser'];
            
            // 用户一致：允许操作
            if($qr_adduser == $LoginUser){
                
                // 执行更新
                $updateSQL = $db->set_table('ylbPlugin_kflandpageQrs')->update(
                    ['qr_id' => $qr_id],
                    ['qr_yz' => $yuzhi]
                );
                if($updateSQL){
                    
                    // 更新成功
                    $result = array(
                        'code' => 200,
                        'msg' => '设置成功',
                        'kf_id' => $checkUser['kf_id']
                    );
                }else{
                    
                    // 更新失败
                    $result = array(
                        'code' => 202,
                        'msg' => '设置失败'
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