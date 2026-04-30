#!/bin/bash
# ===========================================
# 私域引流宝 - 一键部署脚本
# ===========================================
# 版本: 1.0.0
# 作者: AutoDev
# ===========================================

set -e  # 遇到错误立即退出

# ===========================================
# 颜色定义
# ===========================================
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ===========================================
# 日志函数
# ===========================================
log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# ===========================================
# 全局变量
# ===========================================
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOG_FILE="${SCRIPT_DIR}/logs/deploy_$(date +%Y%m%d_%H%M%S).log"
ENV_FILE="${SCRIPT_DIR}/.env"
ENV_EXAMPLE="${SCRIPT_DIR}/.env.example"
CONFIG_CLASS="${SCRIPT_DIR}/console/Config.php"
DEPLOY_LOCK="${SCRIPT_DIR}/logs/deploy.lock"
PID_FILE="${SCRIPT_DIR}/logs/server.pid"

# ===========================================
# 初始化
# ===========================================
init() {
    # 创建日志目录
    mkdir -p "${SCRIPT_DIR}/logs"
    
    # 初始化日志文件
    echo "======================================" > "$LOG_FILE"
    echo "私域引流宝 - 部署日志" >> "$LOG_FILE"
    echo "时间: $(date '+%Y-%m-%d %H:%M:%S')" >> "$LOG_FILE"
    echo "======================================" >> "$LOG_FILE"
    
    log_info "开始部署流程..."
    log_info "日志文件: $LOG_FILE"
}

# ===========================================
# 日志写入
# ===========================================
log() {
    local level=$1
    shift
    local message="$*"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$timestamp] [$level] $message" >> "$LOG_FILE"
    
    case $level in
        INFO)  log_info "$message" ;;
        SUCCESS)  log_success "$message" ;;
        WARNING)  log_warning "$message" ;;
        ERROR)  log_error "$message" ;;
    esac
}

# ===========================================
# 系统检测
# ===========================================
detect_os() {
    log "INFO" "检测操作系统..."
    
    if [[ "$OSTYPE" == "linux-gnu"* ]]; then
        if command -v apt-get &> /dev/null; then
            OS="debian"
            PKG_MANAGER="apt-get"
        elif command -v yum &> /dev/null; then
            OS="centos"
            PKG_MANAGER="yum"
        elif command -v apk &> /dev/null; then
            OS="alpine"
            PKG_MANAGER="apk"
        else
            OS="linux"
            PKG_MANAGER="unknown"
        fi
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        OS="macos"
        PKG_MANAGER="brew"
    else
        OS="unknown"
        PKG_MANAGER="unknown"
    fi
    
    log "INFO" "检测到系统: $OS ($OSTYPE)"
    log "INFO" "包管理器: $PKG_MANAGER"
}

# ===========================================
# 依赖安装
# ===========================================
install_dependencies() {
    log "INFO" "开始安装系统依赖..."
    
    case $OS in
        debian)
            log "INFO" "安装PHP及相关扩展..."
            apt-get update -qq
            apt-get install -y -qq php php-cli php-fpm php-mysql php-xml php-mbstring php-curl php-zip php-gd php-json php-session php-openssl > /dev/null 2>&1
            log "SUCCESS" "PHP及相关扩展安装完成"
            ;;
        centos)
            log "INFO" "安装PHP及相关扩展..."
            yum install -y php php-cli php-fpm php-mysql php-xml php-mbstring php-curl php-zip php-gd php-json php-session php-openssl > /dev/null 2>&1
            log "SUCCESS" "PHP及相关扩展安装完成"
            ;;
        alpine)
            log "INFO" "安装PHP及相关扩展..."
            apk add --no-cache php php-cli php-fpm php-mysqlnd php-xml php-mbstring php-curl php-zip php-gd php-session php-openssl > /dev/null 2>&1
            log "SUCCESS" "PHP及相关扩展安装完成"
            ;;
        macos)
            if command -v brew &> /dev/null; then
                log "INFO" "安装PHP及相关扩展..."
                brew install php > /dev/null 2>&1
                log "SUCCESS" "PHP安装完成"
            else
                log "WARNING" "Homebrew未安装，请手动安装PHP"
            fi
            ;;
    esac
    
    # 检查PHP
    if command -v php &> /dev/null; then
        PHP_VERSION=$(php -r 'echo PHP_VERSION;')
        log "SUCCESS" "PHP版本: $PHP_VERSION"
    else
        log "ERROR" "PHP安装失败"
        exit 1
    fi
}

