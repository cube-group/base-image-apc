#!/bin/sh

# Increase the memory_limit
if [ ! -z "$PHP_MEM_LIMIT" ]; then
    echo "memory_limit = ${PHP_MEM_LIMIT}"  >> ${php_ini}
fi

if [ ! -d "/data/log" ];then
    mkdir -p /data/log
    chmod 777 /data/log
fi

supervisord -c /supervisor.conf