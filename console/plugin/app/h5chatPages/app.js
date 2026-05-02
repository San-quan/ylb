
// 打开网页就是从这里开始执行代码
window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取安装状态
    getSetupStatu();
    
    // clipboard插件
    var clipboard = new ClipboardJS('#sharePageModal .modal-footer button');
    clipboard.on('success', function(e) {
        
        // 复制成功
        $('#sharePageModal .modal-footer button').text('已复制');
    });
    
    // 时间选择器默认值
    function getOneMonthLater() {
        let now = new Date();
        now.setMonth(now.getMonth() + 1); // 增加1个月
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset()); // 处理时区偏移
    
        return now.toISOString().slice(0, 16); // 格式化 YYYY-MM-DDTHH:MM
    }
    document.getElementById('page_expire_time_picker').value = getOneMonthLater();
}

// 获取登录状态
function getLoginStatus(){
    
    // 获取
    $.ajax({
        type: "POST",
        url: "../../../login/getLoginStatus.php",
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 已登录
                // 账号及版本信息
                var $account = $(
                    '<div class="version">'+res.version+'</div>' +
                    '<div class="user_name">'+res.user_name+' <span onclick="exitLogin();" class="exitLogin">退出</span></div>'
                );
                $(".left .account").html($account);
                
                // 初始化
                initialize_Login('login',res.user_admin)
            }else{
                
                // 未登录
                // 账号及版本信息
                var $account = $(
                    '<div class="version">'+res.version+'</div>' +
                    '<div class="user_name">未登录</div>'
                );
                $(".left .account").html($account);
                
                // 初始化
                initialize_Login('unlogin');
            }
        },
        error: function() {
            
            // 服务器发生错误
            errorPage('data-list','getLoginStatus.php');
        }
    });
}

// 登录初始化
function initialize_Login(loginStatus,adminStatus){
    
    if(loginStatus == 'login'){
        
        // 显示创建按钮
        $('#button-view').css('display','block');
        
    }else{
        
        // 隐藏创建按钮
        $('#button-view').css('display','none');
    }
}

// 获取安装状态
function getSetupStatu() {
    
    $.ajax({
        type: "POST",
        url: "server/getSetupStatu.php",
        success: function(res){
            
            // 显示data-list节点
            $('#right .data-list').css('display','block');
            
            if(res.code == 200){
                
                // 未安装
                noData(res.msg);
            }else {
                
                // 获取页码
                var pageNum = queryURLParams(window.location.href).p;
                
                if(pageNum !== 'undefined'){
                    
                    // 获取当前页码数据列表
                    getJwList(pageNum);
                }else{
                    
                    // 获取首页
                    getJwList();
                }
            }
        },
        error: function() {
            
            // 服务器发生错误
            noData('getSetupStatu.php服务器发生错误');
        }
    });
}

// 获取列表
function getJwList(pageNum) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "server/getJwList.php";
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "server/getJwList.php?p="+pageNum
        
        // 设置URL路由
        setRouter(pageNum);
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_getJwList();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>落地页id</th>' +
                '   <th>标题</th>' +
                '   <th>你的头像</th>' +
                '   <th>客户头像</th>' +
                '   <th>访问限制</th>' +
                '   <th>访问次数</th>' +
                '   <th>创建时间</th>' +
                '   <th>到期时间</th>' +
                '   <th>状态</th>' +
                '   <th>操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.jwList.length; i++) {
                                        
                    // 访问限制
                    if(res.jwList[i].limitation == 1) {
                        var limitation_text = '不限制';
                    }else if(res.jwList[i].limitation == 2) {
                        var limitation_text = '只允许在手机打开';
                    }else if(res.jwList[i].limitation == 3) {
                        var limitation_text = '只允许在微信内打开';
                    }else if(res.jwList[i].limitation == 4) {
                        var limitation_text = '只允许在QQ内打开';
                    }else if(res.jwList[i].limitation == 5) {
                        var limitation_text = '只允许在抖音内打开';
                    }
                    
                    // 单行数据对象
                    // 用于编辑、查看、分享时的参数传递
                    var h5chatPagesInfoObject = {
                        page_id: res.jwList[i].page_id,
                        page_title: res.jwList[i].page_title,
                        page_banner: res.jwList[i].page_banner,
                        page_expire_time: res.jwList[i].page_expire_time,
                        customer_avatar: res.jwList[i].customer_avatar,
                        my_avatar: res.jwList[i].my_avatar,
                        limitation: res.jwList[i].limitation,
                        welcome_msg: res.jwList[i].welcome_msg,
                        end_msg: res.jwList[i].end_msg,
                        reject_msg: res.jwList[i].reject_msg,
                        jump_btn_text: res.jwList[i].jump_btn_text,
                        jump_btn_bg_color: res.jwList[i].jump_btn_bg_color,
                        jump_btn_border_radius: res.jwList[i].jump_btn_border_radius,
                        jump_btn_link: res.jwList[i].jump_btn_link,
                        jump_btn_animation: res.jwList[i].jump_btn_animation,
                        page_dlym: res.jwList[i].page_dlym,
                        page_rkym: res.jwList[i].page_rkym,
                        page_ldym: res.jwList[i].page_ldym,
                        targetlink_mode: res.jwList[i].targetlink_mode,
                        chatData: res.jwList[i].chatData
                    };
                    
                    // 状态切换
                    if(res.jwList[i].page_status == 1){
                        
                        // 正常
                        var page_status = 
                        '<span class="switch-on" id="'+res.jwList[i].page_id+'" onclick="changePageStatus(this);">' +
                        '   <span class="press"></span>' +
                        '</span>';
                    }else{
                        
                        // 关闭
                        var page_status = 
                        '<span class="switch-off" id="'+res.jwList[i].page_id+'" onclick="changePageStatus(this);">' +
                        '   <span class="press"></span>' +
                        '</span>';
                    }
                    
                    // 格式化到期时间
                    function formatDateTime(datetimeLocal) {
                        let date = new Date(datetimeLocal);
                        let year = date.getFullYear();
                        let month = String(date.getMonth() + 1).padStart(2, "0"); // 月份从 0 开始
                        let day = String(date.getDate()).padStart(2, "0");
                        let hours = String(date.getHours()).padStart(2, "0");
                        let minutes = String(date.getMinutes()).padStart(2, "0");
                        return `${year}-${month}-${day} ${hours}:${minutes}`;
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+res.jwList[i].page_id+'</td>' +
                        '   <td>'+res.jwList[i].page_title+'</td>' +
                        '   <td><img src="'+ res.jwList[i].customer_avatar +'" width="35" /></td>' +
                        '   <td><img src="'+ res.jwList[i].my_avatar +'" width="35" /></td>' +
                        '   <td>'+limitation_text+'</td>' +
                        '   <td>'+res.jwList[i].page_pv+'</td>' +
                        '   <td>'+res.jwList[i].page_create_time+'</td>' +
                        '   <td>'+formatDateTime(res.jwList[i].page_expire_time)+'</td>' +
                        '   <td>'+page_status+'</td>' +
                        '   <td class="cz-tags">' +
                        '       <span class="light-tag" data-toggle="modal" data-target="#sharePageModal" onclick="sharePage('+res.jwList[i].page_id+')">分享</span>' +
                        '       <span class="light-tag" data-toggle="modal" data-target="#editMultiJumpLinkModal" onclick=\'geth5chatPagesInfo('+JSON.stringify(h5chatPagesInfoObject)+')\'>编辑</span>' +
                        '       <span class="light-tag" data-toggle="modal" data-target="#targetLinksModal" onclick=\'targetLinksModal('+JSON.stringify(h5chatPagesInfoObject)+')\' title="底部按钮的跳转链接">跳转</span>' +
                        '       <span class="light-tag" data-toggle="modal" data-target="#editChatMsgModal" onclick=getChatData('+res.jwList[i].page_id+')>对话</span>' +
                        '       <span class="light-tag" data-toggle="modal" data-target="#delJwModal" onclick="delPageConfirmModal('+res.jwList[i].page_id+')">删除</span>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#right .data-list tbody").append($tbody_HTML);
                }
                
                // 分页组件
                fenyeComponent(res.page,res.allpage,res.nextpage,res.prepage);
                
                // 将Appid设置到按钮中
                $('.openQywxXcxConfig').attr('onclick', 'getQywxKfXcxConfig("'+res.appid+'");');
            }else{
                
                // 未登录
                if(res.code == 201){
                    
                    // 跳转到登录页面
                    jumpUrl('../../../login/');
                }
                
                // 非200状态码
                noData(res.msg);
            }
      },
      error: function(){
        
        // 发生错误
        errorPage('data-list','getZjyList.php');
      },
    });
}

