window.onload = function (){
    
    // 获取登录状态
    getLoginStatus();
    
    // 获取页码
    var pageNum = queryURLParams(window.location.href).p;
    var kf_id = queryURLParams(window.location.href).kf_id;
    
    if(pageNum !== 'undefined'){
        
        // 获取当前页码的客服码列表
        iplist(pageNum,kf_id);
    }else{
        
        // 获取不到页码就获取首页
        iplist(1,kf_id);
    }
}

// 获取登录状态
function getLoginStatus(){
    
    // 获取
    $.ajax({
        type: "POST",
        url: "../login/getLoginStatus.php",
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
                initialize_Login('login');
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
            showNotification('getLoginStatus.php发生错误');
        }
    });
}

// 登录后的一些初始化
function initialize_Login(loginStatus){
    
    if(loginStatus == 'login'){
        
        // 显示
        $('#button-view').css('display','block');
    }else{
        
        // 隐藏
        $('#button-view').css('display','none');
    }
}

// 获取列表
function iplist(pageNum,kf_id) {
    
    // 判断是否有pageNum参数传过来
    if(!pageNum){
        
        // 如果没有就默认请求第1页
        reqUrl = "./iplist.php?kf_id="+kf_id;
    }else{
        
        // 如果有就请求pageNum的那一页
        reqUrl = "./iplist.php?kf_id="+kf_id+"&p="+pageNum
        
        // 设置路由
        setRouter(pageNum,kf_id);
    }
    
    // AJAX获取
    $.ajax({
        type: "POST",
        url: reqUrl,
        success: function(res){
            
            // 初始化
            initialize_iplist();
            
            // 表头
            var $thead_HTML = $(
                '<tr>' +
                '   <th>IP地址</th>' +
                '   <th>访问设备</th>' +
                '   <th>访问平台</th>' +
                '   <th>地理位置</th>' +
                '   <th>访问次数</th>' +
                '   <th>访问时间</th>' +
                '   <th>操作</th>' +
                '</tr>'
            );
            $("#right .data-list thead").html($thead_HTML);
            
            // 状态码为200代表有数据
            if(res.code == 200){
                
                // 如果有数据
                // 遍历数据
                for (var i=0; i<res.ipList.length; i++) {
                    
                    if(res.ipList[i].ip_ban == 1) {
                        
                        // 解封
                        var ban = '<span class="ipcz-tag" id="'+res.ipList[i].ip_address+'" onclick="banip(this,'+kf_id+','+pageNum+')" style="cursor:pointer;">解封</span>';
                    }else {
                        
                        // 封禁
                        var ban = '<span class="ipcz-tag" id="'+res.ipList[i].ip_address+'" onclick="banip(this,'+kf_id+','+pageNum+')" style="cursor:pointer;">封禁IP</span>';
                    }
                    
                    // 列表
                    var $tbody_HTML = $(
                        '<tr>' +
                        '   <td>'+res.ipList[i].ip_address+'</td>' +
                        '   <td>'+res.ipList[i].ip_device+'</td>' +
                        '   <td>'+res.ipList[i].ip_platform+'</td>' +
                        '   <td>'+res.ipList[i].ip_location+'</td>' +
                        '   <td>'+res.ipList[i].ip_pv+'</td>' +
                        '   <td>'+res.ipList[i].ip_addtime+'</td>' +
                        '   <td>'+ban+'</td>' +
                        '</tr>'
                    );
                    $("#right .data-list tbody").append($tbody_HTML);
                }
                
                // 分页组件
                getFenyeComponent(res.page,res.nextpage,res.prepage,res.allpage,kf_id);
                
                // 设置路由
                setRouter(res.page,kf_id);
                
                // 显示清空按钮
                $('.button-gongneng').html('<button class="tint-btn" data-toggle="modal" data-target="#cleanIPAddressModal" onclick="cleanIPAddressModal('+kf_id+')">清空IP记录</button>');
                
                // 显示标题
                $('.from_kf_title').html('（'+res.kf_title+'）');
            }else{
                
                // 未登录
                if(res.code == 201){
                    
                    // 跳转到登录页面
                    jumpUrl('../login/');
                }
                
                // 非200状态码
                noData(res.msg);
            }
            
      },
      error: function(){
        
        // 发生错误
        errorPage('data-list','iplist.php');
      },
    });
}

