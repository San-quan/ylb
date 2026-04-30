<?php

$task_id = intval($_GET['id'] ?? 0);

if($task_id){

    include '../../../../Db.php';

    try{

        $pdo = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
            $config['db_user'],
            $config['db_pass']
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 查询任务信息
        $stmt = $pdo->prepare("SELECT * FROM ylbPlugin_domainCheckTasks WHERE task_id = :task_id LIMIT 1");
        $stmt->execute([
            'task_id'=>$task_id
        ]);

        $getInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if($getInfo){

            $task_object = $getInfo['task_object'];
            $check_status = $getInfo['check_status'];
            $wx_check_num = $getInfo['wx_check_num'];
            $noti_type = $getInfo['noti_type'];
            $ret_msg = '无需发送通知';
            $actionUser = $getInfo['actionUser'];

            if($check_status == 1){

                // 请求检测接口
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

                $currentUrl = $protocol."://".$_SERVER['HTTP_HOST']
                .dirname($_SERVER['REQUEST_URI'])
                ."/api.php?url=".$task_object;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $currentUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

                $response = curl_exec($ch);

                if(curl_errno($ch)){

                    echo json_encode([
                        'code'=>1,
                        'msg'=>'检测接口请求失败：'.curl_error($ch)
                    ]);
                    exit;

                }

                curl_close($ch);

                $json = json_decode($response,true);

                $check_result_code = $json['code'] ?? -5;
                $check_result_msg = $json['msg'] ?? '未知错误';

                // 状态文本
                switch((int)$check_result_code){

                    case 0:
                        $wx_check_result_text='正常';
                        break;

                    case -2:
                        $wx_check_result_text='疑似白拦截';
                        break;

                    case -3:
                        $wx_check_result_text='疑似蓝拦截';
                        break;

                    case -4:
                        $wx_check_result_text='疑似红拦截';
                        break;

                    case -5:
                        $wx_check_result_text='未知拦截类型';
                        break;

                    case -1:
                        $wx_check_result_text='缺少待检测的URL';
                        break;

                    case 205:
                        $wx_check_result_text='未授权域名';
                        break;

                    case 206:
                    case 207:
                        $wx_check_result_text='仅限私域引流宝系统内使用';
                        break;

                    default:
                        $wx_check_result_text='未知状态';
                }

                // 更新检测结果
                $new_num = $wx_check_num + 1;
                $check_time = date('Y-m-d H:i:s');

                $stmt = $pdo->prepare("
                    UPDATE ylbPlugin_domainCheckTasks
                    SET 
                        wx_check_result = :wx_check_result,
                        wx_check_num = :wx_check_num,
                        check_time = :check_time
                    WHERE task_id = :task_id
                ");

                $stmt->execute([
                    'wx_check_result'=>$check_result_code,
                    'wx_check_num'=>$new_num,
                    'check_time'=>$check_time,
                    'task_id'=>$task_id
                ]);
                
                // 获取通知配置
                $stmt = $pdo->prepare("
                    SELECT Appid, AppSecret, AgentId, toUser, BarkUrl
                    FROM ylbPlugin_domainCheckTasks_notify
                    WHERE user_name = :user_name
                    LIMIT 1
                ");
                
                $stmt->execute([
                    'user_name' => $actionUser
                ]);
                
                $notify = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $Appid = $notify['Appid'] ?? '';
                $AppSecret = $notify['AppSecret'] ?? '';
                $AgentId = $notify['AgentId'] ?? '';
                $toUser = $notify['toUser'] ?? '';
                $BarkUrl = $notify['BarkUrl'] ?? '';
                
                
                // 企业微信通知
                if($noti_type == 'qywx' && $wx_check_result_text !== '正常'){
                
                    if(!empty($Appid) && !empty($AppSecret) && !empty($AgentId) && !empty($toUser)){
                        
                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

                        $parentUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['REQUEST_URI'])) . '/';
                        
                        $sendmsg = '域名检测结果：'.$task_object.' 存在异常！';
                        
                        $qywx_post_url = $parentUrl . 'server/qywx.php?content='.urlencode($sendmsg).'&msgtype=markdown&title='.urlencode('域名检测结果').'&tasksId='.$task_id;
                        
                        // 发送
                        $send_result = file_get_contents($qywx_post_url);
                        
                        // 结果
                        $errcode = json_decode($send_result)->errcode;
                        $errmsg = json_decode($send_result)->errmsg;
                        
                        if($errcode === 60020 || $errcode === '60020') {
                            
                            // IP白名单未加入
                            $ret_msg = '通知发送失败！原因：服务器IP地址未加入企业微信应用【企业可信IP】白名单，请查阅文档完成配置';
                        }else if($errcode === 0 || $errcode === '0') {
                                
                            $ret_msg = '通知成功';
                        }else {
                            
                            // 其它情况
                            $ret_msg = $errmsg;
                        }
                    }
                }
                
                // Bark通知
                if($noti_type == 'bark' && $wx_check_result_text !== '正常'){
                    if(!empty($BarkUrl)){
                    
                        $msg = '域名检测结果：'.$task_object.' 存在异常！';
                    
                        $url = rtrim($BarkUrl,'/').'/'.urlencode($msg);
                        $ch = curl_init($url);
                    
                        if($ch === false){
                            $send_result = 'curl_init_failed';
                        }else{
                    
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    
                            $send_result = curl_exec($ch);
                    
                            if(curl_errno($ch)){
                                $send_result = 'curl_error: '.curl_error($ch);
                            }
                    
                            curl_close($ch);
                            
                            // 结果
                            $errcode = json_decode($send_result)->code;
                            $errmsg = json_decode($send_result)->message;
                            
                            if($errcode === 400 || $errcode === '400') {
                                
                                // Token错误
                                $ret_msg = '通知发送失败！原因：Bark服务器链接错误或失效，请检查！';
                            }else if($errcode === 200 || $errcode === '200') {
                                
                                $ret_msg = '通知成功';
                            }else {
                                
                                // 其它情况
                                $ret_msg = $errmsg;
                            }
                        }
                    
                    }
                }
                
                // 返回检测结果
                header("Content-type:application/json");
                echo json_encode([
                    'code'=>$check_result_code,
                    'msg'=>$wx_check_result_text,
                    'noti_type' => $noti_type,
                    'noti_result' => $ret_msg
                ]);
            }else{

                header("Content-type:application/json");
                echo json_encode([
                    'code'=>404,
                    'msg'=>'该任务已被停止'
                ]);
            }
        }else{
            
            header("Content-type:application/json");
            echo json_encode([
                'code'=>404,
                'msg'=>'当前任务不存在！'
            ]);
        }
    }catch(PDOException $e){

        echo json_encode([
            'code'=>500,
            'msg'=>'数据库错误',
            'error'=>$e->getMessage()
        ]);
    }
}else{

    header("Content-type:application/json");
    echo json_encode([
        'code'=>404,
        'msg'=>'参数不完整！'
    ]);
}

?>