// 分页组件
function fenyeComponent(thisPage,allPage,nextPage,prePage){
    
    if(thisPage == 1 && allPage == 1){
        
        // 当前页码=1 且 总页码=1
        // 无需显示分页控件
        $("#right .data-card .fenye").css("display","none");
        
    }else if(thisPage == 1 && allPage > 1){
        
        // 当前页码=1 且 总页码>1
        // 代表还有下一页
        var $fenyeComponent_HTML = $(
        '<ul>' +
        '   <li>'+ 
        '       <button id="'+nextPage+'" onclick="getFenye(this);" title="下一页">'+ 
        '           <img src="../../../../static/img/nextPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+allPage+'" onclick="getFenye(this);" title="最后一页">'+ 
        '           <img src="../../../../static/img/lastPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        
    }else if(thisPage == allPage){
        
        // 当前页码=总页码
        // 代表这是最后一页
        var $fenyeComponent_HTML = $(
        '<ul>' +
        '   <li>'+ 
        '       <button id="1" onclick="getFenye(this);" title="第一页">'+ 
        '           <img src="../../../../static/img/firstPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '   <button id="'+prePage+'" onclick="getFenye(this);" title="上一页">'+ 
        '       <img src="../../../../static/img/prevPage.png" />'+ 
        '   </button>'+ 
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        
    }else{
        
        var $fenyeComponent_HTML = $(
        '<ul>' +
        '   <li>'+ 
        '       <button id="1" onclick="getFenye(this);" title="第一页">'+ 
        '           <img src="../../../../static/img/firstPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+prePage+'" onclick="getFenye(this);" title="上一页">'+ 
        '           <img src="../../../../static/img/prevPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+nextPage+'" onclick="getFenye(this);" title="下一页">'+ 
        '           <img src="../../../../static/img/nextPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '   <li>'+ 
        '       <button id="'+allPage+'" onclick="getFenye(this);" title="最后一页">'+ 
        '           <img src="../../../../static/img/lastPage.png" />'+ 
        '       </button>'+ 
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        
    }
    
    // 渲染分页组件
    $("#right .data-card .fenye").html($fenyeComponent_HTML);
}

// 获取分页数据
function getFenye(e){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    getJwList(pageNum);
}

// 打开目标链接管理Modal
function targetLinksModal(h5chatPagesInfoObject) {
    
    // 将模式、page_id填写到表单
    $('#targetLinksModal input[name="page_id"]').val(h5chatPagesInfoObject.page_id);
    $('#targetLinksModal input[name="mode"]').val(h5chatPagesInfoObject.targetlink_mode);
    
    // 初始化表单
    $('#targetLinksModal input[name="targetlink"]').val('');
    $('#targetLinksModal input[name="yz"]').val('');
    
    // 开始获取
    getTargetLinks(h5chatPagesInfoObject.page_id);
}

// 获取目标链接列表
function getTargetLinks(page_id) {
    
    // 初始化
    $('.nolinks').css('display','none');
    $("#targetLinksModal .modal-body .card-list").html('');
    
    $.ajax({
        type: "POST",
        url: "server/targetLinks.php?page_id="+page_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 加载数据列表
                for (var i=0; i<res.targetlinks.length; i++) {
                    
                    var $link_card_HTML = $(
                        '<div class="link_card">' + 
                        '    <span class="link">'+res.targetlinks[i].targetlink+'</span>' + 
                        '    <span class="number-span" title="点击可修改阈值"><span class="num-tag">阈值 '+res.targetlinks[i].targetlink_yz+' <span class="editYz" onclick="editYzConfirm('+res.targetlinks[i].targetlink_id+')">📝</span></span></span></span>' + 
                        '    <span class="number-span"><span class="num-tag">点击 '+res.targetlinks[i].targetlink_pv+' </span></span>' + 
                        '    <span class="delete" onclick="delTargetLinkConfirm('+res.targetlinks[i].targetlink_id+')">删除</span>' + 
                        '</div>'
                    );
                    $("#targetLinksModal .modal-body .card-list").append($link_card_HTML);
                }
            }else{
                
                // 暂无数据或者获取失败
                $('.nolinks').css('display','block');
                $('.nolinks').text(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            alert('server/targetlinks.php服务器发生错误');
        }
    });
}

// 添加目标链接
function addTargetLink() {
    
    $.ajax({
        type: "POST",
        url: "server/addTargetLink.php",
        data: $('#addTargetLink').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 刷新列表
                getTargetLinks(res.page_id);
                
                // 初始化表单
                $('#targetLinksModal input[name="targetlink"]').val('');
                $('#targetLinksModal input[name="yz"]').val('');
            }else{
                
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            alert('server/addTargetLink.php服务器发生错误');
        }
    });
}

// 删除目标链接
function delTargetLinkConfirm(targetlink_id) {
    let isConfirmed = confirm("确定要删除这个链接吗？");
    if (isConfirmed) {
        $.ajax({
            type: "POST",
            url: "server/delTargetLink.php?targetlink_id="+targetlink_id,
            success: function(res){
                
                // 成功
                if(res.code == 200){

                    // 刷新列表
                    getTargetLinks(res.page_id);
                }else{
                    
                    showErrorResult(res.msg)
                }
            },
            error: function() {
                
                // 服务器发生错误
                alert('server/delTargetLink.php服务器发生错误');
            }
        });
    }
}

// 编辑阈值
function editYzConfirm(targetlink_id) {
    let yuzhi = prompt("请输入阈值：", "");
    if (yuzhi !== null) {
        $.ajax({
            type: "POST",
            url: "server/setYz.php?targetlink_id="+targetlink_id+"&yuzhi="+yuzhi,
            success: function(res){
                
                // 成功
                if(res.code == 200){

                    // 刷新列表
                    getTargetLinks(res.page_id);
                }else{
                    
                    showErrorResult(res.msg)
                }
            },
            error: function() {
                
                // 服务器发生错误
                alert('server/setYz.php服务器发生错误');
            }
        });
    }
}

// 获取当前页面的对话数据
function getChatData(page_id) {
    
    // 将 page_id 设置到表单
    $('#editChatMsgModal input[name="page_id"]').val(page_id);
    
    // 获取最新的对话数据
    $.ajax({
        type: "GET",
        url: "server/getChatData.php?page_id="+page_id,
        success: function(res){
            
            if(res.code == 200) {
                
                // 获取成功
                // 将对话数据添加到表单
                $('#editChatMsgModal input[name="chatData"]').val(res.chatData);
                
                // 问题编号起始值
                let questionCounter = 1;
            
                $(document).ready(function() {
                    
                    // 添加问题
                    $('#add-question').off('click').on('click', function() {
                        addQuestion();
                    });
                    
                    // 添加选项
                    $(document).off('click', '.add-option').on('click', '.add-option', function() {
                        let questionGroup = $(this).closest('.question-group');
                        addOption(questionGroup);
                    });
                    
                    // 监听触发类型的变化
                    $(document).off('change', '.response-type').on('change', '.response-type', function() {
                        let responseType = $(this).val();
                        let parent = $(this).closest('.option');
                        let responseContent = parent.find('.response-content');
                        let nextQuestionSelect = parent.find('.next-question-select');
            
                        if (responseType === 'next_question') {
                            responseContent.hide().val("");
                            nextQuestionSelect.show();
                        } else {
                            nextQuestionSelect.hide();
                            responseContent.toggle(responseType !== 'end' && responseType !== 'reject');
                        }
                    });
                    
                    // 移除问题
                    $(document).off('click', '.remove-question').on('click', '.remove-question', function() {
                        $(this).parent('.question-group').remove();
                        updateQuestionOptions();
                    });
                    
                    // 移除选项
                    $(document).off('click', '.remove-option').on('click', '.remove-option', function() {
                        $(this).parent('.option').remove();
                    });
                    
                    // 保存对话数据
                    $('#editChatMsg').off('submit').on('submit', function(event) {
                        event.preventDefault();
                        saveData();
                    });
                    
                    // 从服务器中获取JSONDATA
                    let jsonData = $('#editChatMsgModal input[name="chatData"]').val();
                    console.log(JSON.parse(jsonData))
                    
                    // 开始加载服务器中的数据到表单中
                    loadData(JSON.parse(jsonData));
                    
                    console.log('数据加载完毕')
                });
                
                // 添加问题（函数）
                function addQuestion(questionData = null) {
                    let questionId = questionData ? questionData.questionid : questionCounter++;
                    let questionText = questionData ? questionData.text : "";
            
                    let questionHtml = `
                        <div class="question-group" data-id="${questionId}">
                            <label><strong>问题${questionId}</strong></label><span class="remove-question">删除问题</span>
                            <input type="text" class="question-text" value="${questionText}" placeholder="请输入问题"/><br/>
                            <button class="add-option">添加选项</button>
                            <div class="options-container"></div>
                        </div>
                    `;
                    $('#questions-container').append(questionHtml);
            
                    if (questionData) {
                        let questionGroup = $(`.question-group[data-id="${questionId}"]`);
                        questionData.options.forEach(option => addOption(questionGroup, option));
                    }
            
                    updateQuestionOptions();
                }
                
                // 添加选项（函数）
                function addOption(questionGroup, optionData = null) {
                    let optionText = optionData ? optionData.text : "";
                    let responseType = optionData ? optionData.response_type : "text"; 
                    let responseContent = optionData ? optionData.response_content : "";
                    let nextQuestionId = optionData ? optionData.next_questionid : ""; // 可能为空
                
                    let optionHtml = `
                        <div class="option">
                            <label>选项文字：</label>
                            <input type="text" class="option-text" value="${optionText}" placeholder="输入选项内容"/>
                            <label>点击后的动作：</label>
                            <select class="response-type">
                                <option value="text">回复文字</option>
                                <option value="end">结束</option>
                                <option value="reject">拒绝</option>
                                <option value="next_question">触发</option>
                            </select>
                            <select class="next-question-select" style="display: none;"></select>
                            <input type="text" class="response-content" value="${responseContent}" placeholder="输入回复内容" style="display: none;"/>
                            <span class="remove-option">×</span>
                        </div>
                    `;
                
                    let optionsContainer = questionGroup.find('.options-container');
                    optionsContainer.append(optionHtml);
                
                    let newOption = optionsContainer.find('.option').last();
                    let responseTypeSelect = newOption.find('.response-type');
                    let nextQuestionSelect = newOption.find('.next-question-select');
                
                    updateQuestionOptions(); // 确保选项列表最新
                
                    // 先设置 responseType 并触发事件
                    responseTypeSelect.val(responseType);
                    responseTypeSelect.trigger('change');
                
                    // 如果 responseType 是 "next_question"，需要处理 nextQuestionSelect
                    if (responseType === "next_question") {
                        // 等待 DOM 更新后再设置 nextQuestionId
                        setTimeout(() => {
                            // 这里确保 select 设置了 "next_question" 并触发
                            responseTypeSelect.val("next_question").trigger('change');
                            
                            // 显示 nextQuestionSelect 下拉菜单
                            nextQuestionSelect.show();
                
                            // 等待下拉列表完全渲染后再设置选中项
                            setTimeout(() => {
                                // 选中 nextQuestionId，并确保其值设置正确
                                if (nextQuestionId) {
                                    nextQuestionSelect.val(nextQuestionId); // 设置当前选中的问题 ID
                                }
                            }, 100); // 延迟确保 select 元素更新完成
                        }, 100); // 延迟确保下拉列表已经正确渲染
                    }
                }
                
                // 加载数据（函数）
                function loadData(data) {
                    $('#questions-container').empty();
                    questionCounter = data.questions.length > 0 
                        ? Math.max(...data.questions.map(q => q.questionid), 0) + 1 
                        : 1;
                
                    data.questions.forEach(question => addQuestion(question));
                
                    // ✅ 确保数据加载后，正确触发 UI 更新
                    setTimeout(() => {
                        $('.question-group').each(function() {
                            let questionGroup = $(this);
                            questionGroup.find('.option').each(function() {
                                let option = $(this);
                                let responseType = option.find('.response-type').val();
                                
                                if (responseType === "next_question") {
                                    option.find('.response-type').val("next_question").trigger('change');
                                    option.find('.next-question-select').val(option.data('next-questionid')).show();
                                }
                            });
                        });
                    }, 150);
                }
                
                // 更新选项（函数）
                function updateQuestionOptions() {
                    let questionGroups = $('.question-group');
                    let questionOptions = questionGroups.map(function() {
                        return `<option value="${$(this).data('id')}">问题${$(this).data('id')}</option>`;
                    }).get().join('');
                
                    questionGroups.each(function() {
                        let currentQuestionId = $(this).data('id');
                        $(this).find('.option').each(function() {
                            let responseTypeSelect = $(this).find('.response-type');
                            let nextQuestionSelect = $(this).find('.next-question-select');
                
                            if (questionGroups.length > 1) {
                                if (!responseTypeSelect.find('option[value="next_question"]').length) {
                                    responseTypeSelect.append('<option value="next_question">触发</option>');
                                }
                                nextQuestionSelect.html(questionOptions);
                                nextQuestionSelect.find(`option[value="${currentQuestionId}"]`).remove(); // 防止选自己
                            } else {
                                responseTypeSelect.find('option[value="next_question"]').remove();
                                nextQuestionSelect.hide();
                            }
                        });
                    });
                
                    // ✅ 确保 next_question 选项正确赋值
                    $('.option').each(function() {
                        let nextQuestionId = $(this).data('next-questionid'); 
                        let nextQuestionSelect = $(this).find('.next-question-select');
                        if (nextQuestionSelect.length > 0 && nextQuestionId) {
                            nextQuestionSelect.val(nextQuestionId);
                        }
                    });
                }
                
                // 保存对话数据（函数）
                function saveData() {
                    let questions = [];
                    $('.question-group').each(function() {
                        let questionData = {
                            questionid: $(this).data('id'),
                            text: $(this).find('.question-text').val(),
                            options: []
                        };
                        $(this).find('.option').each(function() {
                            let responseType = $(this).find('.response-type').val();
                            questionData.options.push({
                                text: $(this).find('.option-text').val(),
                                response_type: responseType,
                                response_content: responseType !== "next_question" ? $(this).find('.response-content').val() : "",
                                next_questionid: responseType === "next_question" ? $(this).find('.next-question-select').val() : ""
                            });
                        });
                        questions.push(questionData);
                    });
                    
                    // 获取到数据
                    const chatData = JSON.stringify({ questions }, null, 2);
                    
                    // 正在保存数据...
                    $('#editChatMsgModal .saveChatData_btn_text').text('正在保存数据...');
                    
                    // 将数据POST给服务器完成更新
                    setTimeout(function() {
                        
                        if(page_id) {
                            $.ajax({
                                type: "GET",
                                url: "server/saveChatData.php?chatData="+chatData+"&page_id="+page_id,
                                success: function(res){
                                    
                                    if(res.code == 200) {
                                        
                                        // 保存成功
                                        $('#editChatMsgModal .saveChatData_btn_text').text(res.msg);
                                        
                                        // 将最新的数据填充到表单中
                                        $('#editChatMsgModal input[name="chatData"]').val(JSON.stringify({questions}))
                                    }else {
                                        
                                        // 失败
                                        $('#editChatMsgModal .saveChatData_btn_text').text(res.msg);
                                    }
                                },
                                error: function() {
                                    
                                    // 服务器发生错误
                                    showErrorResultForphpfileName('saveChatData.php');
                                }
                            });
                        }
                    }, 1500);
                    
                    // 控制台显示完整数据
                    console.log(chatData);
                    
                    // 恢复按钮文字
                    setTimeout(function() {
                        $('#editChatMsgModal .saveChatData_btn_text').text('保存对话数据');
                    }, 2500);
                }
            }else {
                
                // 失败
                showErrorResult(res.chatData);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getChatData.php');
        }
    });
}

// 创建链接
function createJw(){
    
    $.ajax({
        type: "POST",
        url: "server/createJw.php",
        data: $('#createJw').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 操作反馈（操作成功）
                showSuccessResult(res.msg)
                
                // 隐藏modal
                setTimeout('hideModal("createJwModal")', 500);
                
                // 重新加载列表
                setTimeout('getJwList();', 500);
            }else{
                
                // 操作反馈（操作失败）
                if(res.code == 101) {
                    
                    showErrorResult(res.msg+'<a href="'+res.buy_link+'" target="_blank">'+res.buy_link+'</a>')
                }else {
                    
                    showErrorResult(res.msg)
                }
                
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('createJw.php');
        }
    });
}

// 删除确认
function delPageConfirmModal(page_id){
    
    // 将 page_id 添加到确认按钮
    $('#delJwModal .modal-footer').html(
        '<button type="button" class="default-btn center-btn" onclick="delJw('+page_id+');">确认删除</button>'
    )
}

// 执行删除
function delJw(page_id){

    $.ajax({
        type: "GET",
        url: "server/delJw.php?page_id=" + page_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                hideModal("delJwModal");
                
                // 重新加载列表
                setTimeout('getJwList()', 500);
                
                // 显示删除结果
                setTimeout('showNotification("'+res.msg+'")', 600);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('delJw.php');
        }
    });
}

// 获取落地页详情
function geth5chatPagesInfo(h5chatPagesInfoObject){

    // 获取域名列表
    getDomainNameList('edit');
    
    // 当前设置的域名
    setTimeout(function() {
        $("#editJwModal select[name='page_dlym']").val(h5chatPagesInfoObject.page_dlym);
        $("#editJwModal select[name='page_rkym']").val(h5chatPagesInfoObject.page_rkym);
        $("#editJwModal select[name='page_ldym']").val(h5chatPagesInfoObject.page_ldym);
    },100)
    
    // 将落地页详情数据填写到表单中
    $('#editJwModal input[name="page_title"]').val(h5chatPagesInfoObject.page_title);
    $('#editJwModal input[name="page_banner"]').val(h5chatPagesInfoObject.page_banner);
    $('#editJwModal input[name="page_expire_time"]').val(h5chatPagesInfoObject.page_expire_time);
    $('#editJwModal input[name="customer_avatar"]').val(h5chatPagesInfoObject.customer_avatar);
    $('#editJwModal input[name="my_avatar"]').val(h5chatPagesInfoObject.my_avatar);
    $('#editJwModal textarea[name="welcome_msg"]').val(h5chatPagesInfoObject.welcome_msg);
    $('#editJwModal textarea[name="end_msg"]').val(h5chatPagesInfoObject.end_msg);
    $('#editJwModal textarea[name="reject_msg"]').val(h5chatPagesInfoObject.reject_msg);
    $('#editJwModal input[name="jump_btn_text"]').val(h5chatPagesInfoObject.jump_btn_text);
    $('#editJwModal input[name="jump_btn_bg_color"]').val(h5chatPagesInfoObject.jump_btn_bg_color);
    $('#editJwModal textarea[name="jump_btn_link"]').val(h5chatPagesInfoObject.jump_btn_link);
    $('#editJwModal input[name="page_id"]').val(h5chatPagesInfoObject.page_id);
    $('#editJwModal select[name="targetlink_mode"]').val(h5chatPagesInfoObject.targetlink_mode); // 模式
    
    // 到期时间
    
    // 按钮动画频率
    $("#editJwModal select[name='jump_btn_animation']").val(h5chatPagesInfoObject.jump_btn_animation);
    
    // 访问限制
    $("#editJwModal select[name='limitation']").val(h5chatPagesInfoObject.limitation);
    
    // 按钮圆角边框
    $("#editJwModal select[name='jump_btn_border_radius']").val(h5chatPagesInfoObject.jump_btn_border_radius);
    
    // 显示Modal
    showModal('editJwModal');
}

// 编辑链接
function editJw(){
    
    $.ajax({
        type: "POST",
        url: "server/editJw.php",
        data: $('#editJw').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("editJwModal")', 500);
                
                // 重新加载列表
                setTimeout('getJwList();', 500);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('editJw.php');
        }
    });
}

