FROM php:7.2-cli-alpine3.7
#alpine-3.7
#php-cli-7.2.14

MAINTAINER lin2798003 development "lin2798003@sina.com"

USER root

ENV APP_NAME apn
ENV APP_PATH /var/www/html
#ENV APP_MONITOR_HOOK DINGTALK-HOOK

ENV PHP_MEM_LIMIT 512M
ENV PHP_POST_MAX_SIZE 100M

# 备份原始文件
# 修改为国内镜像源
RUN cp /etc/apk/repositories /etc/apk/repositories.bak && \
    echo "http://mirrors.aliyun.com/alpine/v3.7/main/" > /etc/apk/repositories && \
    apk update && \
    apk add --no-cache \
    tzdata \
    wget \
    curl \
    libcurl \
    python \
    python-dev \
    py-pip \
    augeas-dev \
    ca-certificates \
    dialog \
    autoconf \
    make \
    gcc \
    musl-dev \
    linux-headers \
    libpng-dev \
    icu-dev \
    libpq \
    libxslt-dev \
    libffi-dev \
    freetype-dev \
    gettext-dev \
    postgresql-dev \
    libjpeg-turbo-dev && \
    docker-php-ext-configure gd \
    --with-gd \
    --with-freetype-dir=/usr/include/ \
    --with-png-dir=/usr/include/ \
    --with-jpeg-dir=/usr/include/ && \
    docker-php-ext-install iconv pdo_mysql pdo_pgsql gd exif intl xsl soap zip opcache bcmath && \
    docker-php-source delete && \
    apk add libmemcached-libs libmemcached-dev zlib-dev \
    && pecl install igbinary \
    && echo 'extension=igbinary.so' >> /usr/local/etc/php/conf.d/docker-php-ext-igbinary.ini \
    && pecl install msgpack \
    && echo 'extension=msgpack.so' >> /usr/local/etc/php/conf.d/docker-php-ext-msgpack.ini \
    && pecl install memcached \
    && echo 'extension=memcached.so' >> /usr/local/etc/php/conf.d/docker-php-ext-memcached.ini \
    && apk add rabbitmq-c-dev \
    && pecl install amqp \
    && echo 'extension=amqp.so' >> /usr/local/etc/php/conf.d/docker-php-ext-amqp.ini \
    # extensions install
    && pecl install redis && \
    echo '[redis]' >> /usr/local/etc/php/conf.d/docker-php-ext-redis.ini && \
    echo 'extension=redis.so' >> /usr/local/etc/php/conf.d/docker-php-ext-redis.ini && \
    echo 'opcache.validate_timestamps=0' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini && \
    echo 'opcache.enable=1' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini && \
    echo 'opcache.enable_cli=1' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini && \
    pecl install yaf && \
    echo '[yaf]' >> /usr/local/etc/php/conf.d/docker-php-ext-yaf.ini && \
    echo 'extension=yaf.so' >> /usr/local/etc/php/conf.d/docker-php-ext-yaf.ini && \
    echo 'yaf.cache_config=1' >> /usr/local/etc/php/conf.d/docker-php-ext-yaf.ini && \
    echo 'yaf.use_namespace=1' >> /usr/local/etc/php/conf.d/docker-php-ext-yaf.ini && \
    echo 'yaf.use_spl_autoload=1' >> /usr/local/etc/php/conf.d/docker-php-ext-yaf.ini && \
    pecl install mongodb && \
    echo '[mongodb]' >> /usr/local/etc/php/conf.d/docker-php-ext-mongodb.ini && \
    echo 'extension=mongodb.so' >> /usr/local/etc/php/conf.d/docker-php-ext-mongodb.ini && \
    pecl install apcu && \
    echo '[apcu]' >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini && \
    echo 'extension=apcu.so' >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini && \
    echo 'apc.enabled=1' >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini && \
    echo 'apc.shm_size=32M' >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini && \
    echo 'apc.enable_cli=1' >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini && \
    # remove useless
    apk del \
    dpkg-dev dpkg \
    file \
    g++ \
    gcc \
    libc-dev \
    make \
    pkgconf \
    re2c

#设置时区
RUN apk add bash \
    && /bin/cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime \
    && echo 'Asia/Shanghai' >/etc/timezone

COPY ./scripts/ /extra/
COPY ./monitor/ /extra/monitor/

WORKDIR ${APP_PATH}

CMD ["bash","/extra/start.sh"]