// 分页组件
function getFenyeComponent(thisPage,nextPage,prePage,allPage,kf_id){
    
    // 分页
    if(thisPage == 1 && allPage == 1){
        
        // 当前页码=1且总页码=1
        // 无需显示分页控件
        $("#right .data-card .fenye").css("display","none");
        
    }else if(thisPage == 1 && allPage > 1){
        
        // 当前页码=1且总页码>1
        // 代表还有下一页
        var $getFenyeComponent_HTML = $(
        '<ul>' +
        '   <li>' +
        '       <button id="'+nextPage+'" onclick="getFenye(this,'+kf_id+');" title="下一页">' +
        '       <img src="../../static/img/nextPage.png" /></button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'" onclick="getFenye(this,'+kf_id+');" title="最后一页">' +
        '       <img src="../../static/img/lastPage.png" /></button>' +
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        $("#right .data-card .fenye").width("80px");
    }else if(thisPage == allPage){
        
        // 当前页码=总页码
        // 代表这是最后一页
        var $getFenyeComponent_HTML = $(
        '<ul>' +
        '   <li>' +
        '       <button id="1" onclick="getFenye(this,'+kf_id+');" title="第一页">' +
        '       <img src="../../static/img/firstPage.png" /></button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'" onclick="getFenye(this,'+kf_id+');" title="上一页">' +
        '       <img src="../../static/img/prevPage.png" /></button>' +
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        $("#right .data-card .fenye").width("80px");
    }else{
        
        // 显示所有组件
        var $getFenyeComponent_HTML = $(
        '<ul>' +
        '   <li>' +
        '       <button id="1" onclick="getFenye(this,'+kf_id+');" title="第一页">' +
        '           <img src="../../static/img/firstPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+prePage+'" onclick="getFenye(this,'+kf_id+');" title="上一页">' +
        '           <img src="../../static/img/prevPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+nextPage+'" onclick="getFenye(this,'+kf_id+');" title="下一页">' +
        '           <img src="../../static/img/nextPage.png" />' +
        '       </button>' +
        '   </li>' +
        '   <li>' +
        '       <button id="'+allPage+'" onclick="getFenye(this,'+kf_id+');" title="最后一页">' +
        '           <img src="../../static/img/lastPage.png" />' +
        '       </button>' +
        '   </li>' +
        '</ul>'
        );
        $("#right .data-card .fenye").css("display","block");
        $("#right .data-card .fenye").width("150px");
    }
    
    // 渲染分页组件
    $("#right .data-card .fenye").html($getFenyeComponent_HTML);
}

// 分页
function getFenye(e,kf_id){
    
    // 页码
    var pageNum = e.id;
    
    // 获取该页列表
    iplist(pageNum,kf_id);
}

// 封禁IP
function banip(e,kf_id,pageNum) {
    
    $.ajax({
        type: "GET",
        url: "./banip.php?kf_id="+kf_id+"&ip_address="+e.id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
  
                iplist(pageNum,kf_id);
                showNotification(res.msg);
            }else{
                
                // 操作失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('cleanIPAddress.php');
        }
    });
}

// 询问是否要清空所有IP记录
function cleanIPAddressModal(kf_id){
    
    // 传递id
    $('#cleanIPAddressModal .modal-footer').html(
        '<button type="button" class="default-btn" onclick="cleanIPAddress('+kf_id+');">确定清空</button>'
    )
}

// 确认清空
function cleanIPAddress(kf_id){
    
    $.ajax({
        type: "GET",
        url: "./cleanIPAddress.php?kf_id="+kf_id,
        success: function(res){
            
            // 成功
            if(res.code == 200){
  
                // 隐藏Modal
                setTimeout(function(){
                    hideModal("cleanIPAddressModal")
                    iplist(1,kf_id);
                    showNotification(res.msg);
                },500)
            }else{
                
                // 操作失败
                showErrorResult(res.msg)
            }
        },
        error: function() {
            
            // 服务器发生错误
            showErrorResultForphpfileName('cleanIPAddress.php');
        }
    });
}

// 注销登录
function exitLogin(){
    
    $.ajax({
        type: "POST",
        url: "../login/exitLogin.php",
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
        
    }else if(from == 'qrcode-list'){

        $("#kfQrcodeListModal table").html(
            '<img src="../../static/img/errorIcon.png"/><br/>' +
            '<p>服务器发生错误！可按F12打开开发者工具点击Network或网络查看'+text+'的返回信息进行排查！</p>' +
            '<a href="../../static/img/tiaoshi.jpg" target="blank">点击查看排查方法</a>'
        );
    }
    
}

// 暂无数据
function noData(text){
    
    $("#right .data-list").css('display','none');
    $("#right .data-card .loading").html(
    '<img src="../../static/img/noData.png" class="noData" /><br/>' +
    '<p class="noDataText">'+text+'</p>'
    );
    $("#right .data-card .loading").css('display','block');
}

// 初始化
function initialize_iplist(){
    $("#right .data-list").css('display','block');
    $("#right .data-card .loading").css('display','none');
    $("#right .data-list tbody").empty('');
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

// 打开操作反馈（操作成功）
function showSuccessResultTimes(content,times){
    $('#app .result').html('<div class="success">'+content+'</div>');
    $('#app .result .success').css('display','block');
    setTimeout('hideResult()', times); // times秒后自动关闭
}

// 设置路由
function setRouter(pageNum,kf_id){
    
    // 当前页码不等于1的时候
    if(pageNum !== 1){
        window.history.pushState('', '', '?p='+pageNum+'&kf_id='+kf_id+'&token='+creatPageToken(32));
    }
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

// 跳转到指定路径
function jumpUrl(jumpUrl){
    
    // 1秒后跳转至jumpUrl
    setTimeout('location.href="'+jumpUrl+'"',1000);
}

console.log('%c 欢迎使用引流宝','color:#3B5EE1;font-size:30px;font-family:"微软雅黑"');
console.log('%c 作者：TANKING','color:#3B5EE1;font-size:30px;font-family:"微软雅黑"');
console.log('%c 作者博客：https://segmentfault.com/u/tanking','color:#3B5EE1;font-size:30px;font-family:"微软雅黑"');
console.log('%c 开源地址：https://github.com/likeyun/liKeYun_Ylb','color:#3B5EE1;font-size:30px;font-family:"微软雅黑"');