// 分享落地页
function sharePage(page_id){
    
    // 初始化二维码
    $("#shareQrcode").html('');

    $.ajax({
        type: "GET",
        url: "server/shareJw.php?page_id="+page_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 短链接
                $("#shortUrl").html('<span id="page_'+page_id+'">' + res.shortUrl + '</span>');
                
                // 长链接
                $("#longUrl").html('<span>' + res.longUrl + '</span>');
                
                // 二维码
                new QRCode(document.getElementById("shareQrcode"), res.qrcodeUrl);
                
                // 复制按钮
                $('#sharePageModal .modal-footer').html(
                    '<button class="default-btn" data-clipboard-action="copy" data-clipboard-target="#page_'+page_id+'">复制短链接</button>'
                );
            }else{
                
                // 失败
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('shareJw.php');
        }
    });
}

// 切换状态
function changePageStatus(e) {
    
    $.ajax({
        type: "POST",
        url: "server/changePageStatus.php?page_id="+e.id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                getJwList();
                showNotification(res.msg)
            }else{
                
                // 操作失败
                showNotification(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showNotification('服务器发生错误');
        }
    });
}

// 获取企业微信客服小程序配置
function getQywxKfXcxConfig(appid) {
    
    $.ajax({
        type: "POST",
        url: "./server/getQywxKfXcxConfig.php?appid="+appid,
        success: function(res){
            
            // 成功
            // 将获取到的值填写到表单
            if(res.code == 200) {
                
                $('.modal-body input[name="appid"]').val(res.qywxKfXcxConfig.appid);
                if(res.qywxKfXcxConfig.banner) {
                    $('.modal-body input[name="banner"]').val(res.qywxKfXcxConfig.banner);
                    $('.banner-up-text').text('重新上传');
                }
                $('.modal-body input[name="bannerClickUrl"]').val(res.qywxKfXcxConfig.bannerClickUrl);
                $('.modal-body textarea[name="service_content"]').val(res.qywxKfXcxConfig.service_content);
                $('.modal-body input[name="kfQrUrl"]').val(res.qywxKfXcxConfig.kfQrUrl);
                $('.modal-body input[name="kfQrBgimg"]').val(res.qywxKfXcxConfig.kfQrBgimg);
                if(res.qywxKfXcxConfig.kfQrUrl) {
                    
                    // 显示预览的图片
                    $('.kf-qrcode-view').css('display','none');
                    $('.up-title').html(`
                        <span class="re-upload-span" onclick="reUploadKfQrcode()">+ 重新上传</span>
                    `);
                    $('.preview-qrcode').html('<img src="'+res.qywxKfXcxConfig.kfQrUrl+'" />');
                    $('.preview-qrcode').css('display','block');
                }
                $('.bannerClickNum').text(res.qywxKfXcxConfig.bannerClickNum);
                $('.kfQrPagePv').text(res.qywxKfXcxConfig.kfQrPagePv);
                $('.KfQrBtnClickCount').text(res.qywxKfXcxConfig.KfQrBtnClickCount);
            }
        },
        error: function() {
            
            // 服务器发生错误
            alert('getQywxKfXcxConfig.php 服务器发生错误');
        }
    });
}

