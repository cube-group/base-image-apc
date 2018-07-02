#!/bin/sh

#启动初始化shell
php /extra/monitor/init.php >> /cli.log &
#启动cron
php /extra/monitor/start.php >> /cli.log &

