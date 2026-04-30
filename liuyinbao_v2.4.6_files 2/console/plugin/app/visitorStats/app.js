// 访客画像插件 JavaScript
let page = 1;
let loading = false;
let hasMore = true;

// 获取设备图标
function getDeviceIcon(device) {
    if (device === 'PC') return '💻';
    if (device === 'Mobile') return '📱';
    if (device === 'Tablet') return '📱';
    return '❓';
}

// 获取浏览器图标
function getBrowserIcon(browser) {
    const icons = {
        'Chrome': '🔵',
        'Firefox': '🦊',
        'Safari': '🧭',
        'Edge': '🌐',
        'WeChat': '💬',
        'MicroMessenger': '💬'
    };
    return icons[browser] || '🌐';
}

// 格式化时间
function formatTime(timestamp) {
    const date = new Date(timestamp * 1000);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return '刚刚';
    if (diff < 3600000) return Math.floor(diff / 60000) + '分钟前';
    if (diff < 86400000) return Math.floor(diff / 3600000) + '小时前';
    if (diff < 604800000) return Math.floor(diff / 86400000) + '天前';
    return date.toLocaleDateString();
}

// 加载统计概览
function loadStats() {
    $.get('server/getStats.php', function(res) {
        if (res.code === 200) {
            $('#totalCount').text(res.data.total);
            $('#todayCount').text(res.data.today);
            $('#pcCount').text(res.data.pc);
            $('#mobileCount').text(res.data.mobile);
        }
    });
}

// 加载访客列表
function loadVisitors(reset = false) {
    if (loading) return;
    loading = true;
    
    if (reset) {
        page = 1;
        hasMore = true;
    }
    
    const type = $('#filterType').val();
    const date = $('#filterDate').val();
    const ip = $('#searchIp').val();
    
    $.get('server/getVisitors.php', {
        page: page,
        type: type,
        date: date,
        ip: ip
    }, function(res) {
        loading = false;
        if (res.code === 200) {
            const html = res.data.list.map(function(v) {
                return `
                <div class="visitor-item">
                    <div class="visitor-icon">${getDeviceIcon(v.device)}</div>
                    <div class="visitor-info">
                        <div class="visitor-ip">${v.ip}</div>
                        <div class="visitor-meta">
                            <span class="region-badge">${v.region || '未知地区'}</span>
                            <span>${getBrowserIcon(v.browser)} ${v.browser || '未知浏览器'}</span>
                            <span>${v.os || '未知系统'}</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:12px;">${v.target_type}</div>
                        <div class="time-badge">${formatTime(v.create_time)}</div>
                    </div>
                </div>`;
            }).join('');
            
            if (reset) {
                $('#visitorList').html(html || '<div class="text-center text-muted py-5">暂无数据</div>');
            } else {
                $('#visitorList').append(html);
            }
            
            hasMore = res.data.hasMore;
            $('#loadMoreBtn').prop('disabled', !hasMore);
            
            if (res.data.hasMore) {
                page++;
            }
        }
    });
}

// 加载更多
function loadMore() {
    loadVisitors(false);
}

// 导出数据
function exportData() {
    const type = $('#filterType').val();
    const date = $('#filterDate').val();
    window.location.href = 'server/exportVisitors.php?type=' + type + '&date=' + date;
}

// 初始化
$(function() {
    loadStats();
    loadVisitors(true);
    
    // 默认显示今天
    $('#filterDate').val(new Date().toISOString().split('T')[0]);
});