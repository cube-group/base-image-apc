## apc
[![996.icu](https://img.shields.io/badge/link-996.icu-red.svg)](https://996.icu)

这是一个docker镜像,它主要用于php job服务
### 几个重要指标
* 在当前的容器编排工具中,单容器资源将限制内存为4G-5G之间
* os: alpine linux 3.8
* php: php-cli-7.2.8
* opcache: on

### Cron Job支持
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
* 注意: ```一定要把php标准错误输出打开,否则无法捕捉到crontab错误```

### 核心环境变量支持
* APP_NAME: app名称(一般情况下无需配置)
* APP_PATH: 项目所在目录(一定要记住默认为:/var/www/html)
* APP_INIT_SHELL: 以后台方式执行当前容器初始化脚本,如:```php $APP_PATH/init.sh```
* APP_MONITOR_HOOK: app报警钉钉群机器人webhook url

### 辅助类环境变量
* PHP_MEM_LIMIT: php进程内存限制,默认512M


### PHP Version
```
PHP 7.2.8 (cli) (built: Jul 28 2018 17:55:09) ( NTS )
Copyright (c) 1997-2018 The PHP Group
Zend Engine v3.2.0, Copyright (c) 1998-2018 Zend Technologies
    with Zend OPcache v7.2.8, Copyright (c) 1999-2018, by Zend Technologies
```

### PHP Extensions
```
[PHP Modules]
amqp
apcu
bcmath
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
gmp
hash
iconv
intl
json
libxml
mbstring
memcached
mongodb
mysqlnd
openssl
pcre
PDO
pdo_mysql
pdo_pgsql
Phar
posix
readline
redis
Reflection
session
SimpleXML
soap
sodium
SPL
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
