#!/bin/sh

# Increase the memory_limit
if [ ! -z "$PHP_MEM_LIMIT" ]; then
 echo "memory_limit = ${PHP_MEM_LIMIT}" >> /usr/local/etc/php/conf.d/docker-php.ini
fi

# Increase the memory_limit
#if [ ! -z "$PHP_POST_MAX_SIZE" ]; then
# echo "post_max_size = ${PHP_POST_MAX_SIZE}" >> /usr/local/etc/php/conf.d/docker-php.ini
#fi
#
## Increase the memory_limit
#if [ ! -z "$PHP_UPLOAD_MAX_FILESIZE" ]; then
# echo "upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE}" >> /usr/local/etc/php/conf.d/docker-php.ini
#fi


#create cli.log
touch /cli.log

#extra third shell start
bash /extra/external.sh

#tailf
tail -f /cli.log