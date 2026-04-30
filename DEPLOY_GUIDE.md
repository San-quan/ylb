# 私域引流宝 - 一键部署指南

## 🚀 快速开始

### Bash环境（推荐）

```bash
# 完整部署
./deploy.sh

# 仅安装依赖
./deploy.sh --install-deps

# 仅检查配置
./deploy.sh --check-config

# 查看状态
./deploy.sh --status

# 重启服务
./deploy.sh --restart
```

### PHP环境

```bash
# 完整部署
php deploy.php

# 检查配置
php deploy.php --check

# 查看状态
php deploy.php --status

# 健康检查
php deploy.php --health
```

---

## 📋 部署流程

### 1. 系统检测
自动识别操作系统类型（Linux/macOS）和包管理器（apt/yum/apk/brew）。

### 2. 依赖安装
自动安装PHP及相关扩展：
- php-cli
- php-fpm
- php-mysql
- php-xml
- php-mbstring
- php-curl
- php-zip
- php-gd
- php-session
- php-openssl

### 3. 环境变量检查
- 检查`.env`文件是否存在
- 不存在时自动从`.env.example`复制
- 验证必需配置项（DB_HOST、DB_NAME、DB_PASSWORD等）
- 自动生成API_SECRET_KEY

### 4. 配置验证
使用Config类验证所有配置项：
- 类型检查（int/bool/string/email/url）
- 范围检查（端口1-65535、版本号X.Y.Z格式）
- 正则验证

### 5. 进程管理
- 发送SIGTERM信号优雅关闭
- 等待最多30秒
- 超时强制SIGKILL
- 启动新实例

### 6. 健康检查
| 检查项 | 说明 |
|--------|------|
| PHP环境 | PHP 7.4+ |
| PHP扩展 | pdo_mysql、json、mbstring等 |
| 配置文件 | .env存在且验证通过 |
| 目录权限 | static/upload可写 |
| 数据库连接 | PDO连接测试 |

---

## 📁 相关文件

```
.
├── deploy.sh          # Bash版一键部署脚本
├── deploy.php        # PHP版一键部署脚本
├── .env              # 环境变量配置（本地）
├── .env.example      # 配置模板
├── logs/             # 日志目录
│   ├── deploy_*.log  # 部署日志
│   ├── server.pid   # 进程ID
│   └── server_*.log # 服务日志
└── console/
    ├── Config.php    # 配置管理模块
    └── Db.php        # 数据库配置
```

---

## 🔧 配置要求

### 必需配置
```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=llk5g_com
DB_USER=llk5g_com
DB_PASSWORD=你的密码  # ⚠️ 必须修改
```

### 可选配置
```env
APP_DEBUG=false
SESSION_LIFETIME=604800
API_SECRET_KEY=自动生成
```

---

## 🛡️ 安全特性

1. **平滑关闭** - 使用SIGTERM信号，不中断正在处理的请求
2. **配置验证** - 启动前验证所有配置项
3. **健康检查** - 部署后自动验证数据库连接
4. **日志记录** - 所有操作记录到日志文件
5. **进程锁** - 防止并发部署

---

## 📊 日志查看

```bash
# 查看部署日志
tail -f logs/deploy_*.log

# 查看服务日志
tail -f logs/server_*.log
```

---

## ❓ 常见问题

### Q: 部署失败怎么办？
A: 查看部署日志 `logs/deploy_*.log`，根据错误信息修复。

### Q: 端口被占用？
A: 修改 `.env` 中的 `SERVER_PORT`，然后重启。

### Q: 数据库连接失败？
A: 检查 `.env` 中的数据库配置是否正确。

### Q: PHP扩展缺失？
A: 运行 `./deploy.sh --install-deps` 安装依赖。

---

## 🎯 使用示例

### 完整部署流程

```bash
# 1. 上传代码到服务器
scp -r ./project user@server:/var/www/

# 2. SSH登录服务器
ssh user@server

# 3. 进入项目目录
cd /var/www/project

# 4. 复制并编辑配置
cp .env.example .env
vi .env

# 5. 执行一键部署
./deploy.sh

# 6. 查看部署结果
./deploy.sh --status
```

### 生产环境部署

```bash
# 1. 创建生产环境配置
cp .env.example .env.production
vi .env.production

# 2. 使用生产配置部署
mv .env .env.backup
mv .env.production .env

# 3. 执行部署
./deploy.sh

# 4. 验证健康状态
./deploy.sh --health
```

---

## 📞 技术支持

如有问题，请查看日志文件或提交Issue。