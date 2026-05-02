// 进入就加载
window.onload = function (){
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    
    // 检查是否有页码
    if(typeof pageNum !== "undefined" && !isNaN(pageNum)) {
        
        // 获取当前页码的列表
        getTasks(pageNum);
    } else {
        
        // 获取首页
        getTasks();
    }
}

// 加载任务列表
function getTasks(pageNum) {
    
    // 请求地址
    if(!pageNum){
        
        reqUrl = "getTasks.php";
    }else{
        
        // 加载当前页码并设置URL路由
        reqUrl = "getTasks.php?p=" + pageNum
        setRouter(pageNum);
    }
    
    $.ajax({
        type: 'POST',
        url: 'server/' + reqUrl,
        success: function(res){
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>任务ID</th>' +
                '   <th>检测对象</th>' +
                '   <th>微信检测结果</th>' +
                '   <th>检测次数</th>' +
                '   <th>时间</th>' +
                '   <th>任务状态</th>' +
                '   <th>任务链接</th>' +
                '   <th>操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 初始化
            initialize_getTasks();

            if(res.code == 200){
                
                for (var i=0; i<res.tasksList.length; i++) {
                    
                    // 状态处理
                    if(res.tasksList[i].check_status == 1){
                        
                        // 正常
                        var check_status = 
                        '<span class="switch-on" onclick="changeTaskStatus('+res.tasksList[i].task_id+');">' +
                        '   <span class="press"></span>' +
                        '</span>';
                    }else{
                        
                        // 关闭
                        var check_status = 
                        '<span class="switch-off" onclick="changeTaskStatus('+res.tasksList[i].task_id+');">' +
                        '   <span class="press"></span>' +
                        '</span>';
                    }
                    
                    // 微信检测结果
                    if(res.tasksList[i].wx_check_result == '0') {
                        
                        // 未检测
                        var wx_check_result = '<span class="span_tag green_tag">✅正常</span> <i class="bi bi-arrow-repeat" onclick="testTask('+res.tasksList[i].task_id+')" id="task_'+res.tasksList[i].task_id+'" title="测试"></i>';
                    }else if(res.tasksList[i].wx_check_result == '-2') {
                        
                        // 正常
                        var wx_check_result = '<span class="span_tag orange_tag">拦截（红拦截或白拦截）</span> <i class="bi bi-arrow-repeat" onclick="testTask('+res.tasksList[i].task_id+')" id="task_'+res.tasksList[i].task_id+'" title="测试"></i>';
                    }else if(res.tasksList[i].wx_check_result == '-3') {
                        
                        // 封禁
                        var wx_check_result = '<span class="span_tag orange_tag">拦截（中风险）</span> <i class="bi bi-arrow-repeat" onclick="testTask('+res.tasksList[i].task_id+')" id="task_'+res.tasksList[i].task_id+'" title="测试"></i>';
                    }else if(res.tasksList[i].wx_check_result == '-4') {
                        
                        // 封禁
                        var wx_check_result = '<span class="span_tag red_tag">拦截（高风险）</span> <i class="bi bi-arrow-repeat" onclick="testTask('+res.tasksList[i].task_id+')" id="task_'+res.tasksList[i].task_id+'" title="测试"></i>';
                    }else if(res.tasksList[i].wx_check_result == '-5') {
                        
                        // 封禁
                        var wx_check_result = '<span class="span_tag red_tag">拦截（中高风险）</span> <i class="bi bi-arrow-repeat" onclick="testTask('+res.tasksList[i].task_id+')" id="task_'+res.tasksList[i].task_id+'" title="测试"></i>';
                    }else if(res.tasksList[i].wx_check_result == '-1') {
                        
                        // 未检测
                        var wx_check_result = '<span class="span_tag">未检测</span> <i class="bi bi-arrow-repeat" onclick="testTask('+res.tasksList[i].task_id+')" id="task_'+res.tasksList[i].task_id+'" title="测试"></i>';
                    }else {
                        
                        // 失败
                        var wx_check_result = '<span class="span_tag">检测失败</span> <i class="bi bi-arrow-repeat" onclick="testTask('+res.tasksList[i].task_id+')" id="task_'+res.tasksList[i].task_id+'" title="测试"></i>';
                    }
                    
                    // 检测时间
                    if(!res.tasksList[i].check_time || res.tasksList[i].check_time == '' || res.tasksList[i].check_time == 0) {
                        
                        // 没有时间
                        var check_time = ' - ';
                    }else {
                        
                        // 有时间
                        var check_time = res.tasksList[i].check_time;
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+res.tasksList[i].task_id+'</td>' +
                        '   <td class="wrap-row">'+res.tasksList[i].task_object+'</td>' +
                        '   <td>'+wx_check_result+'</td>' +
                        '   <td>'+res.tasksList[i].wx_check_num+'</td>' +
                        '   <td>' +
                        '       <span class="time-span">创建时间 '+res.tasksList[i].create_time+'</span>' +
                        '       <span class="time-span">检测时间 '+check_time+'</span>' +
                        '   </td>' +
                        '   <td>'+check_status+'</td>' +
                        '   <td><span class="span_tag" data-taskid="'+res.tasksList[i].task_id+'" id="copy_tag_'+res.tasksList[i].task_id+'" style="cursor:pointer;" data-copy="'+res.tasksList[i].check_link+'">复制</span></td>' +
                        '   <td>' +
                        '       <span class="light-tag" data-toggle="modal" data-target="#delTaskModal" onclick="delTaskConfirm('+res.tasksList[i].task_id+')">删除</span>' +
                        '   </td>' +
                        '</tr>'
                    );
                    $("#right .data-list tbody").append($tbody_HTML);
                }
                
                // 分页组件
                fenyeComponent(res.page,res.allpage,res.nextpage,res.prepage);
                
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
        
        // 服务器发生错误
        $('#right .data-card').html(
            '<p class="error_text">'+reqUrl+'发生错误！请阅读使用文档通过F12调试排查问题！</p>'
        );
      },
    });
}

// 分页组件
function fenyeComponent(thisPage,allPage,nextPage,prePage){
    
    if(thisPage == 1 && allPage == 1){

        $("#right .data-card .fenye").css("display","none");
    }else if(thisPage == 1 && allPage > 1){

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
        $("#right .data-card .fenye").css("width","80px");
    }else if(thisPage == allPage){

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
        $("#right .data-card .fenye").css("width","80px");
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
        $("#right .data-card .fenye").css("width","150px");
    }
    $("#right .data-card .fenye").html($fenyeComponent_HTML);
}

// 获取分页数据
function getFenye(e){
    
    // 获取该页列表
    getTasks(e.id);
}

// 测试
function testTask(task_id) {
    
    if(task_id) {
        $.ajax({
            type: "POST",
            url: 'check/?id='+task_id,
            success: function(res){
                getTasks();
            },
            error: function() {
                alert('服务器发生错误')
            },
            beforeSend: function() {
                $('#task_'+task_id).text('检测中...');
            }
        });
    }
}

// 创建任务
function createTask(){
    
    // 请求地址
    const reqUrl = 'createTask.php';
    
    $.ajax({
        type: "POST",
        url: 'server/' + reqUrl,
        data: $('#createTask').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 成功
                showSuccessResult(res.msg)
                
                // 隐藏Modal
                setTimeout('hideModal("createTaskModal")', 500);
                
                // 隐藏Result
                setTimeout('hideResult()', 700);
                
                // 重新加载列表
                setTimeout('getTasks();', 800);
            }else{
                
                // 失败
                showErrorResult(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName(reqUrl);
        }
    });
}

// 删除页面确认
function delTaskConfirm(task_id){
    $('#delTaskModal .modal-footer').html(
        '<button type="button" class="default-btn center-btn" onclick="delTask('+task_id+');">确定删除</button>'
    )
}

// 确认删除
function delTask(task_id){
    
    // 请求地址
    const reqUrl = 'delTask.php';
    
    $.ajax({
        type: "POST",
        url: 'server/' + reqUrl + '?task_id='+task_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                // 隐藏Modal
                setTimeout('hideModal("delTaskModal")', 300);
                
                // 重新加载列表
                setTimeout('getTasks()', 500);
                
                // 显示删除结果
                setTimeout('showNotification("'+res.msg+'")', 600);
            }else{
                
                // 失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName(reqUrl);
        }
    });
}

