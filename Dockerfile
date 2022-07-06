FROM alpine:3.15

RUN apk update && apk add --no-cache \
    curl \
    nginx \
    nginx-mod-http-brotli \
    php7-bz2 \
    php7-fpm \
    php7-pdo \
    php7-pdo_mysql \
    php7-pdo_odbc \
    php7-pdo_pgsql \
    php7-pdo_sqlite \
    php7-opcache \
    php7-session \
    php7-zip \
    supervisor \
    && rm -rf /var/cache/apk/ \
    && rm -rf /root/.cache \
    && rm -rf /tmp/*  \
    && mkdir /app \
    && mkdir /app/tmp \
    && chown -R nginx:nginx /app

COPY ./php.ini /etc/php7/conf.d/999-php.ini
COPY ./www.conf /etc/php7/php-fpm.d/www.conf

COPY ./nginx.conf /etc/nginx/nginx.conf
COPY ./fastcgi.conf /etc/nginx/fastcgi.conf

COPY ./supervisord.conf /etc/supervisord.conf

COPY ./app /app

WORKDIR /app
EXPOSE 8080

ENTRYPOINT ["supervisord", "--nodaemon", "--configuration", "/etc/supervisord.conf"]