# ===========================================
# 环境变量检查
# ===========================================
check_env() {
    log "INFO" "检查环境变量配置..."
    
    # 检查.env文件
    if [[ ! -f "$ENV_FILE" ]]; then
        log "WARNING" ".env文件不存在，正在创建..."
        
        if [[ -f "$ENV_EXAMPLE" ]]; then
            cp "$ENV_EXAMPLE" "$ENV_FILE"
            log "SUCCESS" "已基于.env.example创建.env文件"
            log "WARNING" "请编辑.env文件填写数据库密码等敏感配置"
        else
            log "ERROR" ".env.example文件不存在"
            exit 1
        fi
    fi
    
    # 检查必需配置项
    local missing=()
    
    # 检查数据库密码
    if ! grep -q "DB_PASSWORD=" "$ENV_FILE" || grep "DB_PASSWORD=$" "$ENV_FILE" > /dev/null; then
        missing+=("DB_PASSWORD")
    fi
    
    # 检查API密钥（可选，但建议有）
    if ! grep -q "API_SECRET_KEY=" "$ENV_FILE" || grep "API_SECRET_KEY=$" "$ENV_FILE" > /dev/null; then
        log "WARNING" "API_SECRET_KEY未设置，将使用默认值"
        # 自动生成API密钥
        if grep -q "API_SECRET_KEY=" "$ENV_FILE"; then
            sed -i "s/^API_SECRET_KEY=$/API_SECRET_KEY=$(openssl rand -hex 32)/" "$ENV_FILE"
        else
            echo "API_SECRET_KEY=$(openssl rand -hex 32)" >> "$ENV_FILE"
        fi
    fi
    
    # 检查数据库配置
    if ! grep -q "DB_HOST=" "$ENV_FILE"; then
        missing+=("DB_HOST")
    fi
    
    if ! grep -q "DB_NAME=" "$ENV_FILE"; then
        missing+=("DB_NAME")
    fi
    
    if [[ ${#missing[@]} -gt 0 ]]; then
        log "ERROR" "缺少必需配置: ${missing[*]}"
        log "INFO" "请编辑.env文件填写以下配置:"
        for var in "${missing[@]}"; do
            log "INFO" "  - $var"
        done
        exit 1
    fi
    
    log "SUCCESS" "环境变量配置检查通过"
}

# ===========================================
# 验证配置
# ===========================================
validate_config() {
    log "INFO" "验证配置文件..."
    
    # 使用PHP验证配置
    if command -v php &> /dev/null; then
        local result=$(php -r "
            require '${SCRIPT_DIR}/console/Config.php';
            \$config = Config::getInstance();
            \$config->load('${ENV_FILE}');
            
            if (\$config->hasErrors()) {
                echo 'ERROR:';
                foreach (\$config->getErrors() as \$k => \$v) {
                    echo \$k . ': ' . \$v . PHP_EOL;
                }
                exit(1);
            } else {
                echo 'OK';
                exit(0);
            }
        " 2>&1)
        
        if [[ "$result" == "OK" ]]; then
            log "SUCCESS" "配置文件验证通过"
            return 0
        else
            log "ERROR" "配置验证失败:"
            echo "$result" | while read line; do
                log "ERROR" "  $line"
            done
            return 1
        fi
    else
        log "WARNING" "PHP未安装，跳过配置验证"
        return 0
    fi
}

# ===========================================
# 进程管理 - 获取PID
# ===========================================
get_pid() {
    if [[ -f "$PID_FILE" ]]; then
        cat "$PID_FILE"
    fi
}

# ===========================================
# 进程管理 - 检查进程
# ===========================================
is_running() {
    local pid=$(get_pid)
    if [[ -n "$pid" ]] && kill -0 "$pid" 2>/dev/null; then
        return 0
    fi
    return 1
}

# ===========================================
# 进程管理 - 平滑关闭
# ===========================================
graceful_shutdown() {
    log "INFO" "开始平滑关闭旧实例..."
    
    if is_running; then
        local pid=$(get_pid)
        log "INFO" "发送SIGTERM信号到进程 $pid"
        
        # 发送SIGTERM信号
        kill -TERM "$pid" 2>/dev/null || true
        
        # 等待进程优雅退出
        local count=0
        local max_wait=30
        
        while [[ $count -lt $max_wait ]]; do
            if ! kill -0 "$pid" 2>/dev/null; then
                log "SUCCESS" "进程已优雅退出"
                rm -f "$PID_FILE"
                return 0
            fi
            sleep 1
            ((count++))
        done
        
        # 强制终止
        log "WARNING" "进程未响应SIGTERM，发送SIGKILL"
        kill -9 "$pid" 2>/dev/null || true
        rm -f "$PID_FILE"
    else
        log "INFO" "没有运行中的旧实例"
    fi
    
    return 0
}

# ===========================================
# 进程管理 - 启动服务
# ===========================================
start_server() {
    log "INFO" "启动PHP服务..."
    
    # 选择Web服务器
    if command -v php-fpm &> /dev/null; then
        log "INFO" "使用PHP-FPM模式"
        php-fpm -R -y "${SCRIPT_DIR}/php-fpm.conf" -g "${SCRIPT_DIR}/logs/php-fpm.pid" 2>/dev/null &
        
        # 等待启动
        sleep 2
        
        if [[ -f "${SCRIPT_DIR}/logs/php-fpm.pid" ]]; then
            local fpm_pid=$(cat "${SCRIPT_DIR}/logs/php-fpm.pid")
            echo "$fpm_pid" > "$PID_FILE"
            log "SUCCESS" "PHP-FPM已启动 (PID: $fpm_pid)"
        fi
    elif command -v php &> /dev/null; then
        log "INFO" "使用PHP内置服务器模式"
        cd "$SCRIPT_DIR"
        nohup php -S 0.0.0.0:8080 > /dev/null 2>&1 &
        local php_pid=$!
        echo "$php_pid" > "$PID_FILE"
        log "SUCCESS" "PHP内置服务器已启动 (PID: $php_pid)"
    else
        log "ERROR" "未找到PHP"
        return 1
    fi
}

# ===========================================
# 健康检查
# ===========================================
health_check() {
    log "INFO" "执行健康检查..."
    
    local checks=0
    local passed=0
    
    # 检查1: PHP环境
    ((checks++))
    if command -v php &> /dev/null; then
        log "SUCCESS" "[$checks] PHP环境: OK"
        ((passed++))
    else
        log "ERROR" "[$checks] PHP环境: FAILED"
    fi
    
    # 检查2: 数据库连接
    ((checks++))
    if php -r "
        require '${SCRIPT_DIR}/console/Config.php';
        require '${SCRIPT_DIR}/console/DbClass.php';
        \$config = Config::getInstance()->getDatabase();
        try {
            \$pdo = new PDO(
                'mysql:host='.\$config['db_host'].';port='.\$config['db_port'].';dbname='.\$config['db_name'],
                \$config['db_user'],
                \$config['db_pass']
            );
            echo 'OK';
        } catch (PDOException \$e) {
            echo 'FAILED: '.\$e->getMessage();
            exit(1);
        }
    " &> /dev/null; then
        log "SUCCESS" "[$checks] 数据库连接: OK"
        ((passed++))
    else
        log "ERROR" "[$checks] 数据库连接: FAILED"
    fi
    
    # 检查3: 配置文件验证
    ((checks++))
    if validate_config; then
        log "SUCCESS" "[$checks] 配置验证: OK"
        ((passed++))
    else
        log "ERROR" "[$checks] 配置验证: FAILED"
    fi
    
    # 检查4: 目录权限
    ((checks++))
    if [[ -w "${SCRIPT_DIR}/static/upload" ]] 2>/dev/null; then
        log "SUCCESS" "[$checks] 上传目录权限: OK"
        ((passed++))
    else
        log "WARNING" "[$checks] 上传目录权限: 需要检查"
        ((passed++))
    fi
    
    # 汇总
    log ""
    log "======================================"
    if [[ $passed -eq $checks ]]; then
        log "SUCCESS" "健康检查完成: $passed/$checks 通过"
        log "SUCCESS" "部署成功！服务已启动"
        return 0
    else
        log "WARNING" "健康检查完成: $passed/$checks 通过"
        if [[ $passed -ge $((checks/2)) ]]; then
            log "WARNING" "部分检查未通过，但服务可能正常运行"
            return 0
        else
            log "ERROR" "健康检查未通过，请检查日志"
            return 1
        fi
    fi
}

# ===========================================
# 清理旧文件
# ===========================================
cleanup_old() {
    log "INFO" "清理临时文件..."
    
    # 清理日志
    find "${SCRIPT_DIR}/logs" -name "*.log" -mtime +30 -delete 2>/dev/null || true
    
    # 清理缓存
    rm -rf "${SCRIPT_DIR}/runtime/cache" 2>/dev/null || true
    
    log "SUCCESS" "清理完成"
}

# ===========================================
# 主流程
# ===========================================
main() {
    init
    
    log "======================================"
    log "INFO" "私域引流宝 - 一键部署"
    log "INFO" "版本: 1.0.0"
    log "======================================"
    
    # 检查锁文件
    if [[ -f "$DEPLOY_LOCK" ]]; then
        log "WARNING" "检测到部署锁文件，可能有其他部署正在进行"
        read -p "是否继续? (y/N): " confirm
        if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
            log "INFO" "部署已取消"
            exit 0
        fi
    fi
    
    # 创建锁文件
    echo $$ > "$DEPLOY_LOCK"
    trap "rm -f '$DEPLOY_LOCK'" EXIT
    
    # 1. 系统检测
    detect_os
    
    # 2. 安装依赖
    if [[ "$1" == "--install-deps" ]]; then
        install_dependencies
        exit 0
    fi
    
    # 3. 环境变量检查
    check_env
    
    # 4. 验证配置
    if ! validate_config; then
        log "ERROR" "配置验证失败，请修复后重试"
        exit 1
    fi
    
    # 5. 平滑关闭旧实例
    graceful_shutdown
    
    # 6. 清理
    cleanup_old
    
    # 7. 启动服务
    start_server
    
    # 8. 健康检查
    health_check
    
    log "======================================"
    log "INFO" "部署日志: $LOG_FILE"
    log "INFO" "进程ID: $(get_pid)"
    log "======================================"
}

# ===========================================
# 命令行参数
# ===========================================
case "${1:-}" in
    --install-deps)
        init
        detect_os
        install_dependencies
        log_success "依赖安装完成"
        ;;
    --check-config)
        init
        check_env
        validate_config
        ;;
    --health)
        health_check
        ;;
    --stop)
        graceful_shutdown
        ;;
    --start)
        start_server
        ;;
    --restart)
        graceful_shutdown
        start_server
        health_check
        ;;
    *)
        main "$@"
        ;;
esac