// 获取域名或链接列表
function getdomainOrLinks() {
    
    var selectElement = $('#createTaskModal select[name="domainOrLinks"]'); // 获取节点
    selectElement.empty(); // 清空之前的选项
    
    // 重置创建Modal里面的表单
    $('.otherdomainOrLinks').css('display','none');
    $('input[name="otherdomainOrLinks"]').val('');
    
    $.ajax({
        type: "GET",
        url: "server/getdomainOrLinks.php",
        success: function(res) {
            
            // 检查返回的域名或链接数组是否为空
            if (res.domainOrLinks.length === 0) {
                
                // 没有域名或链接
                var customOption = $('<option></option>').val('').text('域名库暂无域名或链接');
                selectElement.append(customOption);
            } else {
                
                // 有数据，渲染至 select
                // 遍历返回的数组并添加选项
                res.domainOrLinks.forEach(function(item) {
                    var option = $('<option></option>').val(item.domain).text(item.domain);
                    selectElement.append(option);
                });
                
                // 添加 "自定义域名或链接" 选项
                var customOption = $('<option></option>').val('customize').text('自定义域名或链接');
                selectElement.append(customOption);
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('getdomainOrLinks.php');
        }
    });
}

// 切换状态
function changeTaskStatus(task_id) {
    
    $.ajax({
        type: "POST",
        url: "server/changeTaskStatus.php?task_id="+task_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                showNotification(res.msg);
                getTasks();
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

// 获取通知渠道
function getNotify() {
    
    showModal('notifyModal');
    
    $.ajax({
        type: "POST",
        url: "server/getNotify.php",
        success: function(res){

            // 成功
            if(res.code == 200){
                
                $('#notifyModal input[name="Appid"]').val(res.data.Appid);
                $('#notifyModal input[name="AppSecret"]').val(res.data.AppSecret);
                $('#notifyModal input[name="AgentId"]').val(res.data.AgentId);
                $('#notifyModal input[name="toUser"]').val(res.data.toUser);
                $('#notifyModal input[name="BarkUrl"]').val(res.data.BarkUrl);
            }else{
                
                // 操作失败
                $('#notifyModal .modal-body').text(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            alert('getNotify.php服务器发生错误');
        }
    });
}

// 保存通知配置
function saveNotify(){
    
    $.ajax({
        type: "POST",
        url: 'server/saveNotify.php',
        data: $('#saveNotify').serialize(),
        success: function(res){
            
            // 成功
            if(res.code == 200){
                
                alert(res.msg);
                
                setTimeout(function() {
                    hideModal('notifyModal')
                }, 800);
            }else{
                
                // 失败
                alert(res.msg);
            }
        },
        error: function() {
            
            // 服务器发生错误
            alert('saveNotify.php服务器发生错误');
        }
    });
}


// 监听
$(document).ready(function() {
    
    // 监听【检测的域名或链接】的切换
    $('#createTaskModal select[name="domainOrLinks"]').on('change', function() {
        
        // 获取选中的值
        var selectedValue = $(this).val();
    
        // 判断选中的值是否为 "customize"
        if (selectedValue === 'customize') {
            
            // 显示自定义填写表单
            $('.otherdomainOrLinks').show();
        } else {
            
            // 隐藏
            $('.otherdomainOrLinks').hide();
        }
    });
    
    // 通知方式切换，默认选中第一项
    $(".type_btn").first().addClass("type_btn_selected");
    let firstPlaceholder = $(".type_btn").first().data("placeholder");
    $("input[name='notiType']").val($(".type_btn").first().data("type"));
    
    // 点击事件
    $(".type_btn").click(function() {
        
        // 清除之前的选中样式
        $(".type_btn").removeClass("type_btn_selected");
        
        // 添加选中样式
        $(this).addClass("type_btn_selected");
        
        // 更新输入框内容
        $("input[name='notiType']").val($(this).data("type"));
    });
});

// 随机生成安全Token
function createToken(length) {
    var str = '0123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ';
    var result = '';
    for (var i = length; i > 0; --i) {
        result += str[Math.floor(Math.random() * str.length)];
    }
    
    $('#wxpayZsmConfigModal input[name="wxpayZsm_token"]').val(result);
    return result;
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
                location.reload();
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('exitLogin.php');
        }
    });
}

// 初始化（加载支付页面列表）
function initialize_getTasks(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
}

// 生成随机token
function creatPageToken(length) {
    var str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var result = '';
    for (var i = length; i > 0; --i) 
        result += str[Math.floor(Math.random() * str.length)];
    return result;
}

// 隐藏Modal（传入节点id决定隐藏哪个Modal）
function hideModal(modal_Id){
    $('#'+modal_Id+'').modal('hide');
}

// 显示Modal（传入节点id决定隐藏哪个Modal）
function showModal(modal_Id){
    $('#'+modal_Id+'').modal('show');
}

// 提醒页面
function warningPage(text){
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html('<img src="../../static/img/warningIcon.png"/><br/><p>'+text+'</p>');
    $("#right .data-card .loading").css('display','block');
}

// 设置剪贴板
$(document).on('click', '[data-copy]', function() {
    var content = $(this).attr('data-copy');
    var tempInput = $('<input>');
    $('body').append(tempInput);
    tempInput.val(content).select();
    document.execCommand('copy');
    tempInput.remove();
    
    // 按钮显示已复制
    $('#copy_tag_'+$(this).attr('data-taskid')).text('已复制');
    
    // 恢复按钮文字
    var _this = $(this);
    setTimeout(function() {
        $('#copy_tag_' + _this.attr('data-taskid')).text('复制');
    }, 2500);
});

function noLimit(text){
    
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html(
    '<img src="../../static/img/noLimit.png" class="noData" /><br/>' +
    '<p class="noDataText">'+text+'</p>'
    );
    $("#right .data-card .loading").css('display','block');
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

// 打开操作反馈（操作成功）
function showSuccessResult(content){
    $('#app .result').html('<div class="success">'+content+'</div>');
    $('#app .result .success').css('display','block');
    setTimeout('hideResult()', 2500); // 2.5秒后自动关闭
}

// 打开操作反馈（操作成功）
function showSuccessResultTimes(content,times){
    $('#app .result').html('<div class="success">'+content+'</div>');
    $('#app .result .success').css('display','block');
    setTimeout('hideResult()', times);
}

// 打开操作反馈（操作失败）
function showErrorResult(content){
    $('#app .result').html('<div class="error">'+content+'</div>');
    $('#app .result .error').css('display','block');
    setTimeout('hideResult()', 2500);
}

// 排查提示1
function showErrorResultForphpfileName(phpfileName){
    $('#app .result').html('<div class="error">服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+phpfileName+'的返回信息进行排查！<a href="../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a></div>');
    $('#app .result .error').css('display','block');
    setTimeout('hideResult()', 3000);
}

// 排查提示2
function errorPage(from,text){
    
    if(from == 'data-list'){
        
        $("#right .data-list").css('display','none');
        $("#right .data-card .loading").html(
            '<img src="../../static/img/errorIcon.png"/><br/>' +
            '<p>服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+text+'的返回信息进行排查！</p>' +
            '<a href="../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a>'
        );
        $("#right .data-card .loading").css('display','block');
    }else if(from == 'data-card'){
        
        $("#right .data-card").html(
            '<img src="../../static/img/errorIcon.png"/><br/>' +
            '<p>服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+text+'的返回信息进行排查！</p>' +
            '<a href="../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a>'
        );
    }else if(from == 'qrcode-list'){

        $("#qunQrcodeListModal table").html(
            '<img src="../../static/img/errorIcon.png"/><br/>' +
            '<p>服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+text+'的返回信息进行排查！</p>' +
            '<a href="../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a>'
        );
    }else if(from == 'bingliuList'){

        $("#bingliuModal .bingliuList").html(
            '<img src="../../static/img/errorIcon.png" class="errorIMG" /><br/>' +
            '<p class="errorTEXT">服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+text+'的返回信息进行排查！</p>' +
            '<a href="../../static/img/tiaoshi.jpg" target="blank" class="errorA">点击查看排查方法</a>'
        );
    }
    
}

// 关闭操作反馈
function hideResult(){
    $("#app .result .success").css("display","none");
    $("#app .result .error").css("display","none");
    $("#app .result .success").text('');
    $("#app .result .error").text('');
}

// 显示指定元素
function showElementBy(csspath){
    
    // 传入CSS路径
    $(csspath).css('display','block');
}

// 隐藏指定元素
function hideElementBy(csspath){
    
    // 传入CSS路径
    $(csspath).css('display','none');
}

// 显示全局信息提示弹出提示
function showNotification(message) {
    
    // 获取文案
	$('#notification-text').text(message);
	
    // 计算文案长度并设置宽度
	var textLength = message.length * 25;
	$('#notification-text').css('width',textLength+'px');
	
    // 距离顶部的高度
	$('#notification').css('top', '25px');
	
    // 延迟隐藏
	setTimeout(function() {
		hideNotification();
	}, 3000);
}

// 隐藏全局信息提示弹出提示
function hideNotification() {
	var $notificationContainer = $('#notification');
	$notificationContainer.css('top', '-100px');
}

// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
}

// 设置URL路由
function setRouter(pageNum){
    
    // 根据页码+token设置路由
    window.history.pushState('', '', '?p='+pageNum+'&token='+creatPageToken(32));
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

console.log('%c欢迎使用私域引流宝！','color:#3B5EE1;font-size:16px;font-family:"微软雅黑"');