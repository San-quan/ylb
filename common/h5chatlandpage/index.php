<html>
    <head>
        <meta name="wechat-enable-text-zoom-em" content="true">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="format-detection" content="telephone=no">
        <link rel="shortcut icon" href="https://res.wx.qq.com/a/wx_fed/assets/res/NTI4MWU5.ico">
        <link rel="stylesheet" href="../../static/css/common.css">
        <link rel="stylesheet" href="chat.css">
        <script type="text/javascript" src="https://www.icbc.com.cn/resource/lib/jquery/jquery-3.6.0.min.js"></script>
    </head>
    <body>
        
    <?php
    
        // 页面编码
        header("Content-type:text/html;charset=utf-8");
        
        // 获取参数
        $page_key = trim($_GET['key']);
        
        // 过滤不安全的字符
        if(preg_match('/[_\-\/\[\].,:;\'"=+*`~!@#$%^&()]/',$page_key)){
           
            echo warnInfo('温馨提示','该链接不安全，请重新生成！');
            exit;
        }
        if(preg_match('/(select|update|drop|DROP|insert|create|delete|where|join|script)/i',$page_key)){
           
            echo warnInfo('温馨提示','该链接不安全，请重新生成！');
            exit;
        }
        
        // 过滤参数
        if($page_key){
            
            // 数据库配置
            include '../../console/Db.php';
            
            // 实例化类
            $db = new DB_API($config);
            
            // 获取当前 page_key 的详情
            $getPageInfo = $db->set_table('ylbPlugin_h5chatPages')->find(['page_key' => $page_key]);
            
            if(!$getPageInfo) {
                
                echo warnInfo('提示','当前页面不存在或已被管理员删除！');
                exit;
            }
            
            // 1. 检查当前链接的管理账号有效期
            // 2. 检测当前链接的管理账号状态
            
            // 获取当前链接的管理账号
            $currentKeyUser = $getPageInfo['page_create_user'];
            
            // 获取用户信息
            $getUserInfo = $db->set_table('huoma_user')->findAll(
                $conditions = ['user_name' => $currentKeyUser],
                $order = 'id asc',
                $fields = 'user_status',
                $limit = null
            );
            
            // user_expire_time
            
            // 检查创建者的状态
            if($getUserInfo[0]['user_status'] == 2) {
                
                // 账号已被停止使用
                echo warnInfo('提示','当前页面的管理账号已被停止使用');
                exit;
            }
            
            // // 当前链接的管理者的账号有效期
            // $current_user_expire_time = strtotime($getUserInfo[0]['user_expire_time']);
            
            // // 对比时间
            // if(time() > $current_user_expire_time) {
                
            //     // 账号已过期
            //     echo warnInfo('提示','当前链接的管理账号已到期');
            //     exit;
            // }
            
            // 检查当前页面是否已达到过期时间
            $page_expire_time = $getPageInfo['page_expire_time'];
            $current_time = date("Y-m-d H:i:s"); // 当前时间
            if (strtotime($current_time) >= strtotime($page_expire_time)) {
                
                // 页面已到期
                echo warnInfo('提示','当前页面已过期');
                exit;
            }
            
            if($getPageInfo){
                
                // 状态
                $page_status = $getPageInfo['page_status'];
                
                // 判断状态
                if($page_status == 1) {
                    
                    // 正常
                    // 访问限制检测（仅限手机打开）
                    if($getPageInfo['limitation'] == 2 && !preg_match('/Mobile|Android|iPhone|iPad|iPod|Windows Phone|webOS|BlackBerry/i', $_SERVER['HTTP_USER_AGENT'])) {
                        
                        echo warnInfo('提示','请在手机设备打开页面');
                        exit;
                    }
                    
                    // 访问限制检测（仅限微信内打开）
                    if($getPageInfo['limitation'] == 3 && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === FALSE) {
                        
                        echo warnInfo('提示','请在微信内打开页面');
                        exit;
                    }
                    
                    // 访问限制检测（仅限QQ内打开）
                    if($getPageInfo['limitation'] == 4 && strpos($_SERVER['HTTP_USER_AGENT'], 'QQ/') === FALSE && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === FALSE) {
                        
                        echo warnInfo('提示','请在QQ内打开页面');
                        exit;
                    }
                    
                    // 访问限制检测（仅限抖音内打开）
                    if($getPageInfo['limitation'] == 5 && (strpos($_SERVER['HTTP_USER_AGENT'], 'aweme') === FALSE || strpos($_SERVER['HTTP_USER_AGENT'], 'Bytedance') === FALSE)) {
                        
                        echo warnInfo('提示','请在抖音内打开页面');
                        exit;
                    }
                    
                    // 开始渲染对话内容
                    echo '
                    <title>'.$getPageInfo['page_title'].'</title>
                    <div id="chat-box">
                        <div id="chat-content">
                            <div class="banner-view"></div>
                        </div>
                        <a id="jump-btn" target="_blank"></a>
                    </div>';
                }else {
                    
                    // 停用
                    echo warnInfo('提示','该页面已被管理员停止使用');
                }
            }else{
                
                // 不存在
                echo warnInfo('提示','页面不存在或已被管理员删除');
            }
        }else {
            
            // 参数不完整
            echo warnInfo('提示','参数不完整！');
        }
        
        // 提醒文字
        function warnInfo($title,$warnText){
            
            return '
            <title>'.$title.'</title>
            <div id="warnning">
                <img src="../../static/img/warn.png" />
            </div>
            <p id="warnText">'.$warnText.'</p>';
        }
        
    ?>
    </body>
    <script>
        
        // 获取聊天数据
        async function getData(key, exportKey) {
          try {
            
            // 请求服务器
            let response = await fetch(`getChatData.php?key=${key}&exportKey=${exportKey}`);
            
            if (!response.ok) {
                document.title = '提醒';
                $('#chat-box').html('<div class="warnInfo"><div class="warn_icon"></div>服务器异常！如需调查发生该问题的原因，可在电脑浏览器打开本页面，按键盘的F12，打开开发者控制台切换至网络选项检查getChatData.php文件的错误信息。</div>');
                throw new Error('Network response was not ok');
            }
        
            let data = await response.json();
            processData(data);
        
          } catch (error) {
            console.error('Fetch error: ', error);
          }
        }
    
        // 确保聊天数据加载完成后执行
        function processData(data) {
            
            // 异常处理
            if(data.code !== "0") {
                
                document.title = '提醒';
                $('#chat-box').html('<div class="warnInfo"><div class="warn_icon"></div>'+data.msg+'</div>');
                return;
            }
            if(data.page_status !== "1") {
                
                document.title = '提醒';
                $('#chat-box').html('<div class="warnInfo"><div class="warn_icon"></div>该页面已被管理员停止使用</div>');
                return;
            }
            
            // 渲染页面
            $(document).ready(function () {
                let chatContent = $("#chat-content");
                let jumpBtn = $("#jump-btn").hide();
                
                // 设置banner
                $(".banner-view").html(`<img id="page-banner" src="${data.page_banner}">`);
                
                function appendMessage(type, text, avatar, options = []) {
                    let messageHtml = `
                        <div class="message-container ${type === 'user' ? 'user-container' : ''}">
                            ${type === 'bot' ? `<img class="avatar" src="${avatar}">` : ''}
                            <div class="message ${type}">
                                ${text}
                                ${options.length > 0 ? `<div class="options-container">${options.map(option => 
                                    `<span class="option" data-type="${option.response_type}" 
                                    data-content="${option.response_content}" 
                                    data-next="${option.next_questionid}">${option.text}</span>`).join('')}
                                </div>` : ''}
                            </div>
                            ${type === 'user' ? `<img class="avatar" src="${avatar}">` : ''}
                        </div>`;
                    chatContent.append(messageHtml);
                    chatContent.scrollTop(chatContent.prop("scrollHeight"));
                }
                
                // 监听点击选项事件
                function handleOptionClick() {
                    let option = $(this);
                    let text = option.text();
                    let responseType = option.data("type");
                    let responseContent = option.data("content");
                    let nextQuestionId = option.data("next");
        
                    appendMessage("user", text, data.customer_avatar);
        
                    setTimeout(() => {
                        if (responseType === "text") {
                            appendMessage("bot", responseContent, data.my_avatar);
                        } else if (responseType === "next_question") {
                            let nextQuestion = data.questions.find(q => q.questionid == nextQuestionId);
                            if (nextQuestion) {
                                appendMessage("bot", nextQuestion.text, data.my_avatar, nextQuestion.options);
                            }
                        } else if (responseType === "end") {
                            appendMessage("bot", data.end_msg, data.my_avatar);
                            jumpBtn.text(data.jump_btn_text).css({
                                "background-color": data.jump_btn_bg_color,
                                "border-radius": data.jump_btn_border_radius
                            }).attr("href", data.jump_btn_link).show();
                            
                            // 如果后台还没有配置任何的目标链接
                            if(!data.jump_btn_link) {
                                jumpBtn.attr("href", "javascript:alert('管理员暂未配置跳转链接！')").removeAttr("target");
                            }
                            if(data.jump_btn_animation == 1) {
                                jumpBtn.addClass('anim-high');
                            }else if(data.jump_btn_animation == 2) {
                                jumpBtn.addClass('anim-medium');
                            }else if(data.jump_btn_animation == 3) {
                                jumpBtn.addClass('anim-low');
                            }
                        } else if (responseType === "reject") {
                            appendMessage("bot", data.reject_msg, data.my_avatar);
                        }
                    }, 500);
                }
        
                appendMessage("bot", data.welcome_msg, data.my_avatar);
                setTimeout(() => {
                    let firstQuestion = data.questions[0];
                    appendMessage("bot", firstQuestion.text, data.my_avatar, firstQuestion.options);
                }, 500);
                
                $(document).on("click", ".option", handleOptionClick);
            });
        }
        
        // 获取服务器数据
        // 从URL中获取参数
        const urlParams = new URLSearchParams(window.location.search);
        const key = urlParams.get('key'); // 获取 key 参数
        const exportKey = urlParams.get('exportKey'); // 获取 exportKey 参数
        if (key && exportKey) {
            
            // 请求
            getData(key, exportKey);
        }else {
            
            // 参数缺失
            document.title = '提醒';
            $('#chat-box').html('<div class="warnInfo"><div class="warn_icon"></div>参数缺失，无法加载页面！</div>');
        }
    </script>
</html>