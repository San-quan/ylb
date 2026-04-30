#!/bin/sh
# 项目打包脚本 - 私域引流宝 v2.4.6
# 使用方法: ./export_project.sh

PROJECT_NAME="liuyinbao_v2.4.6"
DATE=$(date +%Y%m%d)

echo "=== 私域引流宝项目打包 ==="
echo "版本: 2.4.6"
echo "时间: $DATE"
echo ""

# 1. 创建导出目录
EXPORT_DIR="/var/minis/exports"
mkdir -p $EXPORT_DIR

# 2. 打包项目文件（排除不必要的文件）
echo "[1/3] 打包项目文件..."
cd /var/minis/workspace

# 创建临时排除列表
EXCLUDE_LIST="/tmp/exclude_list_$$.txt"
cat > $EXCLUDE_LIST << EOF
--exclude=*.log
--exclude=*.tmp
--exclude=.DS_Store
--exclude=Thumbs.db
--exclude=*.swp
--exclude=*~
EOF

# 打包
tar -czf "$EXPORT_DIR/${PROJECT_NAME}_files.tar.gz" \
    -X $EXCLUDE_LIST \
    FrameBridge common console index.html s static wailian 2>/dev/null

rm -f $EXCLUDE_LIST

# 3. 导出数据库（需要MySQL）
echo "[2/3] 导出数据库..."
DB_NAME="llk5g_com"
DB_USER="llk5g_com"  
DB_PASS="8dwZwDkGMaWEdijK"

mysqldump -u$DB_USER -p$DB_PASS $DB_NAME > "$EXPORT_DIR/${PROJECT_NAME}_database.sql" 2>/dev/null

if [ -f "$EXPORT_DIR/${PROJECT_NAME}_database.sql" ]; then
    echo "   数据库导出成功"
else
    echo "   数据库导出失败（可能没有MySQL客户端）"
    echo "   建议手动导出数据库"
fi

# 4. 创建安装说明
echo "[3/3] 创建安装说明..."
cat > "$EXPORT_DIR/安装说明.txt" << 'EOF'
=====================================
私域引流宝 v2.4.6 部署指南
=====================================

【文件结构】
liuyinbao_v2.4.6_files.tar.gz  - 项目源码
安装说明.txt                   - 本文件

【环境要求】
- PHP 7.4+ 或 8.0+
- MySQL 5.7+ 或 MariaDB 10.2+
- Nginx 或 Apache
- PDO扩展
- OpenSSL扩展

【部署步骤】

1. 上传文件
   解压 liuyinbao_v2.4.6_files.tar.gz 到网站根目录
   例: /var/www/liuyinbao/

2. 导入数据库
   mysql -u用户名 -p数据库名 < liuyinbao_v2.4.6_database.sql

3. 配置数据库连接
   编辑 console/Db.php 修改数据库配置:
   - db_host: 数据库地址
   - db_name: 数据库名称
   - db_user: 数据库用户名
   - db_pass: 数据库密码

4. 设置目录权限
   chmod -R 755 /var/www/liuyinbao
   chmod -R 777 /var/www/liuyinbao/static/upload

5. 配置伪静态

   Nginx配置示例:
   location / {
       if (!-e $request_filename){
           rewrite ^(.*)$ /index.php?s=$1 last;
           break;
       }
   }
   
   Apache (.htaccess):
   <IfModule mod_rewrite.c>
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php?s=$1 [L]
   </IfModule>

6. 访问后台
   http://你的域名/console/index/
   默认管理员账号密码在安装时设置

7. 删除安装目录（安装完成后）
   rm -rf /var/www/liuyinbao/删install

【安全建议】
1. 修改数据库密码
2. 修改后台登录密码
3. 配置SSL证书使用HTTPS
4. 定期备份数据库

【版本信息】
- 系统版本: 2.4.6
- 已安装插件:
  * FrameProxy - 框架代理
  * domainCheckTask - 域名检测任务
  * h5chatPages - H5聊天页面
  * jumpWX - 微信外链/抖音卡片
  * pluginSDK - 插件SDK
  * qrcodeGenerator - 二维码生成器
  * visitorStats - 访客画像

【技术支持】
有问题请提交Issue或联系开发者

=====================================
EOF

# 输出结果
echo ""
echo "=== 打包完成 ==="
echo ""
ls -lh $EXPORT_DIR/
echo ""
echo "导出目录: $EXPORT_DIR"
echo ""
echo "下一步:"
echo "1. 下载 $EXPORT_DIR 下的文件"
echo "2. 按照 安装说明.txt 进行部署"