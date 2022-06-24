FROM php:8.0-fpm-alpine

# Add some system packages
RUN apk update && apk add --no-cache \
    brotli \
    # shadow \
    supervisor \
    curl \
    nginx \
    nginx-mod-http-brotli \
    && rm -rf /var/cache/apk/*

# Add some PHP extensions
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN IPE_LZF_BETTERCOMPRESSION=1 install-php-extensions \
    bz2 \
    lzf \
    mysqli \
    opcache \
    pdo_dblib \
    pdo_odbc \
    pgsql \
    zip

COPY ./supervisord.conf /etc/supervisord.conf
COPY ./nginx/ /etc/nginx/
COPY ./php/php.ini /usr/local/etc/php/conf.d/999-php.ini
COPY ./public/ /app/

WORKDIR /app
RUN curl -fsSL "https://www.adminer.org/latest-mysql-en.php" -o adminer.php && \
	addgroup -S adminer \
    &&	adduser -S -G adminer adminer \
    &&	chown -R adminer:adminer /app
    
EXPOSE 8080
ENTRYPOINT ["supervisord", "--nodaemon", "--configuration", "/etc/supervisord.conf"]