<?php
    
	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $appid = trim($_POST['appid']);
        $banner = trim($_POST['banner']);
        $bannerClickUrl = trim($_POST['bannerClickUrl']);
        $service_content = trim($_POST['service_content']);
        $kfQrUrl = trim($_POST['kfQrUrl']);
        $kfQrBgimg = trim($_POST['kfQrBgimg']);
        $loginUser = trim($_SESSION["yinliubao"]);
        
        // 过滤参数
        if(empty($appid) || !isset($appid)){
            
            $result = array(
                'code' => 203,
                'msg' => '小程序Appid未填写'
            );
        }else if(empty($banner) || !isset($banner)){
            
            $result = array(
                'code' => 203,
                'msg' => '请上传首页海报图'
            );
        }else if(empty($service_content) || !isset($service_content)){
            
            $result = array(
                'code' => 203,
                'msg' => '首页服务介绍文案未完成'
            );
        }else if(empty($kfQrUrl) || !isset($kfQrUrl)){
            
            $result = array(
                'code' => 203,
                'msg' => '请上传客服二维码'
            );
        }else{
            
            // 数据库配置
        	include '../../../../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);

            // 检查当前appid配置是否存在
            $checkAppid = $db->set_table('ylbPlugin_h5chatPages_qywxKfXcxConfig')->find(['action_User' => $loginUser]);
            
            if($checkAppid) {
                
                // 如果存在
                // 则更新
                // 检查当前要更新的Appid的用户是否为当前登录的用户
                if($checkAppid['action_User'] == $loginUser) {
                    
                    // 是的
                    $updateConfig = $db->set_table('ylbPlugin_h5chatPages_qywxKfXcxConfig')->update(
                        ['action_User' => $loginUser],
                        [
                            'appid' => $appid, 
                            'banner' => $banner,
                            'bannerClickUrl' => $bannerClickUrl,
                            'service_content' => $service_content,
                            'kfQrUrl' => $kfQrUrl,
                            'kfQrBgimg' => $kfQrBgimg
                        ]
                    );
                    
                    // 执行结果
                    if($updateConfig){
                        
                        // 成功
                        $result = array(
                            'code' => 200,
                            'msg' => '保存成功'
                        );
                    }else{
                        
                        // 失败
                        $result = array(
                            'code' => 202,
                            'msg' => '保存失败'
                        );
                    }
                }else {
                    
                    // 不是的
                    $result = array(
                        'code' => 202,
                        'msg' => '禁止操作！'
                    );
                }
            }else {
                
                // 否则添加
                // 执行SQL
                $addConfig = $db->set_table('ylbPlugin_h5chatPages_qywxKfXcxConfig')->add(
                    [
                        'appid' => $appid,
                        'banner' => $banner,
                        'bannerClickUrl' => $bannerClickUrl,
                        'service_content' => $service_content,
                        'kfQrUrl' => $kfQrUrl,
                        'kfQrBgimg' => $kfQrBgimg,
                        'action_User' => $loginUser
                    ]
                );
                
                // 执行结果
                if($addConfig){
                    
                    // 成功
                    $result = array(
                        'code' => 200,
                        'msg' => '配置成功'
                    );
                }else{
                    
                    // 失败
                    $result = array(
                        'code' => 202,
                        'msg' => '配置失败'
                    );
                }
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