// 保存小程序配置
function setQywxKfXcxConfig(){
    
    $.ajax({
        type: "POST",
        url: "server/setQywxKfXcxConfig.php",
        data: $('#qywxKfXcx').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("qywxKfXcxModal")', 1000);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('setQywxKfXcxConfig.php');
        }
    });
}

// 重置客服数据
function resetKfData() {
    
    if (confirm("确定要重置数据吗？")) {
        $.ajax({
            type: "POST",
            url: "./server/resetKfData.php",
            success: function(res){
                
                // 成功
                // 将获取到的值填写到表单
                if(res.code == 200) {
                    
                    showSuccessResult(res.msg);
                    setTimeout('hideModal("qywxKfXcxModal")', 1000);
                }else {
                    
                    alert(res.msg)
                }
            },
            error: function() {
                
                // 服务器发生错误
                alert('resetKfData.php 服务器发生错误');
            }
        });
    }
}

// 注销登录
function exitLogin(){
    
    $.ajax({
        type: "POST",
        url: "../../../login/exitLogin.php",
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 刷新
                location.href = '../../../login/';
            }
        },
        error: function() {
            
            // 服务器发生错误
            errorPage('data-list','exitLogin.php');
        }
    });
}

// 使用 appendOptionsToSelect函数来为每个select元素处理选项的添加
function appendOptionsToSelect(selectElement, dataList) {
    
    if (dataList.length > 0) {
        
        // 有域名
        for (var i = 0; i < dataList.length; i++) {
            
            // 添加至指定的节点
            selectElement.append(
                '<option value="' + dataList[i].domain + '">' + dataList[i].domain + '</option>'
            );
        }
    } else {
        
        // 暂无域名
        selectElement.append('<option value="">暂无域名</option>');
    }
}

