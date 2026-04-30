<html>
    <head>
        <meta name="wechat-enable-text-zoom-em" content="true">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="color-scheme" content="light dark">
        <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="format-detection" content="telephone=no">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Expires" content="0">
        <link rel="shortcut icon" href="https://res.wx.qq.com/a/wx_fed/assets/res/NTI4MWU5.ico">
        <style>
            #warnning{
                width: 80px;
                height: 80px;
                margin: 50px auto 20px;
            }
            #warnText{
                text-align: center;
                font-size: 20px;
                color: #000;
                font-weight: bold;
            }
            #warnning img{
                width: 80px;
                height: 80px;
            }
        </style>
    </head>
    <body>
        
    <?php
        
        // 禁止缓存
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Expires: 0");
    
        // 获取参数
        $kid = trim(intval($_GET['kid']));
        
        // 过滤参数
        if($kid){
            
            // 数据库配置
            include '../../../console/Db.php';
            
            // 实例化类
            $db = new DB_API($config);

// 记录访客
include '../../../console/public/visitorStats.php';
VisitorStats::recordKf($kid);
            
            // 根据kid获取落地域名
            $getkfldymResult = $db->set_table('huoma_kf')->find(['kf_id'=>$kid]);
            if($getkfldymResult){
                
                // 落地域名
                $kf_ldym = json_decode(json_encode($getkfldymResult))->kf_ldym;
                
                // 定制功能:2025-05-25
                // ------------------------------------
                // 检测落地域名是不是泛解析
                if(strpos($kf_ldym,'*.') !== FALSE){ 
                    
                    // 生成随机的泛解析域名
                    // ---------------------------------------------------------
                    // 算法 1：16 进制随机字符串（原始）
                    $fjx_1 = strtolower(bin2hex(random_bytes(4))); // 8位16进制
                    
                    // 算法 2：uniqid() 唯一ID
                    $fjx_2 = uniqid();
                    
                    // 算法 3：可读性强的随机字母（a-z）
                    $fjx_3 = substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz', 5)), 0, 8);
                    
                    // 算法 4：混合字母+数字
                    $fjx_4 = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', 5)), 0, 10);
                    
                    // 算法 5：当前时间戳 + 随机数
                    $fjx_5 = time() . rand(100, 999);
                    
                    // 算法 6：base36（数字+小写字母，压缩效果好）
                    $fjx_6 = base_convert(mt_rand(100000000, 999999999), 10, 36);
                    
                    // 算法 7：日期+随机后缀
                    $fjx_7 = date('Ymd') . '-' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 5);
                    
                    // 上面提供了7个泛解析前缀随机生成的算法
                    // --------------------------------------------------
                    // 你可以选择喜欢的算法，默认是 $fjx_4
                    $kf_ldym = preg_replace('/\*/', $fjx_4, $kf_ldym, 1);
                }
                
                // 301跳转
                jump($kf_ldym,$kid);
            }else{
                
                // 获取失败
                echo warnInfo('温馨提示','页面不存在或已被管理员删除');
            }
        }else{
            
            // 参数为空
            echo warnInfo('温馨提示','请求参数为空');
        }
        
        // 跳转
        function jump($kf_ldym,$kid){
            
            // 拼接落地页链接
            $longUrl = dirname(dirname($kf_ldym.$_SERVER['REQUEST_URI'])).'/?kid='.$kid.'#wechat_redirect';
            
            // 301跳转
            header('HTTP/1.1 301 Moved Permanently');
            
            // 跳转
            header('Location:'.$longUrl);
        }
                
        // 发送通知
        function sendNotification($noti_type,$noti_text,$db){
            
            // 根据noti_type选择发送的渠道
            include_once '../../../console/public/sendNotification.php';
        }
        
        // 解析数组
        function getSqlData($result,$field){
            
            // 传入数组和需要解析的字段
            return json_decode(json_encode($result))->$field;
        }
        
        // 提醒文字
        function warnInfo($title,$warnText){
            
            return '
            <title>'.$title.'</title>
            <div id="warnning">
                <img src="../../../static/img/warn.png" />
            </div>
            <p id="warnText">'.$warnText.'</p>';
        }
    
    ?>
    
    </body>
    
</html>