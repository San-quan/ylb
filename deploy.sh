#!/bin/sh
# ===========================================
# 私域引流宝 - 自动化运维体系
# ===========================================
# 版本: 2.0.0
# 功能:
#   • git pull 自动同步远程
#   • .env 自愈 (模板感知+配置回填)
#   • 智能依赖识别 (composer/apk/apt 自动)
#   • Git 工作流安全检查
#   • 敏感文件权限锁定
#   • 极简指令: ./deploy.sh
# ===========================================

set -eu

# ---------- 颜色 ----------
R='\033[0;31m'; G='\033[0;32m'; Y='\033[1;33m'; B='\033[0;34m'; C='\033[0;36m'; N='\033[0m'
ok()  { echo -e " ${G}✔${N} $1"; }
warn(){ echo -e " ${Y}⚠${N} $1"; }
err() { echo -e " ${R}✘${N} $1"; }
info(){ echo -e " ${B}→${N} $1"; }
tit() { echo -e "\n${C}━━━ $1 ━━━${N}"; }

# ---------- 路径 ----------
DIR="$(cd "$(dirname "$0")" && pwd)"
LOG="${DIR}/logs/ops.log"
LOCK="${DIR}/logs/.deploy.lock"
ENV_FILE="${DIR}/.env"
ENV_TPL="${DIR}/.env.example"
GITIGNORE="${DIR}/.gitignore"
UPLOAD="${DIR}/static/upload"

mkdir -p "${DIR}/logs"

# ---------- 锁 ----------
cleanup() { rm -f "$LOCK"; }
trap cleanup EXIT
if [[ -f "$LOCK" ]]; then
    err "已有部署进程运行中 ($(cat "$LOCK"))"
    exit 1
fi
echo "$$" > "$LOCK"

# ---------- 日志 ----------
log() {
    local lvl="$1"; shift
    local ts
    ts="$(date '+%Y-%m-%d %H:%M:%S')"
    echo "[$ts] [$lvl] $*" >> "$LOG"
}
log_wrap() {
    log "$@"
    case "$1" in
        OK)   ok "$2" ;;
        WARN) warn "$2" ;;
        ERR)  err "$2" ;;
        INFO) info "$2" ;;
    esac
}

# ======================================================================
#  1. GIT 同步  +  安全检查
# ======================================================================
git_sync() {
    tit "1/6 Git 工作流"

    # 检查远程
    local remote
    remote="$(git remote -v 2>/dev/null | head -1 || true)"
    if [[ -z "$remote" ]]; then
        log_wrap WARN "未配置远程仓库，跳过 Git 同步"
        return 0
    fi
    ok "远程仓库: $(echo "$remote" | awk '{print $1, $2}')"

    # stash 本地变更
    local dirty=false
    if ! git diff --quiet 2>/dev/null; then dirty=true; fi
    $dirty && git stash push -m "ops-auto-stash-$(date +%s)" 2>/dev/null && \
        log_wrap INFO "本地变更已暂存 (stash)"

    # pull
    if git pull --rebase --autostash 2>/dev/null; then
        log_wrap OK "代码已同步最新"
    else
        log_wrap WARN "pull 失败，尝试 fetch + reset"
        git fetch origin 2>/dev/null
        git reset --hard "origin/$(git branch --show-current)" 2>/dev/null || true
    fi

    # pop stash
    $dirty && git stash pop 2>/dev/null && log_wrap INFO "本地变更已恢复"

    # ---- 安全检查 ----
    echo ""
    info "最近一次提交:"
    git log -1 --format="  %h %s (%ar)" 2>/dev/null || true

    info "变更文件清单:"
    git diff --name-status HEAD~1..HEAD 2>/dev/null | head -20 | sed 's/^/  /' || \
        git show --name-status --format="" HEAD 2>/dev/null | head -20 | sed 's/^/  /'

    # 敏感文件忽略校验
    echo ""
    info "敏感文件忽略状态:"
    local sensitive=(".env" "*.pem" "*.key" "credentials.json" "secrets.json")
    local all_ok=true
    for p in "${sensitive[@]}"; do
        if git check-ignore "$p" 1>/dev/null 2>&1; then
            ok "  $p 已忽略 ✓"
        else
            warn "  $p 未被忽略 !"
            all_ok=false
        fi
    done

    # 未暂存变更
    local unstaged
    unstaged="$(git status --porcelain 2>/dev/null | grep -v '^?' | head -10 || true)"
    if [[ -n "$unstaged" ]]; then
        warn "未暂存变更:"
        echo "$unstaged" | sed 's/^/  /' | while read -r line; do warn "$line"; done
    else
        ok "工作区干净"
    fi

    return 0
}