// 获取域名列表
function getDomainNameList(module){
    
    // 初始化
    initialize_getDomainNameList(module);

    // 获取
    $.ajax({
        type: "GET",
        url: "../../../public/getDomainNameList.php",
        success: function (res) {
            
            // 成功
            if (res.code == 200) {
                
                // 创建
                appendOptionsToSelect($("#createJwModal select[name='page_dlym']"), res.dlymList);
                appendOptionsToSelect($("#createJwModal select[name='page_rkym']"), res.rkymList);
                appendOptionsToSelect($("#createJwModal select[name='page_ldym']"), res.ldymList);
                
                // 创建客服落地页
                appendOptionsToSelect($("#createKflandpageModal select[name='kf_ldym']"), res.ldymList);
                
                // 编辑
                appendOptionsToSelect($("#editJwModal select[name='page_dlym']"), res.dlymList);
                appendOptionsToSelect($("#editJwModal select[name='page_rkym']"), res.rkymList);
                appendOptionsToSelect($("#editJwModal select[name='page_ldym']"), res.ldymList);
                
                // 获取默认banner、我的头像、客户头像
                const imgURL = window.location.href + 'img/';
                const page_banner = imgURL + 'page_banner.jpg';
                const customer_avatar = imgURL + 'customer_avatar.jpg';
                const my_avatar = imgURL + 'my_avatar.jpg';
                
                // 填写到表单
                $('#createJwModal input[name="page_banner"]').val(page_banner);
                $('#createJwModal input[name="customer_avatar"]').val(customer_avatar);
                $('#createJwModal input[name="my_avatar"]').val(my_avatar);
            } else {
                
                // 操作失败
                showErrorResult(res.msg);
            }
        },
        error: function () {
            
            // 服务器发生错误
            showErrorResult('服务器发生错误！可按F12打开开发者工具点击Network或网络查看返回信息进行排查！');
        }
    });
}

