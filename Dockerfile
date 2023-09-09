FROM alpine:3.18

WORKDIR /app

RUN apk update \
    && apk upgrade \
    && apk add --update --no-cache \
    npm curl apache2 memcached php81-apache2 php81 \
    php81-apcu php81-bcmath php81-bz2 \
    php81-calendar php81-ctype php81-curl \
    php81-dba php81-dom php81-embed \
    php81-enchant php81-exif php81-ffi \
    php81-fileinfo php81-ftp php81-gd \
    php81-gettext php81-gmp php81-iconv \
    php81-imap php81-intl php81-json \
    php81-ldap php81-mbstring php81-mysqli \
    php81-mysqlnd php81-odbc php81-opcache \
    php81-openssl php81-pcntl php81-pdo \
    php81-pdo_dblib php81-pdo_mysql \
    php81-pdo_odbc php81-pdo_pgsql \
    php81-pdo_sqlite php81-pear \
    php81-pgsql php81-phar php81-phpdbg \
    php81-posix php81-pspell php81-session \
    php81-shmop php81-simplexml php81-snmp \
    php81-soap php81-sockets php81-sodium \
    php81-sqlite3 php81-sysvmsg php81-sysvsem \
    php81-sysvshm php81-tidy php81-tokenizer \
    php81-xml php81-xmlreader php81-xmlwriter \
    php81-xsl php81-zip php81-zlib php81-pecl-memcached \
    busybox-extras \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && npm i -g pnpm \
    && rm -rf /var/cache/apk/*

COPY html/composer.json html/composer.lock ./
RUN composer install -o 

COPY system/ /
COPY html .
COPY check /
COPY script /
COPY migrate /

RUN chmod +x /usr/bin/skiddph
RUN pnpm install --prefix client/cdn \
    && pnpm install --prefix client/admin \
    && pnpm install --prefix client/main \
    && cd /app/client/admin && pnpm build \
    && cd /app/client/main && pnpm build \
    && cd /app/client/cdn && pnpm build

# Production only - Delete FEs source code
RUN find /app/client -mindepth 2 -maxdepth 2 -not -name 'dist'  -exec rm -rf {} \;
RUN find /app/client -mindepth 1 -maxdepth 1 -not -name 'admin' -not -name 'cdn' -not -name 'main' -exec rm -rf {} \;

EXPOSE 80
ENTRYPOINT ["/usr/bin/skiddph"]
