# 私域引流宝 - 配置管理文档

## 📋 概述

本系统采用基于 `.env` 文件的环境变量配置方案，具有以下特点：

- ✅ **类型安全** - 自动类型转换和验证
- ✅ **解耦设计** - 配置与业务逻辑分离
- ✅ **统一接口** - 通过 Config 类或便捷函数获取配置
- ✅ **验证机制** - 内置配置验证规则
- ✅ **敏感信息安全** - `.env` 文件不提交到版本库

---

## 🚀 快速开始

### 1. 配置环境变量

```bash
# 复制示例配置
cp .env.example .env

# 编辑配置
vi .env
```

### 2. 在代码中使用配置

```php
// 方式1: 使用 config() 函数
$version = config('APP_VERSION');
$dbHost = config('DB_HOST', 'localhost');

// 方式2: 使用 Config 类
$config = Config::getInstance();
$dbConfig = $config->getDatabase();

// 方式3: 使用魔术方法
echo $config->DB_HOST;

// 方式4: 分组获取
$appConfig = $config->getApp();
$apiConfig = $config->getApi();
```

---

## 📝 配置项说明

详见 [`.env.example`](.env.example) 文件

### 数据库配置

| 变量 | 类型 | 必填 | 说明 |
|------|------|------|------|
| DB_HOST | string | ✅ | 数据库主机地址 |
| DB_PORT | int | ✅ | 端口 (1-65535) |
| DB_NAME | string | ✅ | 数据库名称 |
| DB_USER | string | ✅ | 用户名 |
| DB_PASSWORD | string | ✅ | 密码 |
| DB_PREFIX | string | ❌ | 表前缀 |

### 应用配置

| 变量 | 类型 | 必填 | 说明 |
|------|------|------|------|
| APP_VERSION | string | ✅ | 版本号 (X.Y.Z) |
| APP_DEBUG | bool | ❌ | 调试模式 |
| APP_NAME | string | ❌ | 应用名称 |

### 安全配置

| 变量 | 类型 | 说明 |
|------|------|------|
| SESSION_LIFETIME | int | 会话超时 (秒) |
| CSRF_TOKEN | bool | CSRF保护 |
| API_SECRET_KEY | string | API密钥 (至少32位) |

---

## 🛡️ 安全建议

1. **不要提交 `.env` 文件**
   - `.gitignore` 已配置忽略规则
   - 敏感信息（数据库密码等）仅存储在本地

2. **生产环境必须修改的项**
   - `DB_PASSWORD` - 使用强密码
   - `APP_DEBUG` - 设置为 `false`
   - `API_SECRET_KEY` - 设置至少32位的密钥

3. **多环境部署**
   - 开发环境: `.env.development`
   - 测试环境: `.env.staging`
   - 生产环境: `.env.production`

---

## 🔧 高级用法

### 自定义验证规则

```php
Config::getInstance()->addRule('CUSTOM_KEY', [
    'type' => 'string',
    'required' => true,
    'pattern' => '/^[A-Z]{10}$/',
    'message' => 'CUSTOM_KEY 必须是10位大写字母'
]);
```

### 环境变量优先级

```
.env 文件 < .env.local < 系统环境变量
```

### 便捷函数

```php
// 获取所有配置
config()                   // 返回数组

// 获取单个配置
config('KEY')              // 返回值
config('KEY', 'default')   // 带默认值

// 获取分组配置
db_config()               // 数据库配置数组
app_config()              // 应用配置数组
```

---

## 🧪 测试

```bash
php console/test_config.php
```

---

## 📚 相关文件

| 文件 | 说明 |
|------|------|
| `console/Config.php` | 配置管理核心类 |
| `console/Db.php` | 数据库配置入口 |
| `.env` | 环境变量文件（本地） |
| `.env.example` | 配置模板（可提交） |
| `.gitignore` | Git忽略规则 |