// 获取素材
function getSuCai(pageNum,fromPannel,fromINput){
    
    // 初始化
    $('#suCaiKu .modal-body .sucai-view').empty('');
    
    // 关闭 fromPannel 界面
    hideModal(fromPannel);
    
    // 打开素材库界面
    showModal('suCaiKu');
    
    // 将 fromPannel 的值设置到隐藏的表单中
    $('#suCaiKu input[name="upload_sucai_fromPannel"]').val(fromPannel);
    
    // 将 fromINput 的值设置到隐藏的表单中
    $('#suCaiKu input[name="upload_sucai_fromINput"]').val(fromINput);
    
    // 判断是否有pageNum参数传过来
    if(pageNum == undefined){
        
        // 没有参数就设置默认值
        var pageNum = 1;
    }
    
    // 获取从哪个面板点击打开的
    if(fromPannel == 'createJwModal'){
        
        // 上一个面板是 createJwModal 
        // 渲染出来的关闭按钮是需要返回 createJwModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'createJwModal\')">&times;</button>'
        );
    }
    
    if(fromPannel == 'editJwModal'){
        
        // 上一个面板是 editJwModal
        // 渲染出来的关闭按钮是需要返回 editJwModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'editJwModal\')">&times;</button>'
        );
    }
    
    if(fromPannel == 'qywxKfXcxModal'){
        
        // 上一个面板是 qywxKfXcxModal
        // 渲染出来的关闭按钮是需要返回 qywxKfXcxModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'qywxKfXcxModal\')">&times;</button>'
        );
    }
    
    if(fromPannel == 'createKflandpageModal'){
        
        // 上一个面板是 createKflandpageModal
        // 渲染出来的关闭按钮是需要返回 createKflandpageModal 的
        $('#suCaiKu .hideSuCaiPannel_closeIcon').html(
            '<button type="button" class="close" data-dismiss="modal" onclick="hideSuCaiPannel(\'createKflandpageModal\')">&times;</button>'
        );
    }

    // 开始获取素材列表
    $.ajax({
        type: "POST",
        url: "../../../public/getSuCaiList.php?p="+pageNum,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 遍历数据
                for (var i=0; i<res.suCaiList.length; i++) {
                    
                    // 素材ID
                    var sucai_id = res.suCaiList[i].sucai_id;
                    
                    // 素材文件名
                    var sucai_filename = res.suCaiList[i].sucai_filename;
                    
                    // 素材备注
                    var sucai_beizhu = res.suCaiList[i].sucai_beizhu;
                    
                    // 选择当前点击的素材的函数
                    var clickFunction = "selectSucaiToForm(" + sucai_id + ", '" + fromPannel.trim() + "', '"+fromINput+"')";
                    
                    var $sucaiList_HTML = $(
                    '<div class="sucai_msg" title="'+sucai_beizhu+'" onclick="'+clickFunction+'">' +
                    '   <div class="sucai_cover">' +
                    '       <img src="../../../upload/'+sucai_filename+'" />' +
                    '   </div>' +
                    '   <div class="sucai_name">'+sucai_filename+'</div>' +
                    '</div>'
                    );
                    
                    // 渲染HTML
                    $('#suCaiKu .modal-body .sucai-view').append($sucaiList_HTML);
                }
            }else{
                
                // 获取失败
                getSuCaiFail(res.msg);
            }
            
            // 分页控件
            if(res.totalNum > 12){
                
                // 渲染分页控件
                suCaifenyeControl(pageNum,fromPannel,fromINput,res.nextpage,res.prepage,res.allpage);
                
            }else{
                
                // 隐藏分页控件
                $('#suCaiKu .fenye').css('display','none');
            }
        },
        error: function() {
            
            // 服务器发生错误
            getSuCaiFail('服务器发生错误，请检查getSuCaiList.php服务是否正常！');
        }
    });
}

