# apc
docker hub地址: https://hub.docker.com/r/lin2798003/apc

用途：
* 项目进行自动化crontab任务
* 项目进行持久化脚本

### linux
alpine:3.7
### php7
php:7.2
### command
```shell
crontab -l > /tmp/crontab.bak
crontab /tmp/crontab.bak
```

### php扩展支持
```
[PHP Modules]
amqp
apcu
Core
ctype
curl
date
dom
exif
fileinfo
filter
ftp
gd
gettext
hash
iconv
igbinary
intl
json
libxml
mbstring
memcached
mongodb
msgpack
mysqlnd
openssl
pcre
PDO
pdo_mysql
pdo_pgsql
pdo_sqlite
Phar
posix
readline
Reflection
session
SimpleXML
soap
sodium
SPL
sqlite3
standard
tokenizer
xml
xmlreader
xmlwriter
xsl
yaf
Zend OPcache
zip
zlib

[Zend Modules]
Zend OPcache
```
### opcache
自动开启且不进行定时检测
### cron支持
项目根目录(即/var/www/html目录)包含cron.json格式如下:
```json
{
  "hook": "",
  "post": 0,
  "jobs": [
    {
      "time": "* * * * *",
      "value": "echo hello"
    },
    {
      "time": "*/10 * * * *",
      "value": "php $APP_PATH/think module/controller/action"
    }
  ]
}
```
说明:
* hook: 钉钉群报警机器人url,如果为空或者不写则默认使用环境变量$APP_MONITOR_HOOK进行发送
* post: 标准输出日志是否进行钉钉收集,1为收集,默认为不收集
* time: 标准crontab格式(分 时 日 月 周)
* value: 标准shell脚本

## 环境变量
* APP_NAME: app名称
* APP_PATH: 项目所在目录(默认为:/var/www/html)
* APP_MONITOR_HOOK: app报警钉钉群机器人webhook
* APP_INIT_SHELL: 初始化执行脚本,如:php \$APP_PATH/a.php
* PHP_MEM_LIMIT: php进程内存限制,默认512M