# ======================================================================
#  2. 系统检测
# ======================================================================
detect_os() {
    tit "2/6 系统环境检测"

    if [[ "$OSTYPE" == linux-gnu* ]]; then
        if command -v apt-get &>/dev/null; then
            OS="debian"; PKG="apt-get"
        elif command -v yum &>/dev/null; then
            OS="centos"; PKG="yum"
        elif command -v apk &>/dev/null; then
            OS="alpine"; PKG="apk"
        else
            OS="linux"; PKG=""
        fi
    elif [[ "$OSTYPE" == darwin* ]]; then
        OS="macos"; PKG="brew"
    else
        OS="unknown"; PKG=""
    fi

    ok "系统: $OS 架构: $(uname -m) 包管理器: ${PKG:-none}"
}

# ======================================================================
#  3. 智能依赖识别
# ======================================================================
smart_deps() {
    tit "3/6 智能依赖识别"

    local need_php=false; local need_nginx=false; local need_composer=false

    # 通过文件特征自动识别
    [[ -f "${DIR}/console/DbClass.php" || -f "${DIR}/index.php" ]] && need_php=true
    [[ -f "${DIR}/composer.json" || -f "${DIR}/composer.lock" ]] && need_composer=true
    [[ -f "${DIR}/nginx.conf" || -f "${DIR}/.nginx" ]] && need_nginx=true

    info "PHP     → $([ "$need_php" = true ] && echo '需要' || echo '不需要')"
    info "Composer → $([ "$need_composer" = true ] && echo '需要' || echo '不需要')"
    info "Nginx   → $([ "$need_nginx" = true ] && echo '需要' || echo '不需要')"

    if [[ "$need_php" == true && -z "$(command -v php 2>/dev/null)" ]]; then
        log_wrap INFO "检测到 PHP 源码但未安装，自动安装..."
        case "$OS" in
            debian)
                apt-get update -qq && apt-get install -y -qq \
                    php php-cli php-mysql php-xml php-mbstring php-curl \
                    php-zip php-gd php-json php-session php-openssl 2>&1 | tail -1 ;;
            centos)
                yum install -y php php-cli php-mysql php-xml php-mbstring \
                    php-curl php-zip php-gd php-json php-session php-openssl 2>&1 | tail -1 ;;
            alpine)
                apk add --no-cache php php-cli php-mysqlnd php-xml \
                    php-mbstring php-curl php-zip php-gd php-session php-openssl 2>&1 | tail -1 ;;
            macos)
                command -v brew &>/dev/null && brew install php 2>&1 | tail -1 || \
                    log_wrap WARN "请手动安装 PHP";;
            *) log_wrap WARN "未知系统，跳过 PHP 安装" ;;
        esac
    fi

    if command -v php &>/dev/null; then
        ok "PHP: $(php -r 'echo PHP_VERSION;')"
    else
        warn "PHP 未安装"
    fi

    if [[ "$need_composer" == true ]] && [[ ! -f "${DIR}/vendor/autoload.php" ]]; then
        if command -v composer &>/dev/null; then
            log_wrap INFO "执行 composer install..."
            cd "$DIR" && composer install --no-dev --quiet 2>&1 && ok "依赖安装完成" || \
                log_wrap WARN "composer install 失败"
        else
            log_wrap WARN "需要 Composer，请手动安装: https://getcomposer.org"
        fi
    fi
}