// 获取素材失败
function getSuCaiFail(text){
    
    $('#suCaiKu .modal-body .sucai-view').html(
        '<div class="loading">'+
        '   <img src="../../../../static/img/noRes.png" class="noRes"/>' +
        '   <br/><p>'+text+'</p>'+
        '</div>'
    );
}

// 选择当前点击的素材
function selectSucaiToForm(sucai_id,fromPannel,fromINput){
    
    $.ajax({
        type: "POST",
        url: "server/selectSucaiToForm.php?sucai_id="+sucai_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 获取从哪个表单点击的
                if(fromINput == 'banner') {
                    
                    // 将选择的素材设置到 banner 这个表单
                    $('#'+fromPannel+' input[name="page_banner"]').val(res.suCaiUrl);
                }
                if(fromINput == 'customer') {
                
                    // 将选择的素材设置到 customer 这个表单
                    $('#'+fromPannel+' input[name="customer_avatar"]').val(res.suCaiUrl);
                }
                if(fromINput == 'my') {
                    
                    // 将选择的素材设置到 my 这个表单
                    $('#'+fromPannel+' input[name="my_avatar"]').val(res.suCaiUrl);
                }
                if(fromINput == 'kfQrUrl') {
                    
                    // 将选择的素材设置到 kfQrUrl 这个表单
                    $('#'+fromPannel+' input[name="kfQrUrl"]').val(res.suCaiUrl);
                    
                    // 隐藏选择图片的控件
                    $('.kf-qrcode-view').css('display','none');
                    
                    // 设置预览
                    $('.up-title').html(`
                        <span class="re-upload-span" onclick="reUploadKfQrcode()">+ 重新上传</span>
                    `);
                    $('.preview-qrcode').html('<img src="'+res.suCaiUrl+'" />');
                    $('.preview-qrcode').css('display','block');
                }
                if(fromINput == 'banner') {
                    
                    // 将选择的素材设置到 banner 这个表单
                    $('#'+fromPannel+' input[name="banner"]').val(res.suCaiUrl);
                }
                if(fromINput == 'kfQrBgimg') {
                    
                    // 将选择的素材设置到 kfQrBgimg 这个表单
                    $('#'+fromPannel+' input[name="kfQrBgimg"]').val(res.suCaiUrl);
                }
                if(fromINput == 'kf_qrcode') {
                    
                    // 将选择的素材设置到 kf_qrcode 这个表单
                    $('#'+fromPannel+' input[name="kf_qrcode"]').val(res.suCaiUrl);
                    $('#'+fromPannel+' .up-span-text').text('重新上传');
                }

                // 隐藏素材面板
                setTimeout("hideModal('suCaiKu')",500);
                
                // 打开fromPannel的Modal
                setTimeout("showModal('"+fromPannel+"')",600);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('selectSucaiForCreate.php');
        }
    });
    
    // 解决一个bug
    setTimeout("$('body').attr('class', 'modal-open')",1600);
}

// 素材库分页组件
function suCaifenyeControl(thisPage,fromPannel,fromINput,nextPage,prePage,allPage){

    if(thisPage == 1 && allPage == 1){
        
        // 当前页码=1且总页码=1
        // 无需显示分页组件
        $('#suCaiKu .fenye').css('display','none');
        
    }else if(thisPage == 1 && allPage > 1){
        
        // 当前页码=1且总页码>1
        // 代表还有下一页
        var $suCaiFenye = $(
        '<ul>' +
        '   <li>' +
        '       <button title="当前是第一页">' +
        '           <img src="../../../../static/img/firstPage_.png" style="opacity:0.3;" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button title="暂无上一页">' +
        '           <img src="../../../../static/img/prevPage_.png" style="opacity:0.3;" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+nextPage+'_'+fromPannel+'_'+fromINput+'" onclick="getSuCaiFenyeData(this);" title="下一页">' +
        '           <img src="../../../../static/img/nextPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'_'+fromPannel+'_'+fromINput+'" onclick="getSuCaiFenyeData(this);" title="最后一页">' +
        '           <img src="../../../../static/img/lastPage.png" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        
        // 显示组件
        $('#suCaiKu .fenye').css('display','block');
        
    }else if(thisPage == allPage){
        
        // 当前页码=总页码
        // 代表这是最后一页
        var $suCaiFenye = $(
        '<ul>' +
        '   <li>' +
        '       <button id="1_'+fromPannel+'_'+fromINput+'" onclick="getSuCaiFenyeData(this);" title="第一页">' +
        '           <img src="../../../../static/img/firstPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'_'+fromPannel+'_'+fromINput+'" onclick="getSuCaiFenyeData(this);" title="上一页">' +
        '           <img src="../../../../static/img/prevPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button title="暂无下一页">' +
        '           <img src="../../../../static/img/nextPage_.png" style="opacity:0.3;" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button title="当前是最后一页">' +
        '           <img src="../../../../static/img/lastPage_.png" style="opacity:0.3;" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        
        // 显示组件
        $('#suCaiKu .fenye').css('display','block');
        
    }else{
        
        // 其他情况
        // 需要显示所有组件
        var $suCaiFenye = $(
        '<ul>' +
        '   <li>' +
        '       <button id="1_'+fromPannel+'_'+fromINput+'" onclick="getSuCaiFenyeData(this);" title="第一页">' +
        '           <img src="../../../../static/img/firstPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'_'+fromPannel+'_'+fromINput+'" onclick="getSuCaiFenyeData(this);" title="上一页">' +
        '           <img src="../../../../static/img/prevPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+nextPage+'_'+fromPannel+'_'+fromINput+'" onclick="getSuCaiFenyeData(this);" title="下一页">' +
        '           <img src="../../../../static/img/nextPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'_'+fromPannel+'_'+fromINput+'" onclick="getSuCaiFenyeData(this);" title="最后一页">' +
        '           <img src="../../../../static/img/lastPage.png" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        
        // 显示组件
        $('#suCaiKu .fenye').css('display','block');
    }
    
    // 渲染分页组件
    $('#suCaiKu .fenye').html($suCaiFenye);
}

// 获取素材库分页数据
function getSuCaiFenyeData(e){
    
    var FenyeData = e.id;
    var FenyeData_parts = FenyeData.split("_");
    var pageNum = FenyeData_parts[0]; // 页码
    var fromPannel = FenyeData_parts[1]; // 来源Modal
    var fromINput = FenyeData_parts[2]; // 来源表单
    
    // 获取该页列表
    getSuCai(pageNum,fromPannel,fromINput);
}

// 素材库的界面关闭后
// 点击右上角X会返回上一步
function hideSuCaiPannel(fromPannel){
    
    // 先隐藏suCaiKu面板
    hideModal('suCaiKu');
    
    // 根据fromPannel决定打开哪个Modal
    if(fromPannel == 'createJwModal'){
        
        showModal('createJwModal')
    }else if(fromPannel == 'editJwModal'){

        showModal('editJwModal')
    }else if(fromPannel == 'qywxKfXcxModal'){

        showModal('qywxKfXcxModal')
    }else if(fromPannel == 'createKflandpageModal'){

        showModal('createKflandpageModal')
    }
    
    // 解决一个bug
    setTimeout("$('body').attr('class', 'modal-open')",1600);
}

// 设置路由
function setRouter(pageNum){
    
    // 当前页码不等于1的时候
    if(pageNum !== 1){
        window.history.pushState('', '', '?p='+pageNum);
    }
}

// 排查提示1
function showErrorResultForphpfileName(phpfileName){
    $('#app .result').html('<div class="error">服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+phpfileName+'的返回信息进行排查！<a href="../../../../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a></div>');
    $('#app .result .error').css('display','block');
    setTimeout('hideResult()', 3000);
}

// 暂无数据
function noData(text){
    
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html(
    '<img src="../../../../static/img/noData.png" class="noData" /><br/>' +
    '<p class="noDataText">'+text+'</p>'
    );
    $("#right .data-card .loading").css('display','block');
}

// 初始化（getJwList获取中间页列表）
function initialize_getJwList(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 初始化（获取域名列表）
function initialize_getDomainNameList(module){
    
    if(module == 'create'){
        
        // 创建时的表单初始化
        $('#createJwModal input[name="page_title"]').val('');
        $('#createJwModal input[name="page_banner"]').val('');
        $('#createJwModal input[name="customer_avatar"]').val('');
        $('#createJwModal input[name="my_avatar"]').val('');
        $('#createJwModal textarea[name="welcome_msg"]').val('');
        $('#createJwModal textarea[name="end_msg"]').val('');
        $('#createJwModal textarea[name="reject_msg"]').val('');
        $('#createJwModal input[name="jump_btn_text"]').val('');
        $('#createJwModal input[name="jump_btn_link"]').val('');
        
        // 域名初始化
        $('#createJwModal select[name="page_dlym"]').empty();
        $('#createJwModal select[name="page_rkym"]').empty();
        $('#createJwModal select[name="page_ldym"]').empty();
        
        // 新增的
        $('#createJwModal input[name="jw_qywx"]').val('');
        $('#createJwModal input[name="jw_h5page"]').val('');
        $('#createJwModal input[name="jw_qqgroup"]').val('');
        $('#createJwModal input[name="jw_txym"]').val('');
        
        // 隐藏提示
        hideResult();

    }else if(module == 'edit'){
        
        // 域名初始化
        $('#editJwModal select[name="page_dlym"]').empty();
        $('#editJwModal select[name="page_rkym"]').empty();
        $('#editJwModal select[name="page_ldym"]').empty();
        hideResult();
    }
    
    // 创建客服落地页
    $('#createKflandpageModal input').val('');
    $('#createKflandpageModal select[name="kf_ldym"]').empty();
    $('#createKflandpageModal .up-span-text').text('+ 上传图片');
}

// 隐藏Modal（传入节点id决定隐藏哪个Modal）
function hideModal(modal_Id){
    $('#'+modal_Id+'').modal('hide');
}

// 显示Modal（传入节点id决定隐藏哪个Modal）
function showModal(modal_Id){
    $('#'+modal_Id+'').modal('show');
}

// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
}

// 打开操作反馈（操作成功）
function showSuccessResult(content){
    $('#app .result').html('<div class="success">'+content+'</div>');
    $('#app .result .success').css('display','block');
    setTimeout('hideResult()', 2500); // 2.5秒后自动关闭
}

// 打开操作反馈（操作失败）
function showErrorResult(content){
    $('#app .result').html('<div class="error">'+content+'</div>');
    $('#app .result .error').css('display','block');
    setTimeout('hideResult()', 2500); // 2.5秒后自动关闭
}

// 关闭操作反馈
function hideResult(){
    $("#app .result .success").css("display","none");
    $("#app .result .error").css("display","none");
    $("#app .result .success").text('');
    $("#app .result .error").text('');
}

// 获取URL参数
function queryURLParams(url) {
    var pattern = /(\w+)=(\w+)/ig;
    var parames = {};
    url.replace(pattern, ($, $1, $2) => {
        parames[$1] = $2;
    });
    return parames;
}