# ======================================================================
#  4. .env 自愈
# ======================================================================
self_heal_env() {
    tit "4/6 环境变量自愈"

    if [[ -f "$ENV_FILE" ]]; then
        ok ".env 已存在"
    else
        if [[ ! -f "$ENV_TPL" ]]; then
            err ".env.example 不存在，无法生成 .env"
            exit 1
        fi

        log_wrap INFO ".env 缺失，基于模板自动生成..."

        # 读取模板，去掉注释行，只保留 KEY= 结构
        while IFS='=' read -r key val rest; do
            [[ "$key" =~ ^#.*$ || -z "$key" ]] && continue
            key="$(echo "$key" | xargs)"
            local template_val="$val"
            [[ -n "$rest" ]] && template_val="$val=$rest"

            case "$key" in
                DB_PASSWORD)
                    val="$(openssl rand -base64 18 2>/dev/null || echo 'ChangeMe!' )"
                    ;;
                API_SECRET_KEY)
                    val="$(openssl rand -hex 32 2>/dev/null || echo '')"
                    ;;
                APP_DEBUG)
                    val="false"
                    ;;
                NOTIFICATION_EMAIL|NOTIFICATION_WEBHOOK|OSS_*)
                    val=""
                    ;;
                *)
                    val="${template_val%%#*}"
                    val="$(echo "$val" | xargs)"
                    ;;
            esac
            echo "${key}=${val}" >> "$ENV_FILE"
        done < "$ENV_TPL"

        log_wrap OK ".env 已自动生成（请检查 DB_PASSWORD、API_SECRET_KEY 等敏感项）"
    fi

    # 锁定权限
    chmod 600 "$ENV_FILE"
    ok ".env 权限已锁定: $(stat -c '%a' "$ENV_FILE" 2>/dev/null || stat -f '%A' "$ENV_FILE" 2>/dev/null)"

    # 验证核心配置
    local missing=()
    for k in DB_HOST DB_NAME DB_USER DB_PASSWORD; do
        if grep -q "^${k}=" "$ENV_FILE"; then
            val="$(grep "^${k}=" "$ENV_FILE" | cut -d= -f2-)"
            [[ -z "$val" ]] && missing+=("$k")
        else
            missing+=("$k")
        fi
    done

    if [[ ${#missing[@]} -gt 0 ]]; then
        warn "以下配置项为空，请手动编辑 .env: ${missing[*]}"
    else
        ok "核心配置齐全"
    fi
}

# ======================================================================
#  5. 权限锁定
# ======================================================================
lockdown() {
    tit "5/6 文件权限锁定"

    # .env
    [[ -f "$ENV_FILE" ]] && chmod 600 "$ENV_FILE" && ok ".env → 600"

    # 上传目录
    if [[ -d "$UPLOAD" ]]; then
        chmod 755 "$UPLOAD"
        find "$UPLOAD" -type d -exec chmod 755 {} \; 2>/dev/null || true
        find "$UPLOAD" -type f -exec chmod 644 {} \; 2>/dev/null || true
        ok "static/upload → 755/644"
    else
        mkdir -p "$UPLOAD" && chmod 755 "$UPLOAD"
        ok "创建 static/upload (755)"
    fi

    # 日志
    chmod 750 "${DIR}/logs" 2>/dev/null || true
    ok "logs 目录 → 750"

    # 通用
    find "${DIR}" -type d -exec chmod 755 {} \; 2>/dev/null || true
    find "${DIR}" -type f \( -name '*.php' -o -name '*.html' -o -name '*.js' -o -name '*.css' \) \
        -exec chmod 644 {} \; 2>/dev/null || true

    ok "通用文件权限已标准化"
}

# ======================================================================
#  6. 健康检查 + 汇总
# ======================================================================
health_summary() {
    tit "6/6 系统健康检查"

    local pass=0 fail=0

    # PHP
    if command -v php &>/dev/null; then
        ok "[1] PHP $(php -r 'echo PHP_VERSION;')"; ((pass++))
    else
        err "[1] PHP 未安装"; ((fail++))
    fi

    # 必需扩展
    local exts=(pdo pdo_mysql json mbstring curl openssl)
    for e in "${exts[@]}"; do
        php -m 2>/dev/null | grep -qi "^$e$" && continue
        err "    扩展 $e 缺失"; ((fail++))
    done
    ((fail == 0)) && ok "[2] PHP 扩展齐全"

    # .env
    if [[ -f "$ENV_FILE" ]]; then
        local mode
        mode="$(stat -c '%a' "$ENV_FILE" 2>/dev/null || stat -f '%A' "$ENV_FILE" 2>/dev/null)"
        ok "[3] .env (权限: $mode)"; ((pass++))
    else
        err "[3] .env 缺失"; ((fail++))
    fi

    # 配置验证
    if command -v php &>/dev/null && [[ -f "${DIR}/console/Config.php" ]]; then
        local cfg_result
        cfg_result="$(php -r "
            require '${DIR}/console/Config.php';
            \$c = Config::getInstance();
            \$c->load('${ENV_FILE}');
            echo \$c->hasErrors() ? 'FAIL' : 'OK';
        " 2>/dev/null || echo 'FAIL')"

        if [[ "$cfg_result" == "OK" ]]; then
            ok "[4] Config验证通过"; ((pass++))
        else
            err "[4] Config验证失败"; ((fail++))
        fi
    else
        warn "[4] 跳过 Config 验证（PHP 未就绪）"
    fi

    # Git 忽略
    if git check-ignore .env 1>/dev/null 2>&1; then
        ok "[5] .env 已被 gitignore"; ((pass++))
    else
        err "[5] .env 未被 gitignore"; ((fail++))
    fi

    # 上传目录
    if [[ -d "$UPLOAD" && -w "$UPLOAD" ]]; then
        ok "[6] upload 目录可写"; ((pass++))
    else
        warn "[6] upload 目录不可写"; ((pass++))
    fi

    echo ""
    tit "━━━ 部署结果 ━━━"
    info "日志: $LOG"
    ok "通过: ${pass}  失败: ${fail}"
    if [[ $fail -eq 0 ]]; then
        echo -e "${G}━━━━━━━━━━━━━━━━━━━━━━━${N}"
        echo -e "${G}  部署成功，系统就绪 ✓   ${N}"
        echo -e "${G}━━━━━━━━━━━━━━━━━━━━━━━${N}"
        return 0
    else
        echo -e "${Y}━━━━━━━━━━━━━━━━━━━━━━━${N}"
        echo -e "${Y}  部署完成，${fail} 项需关注  ${N}"
        echo -e "${Y}━━━━━━━━━━━━━━━━━━━━━━━${N}"
        return 1
    fi
}

# ======================================================================
#  主入口
# ======================================================================
main() {
    cat <<'EOF'
┌─────────────────────────────────────┐
│  私域引流宝 · 自动化运维体系 v2.0.0  │
│  DevOps Pipeline                    │
└─────────────────────────────────────┘
EOF

    git_sync
    detect_os
    smart_deps
    self_heal_env
    lockdown
    health_summary
}

main