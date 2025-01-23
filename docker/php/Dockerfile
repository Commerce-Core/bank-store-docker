FROM php:8.1-fpm

RUN apt-get update -y
RUN apt -y install gcc make cmake autoconf libc-dev pkg-config libzip-dev

RUN apt-get update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip less \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

RUN apt-get install -y --no-install-recommends \
    git \
    re2c libpng++-dev \
    libwebp-dev libjpeg-dev libjpeg62-turbo-dev libpng-dev libxpm-dev libvpx-dev libfreetype6-dev \
    libmagick++-dev libmagickwand-dev subversion openssh-client

RUN docker-php-ext-install bcmath mysqli exif gd

RUN pecl install imagick && docker-php-ext-enable imagick

# install xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer \
    && chmod ugo+x /usr/local/bin/composer \
    && echo "*** composer command installed"

# Install wp-cli
RUN curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar > /usr/local/bin/wp-cli \
    && chmod +x /usr/local/bin/wp-cli \
    && echo 'alias wp="wp-cli --allow-root"' >> ~/.bashrc


# install required libs for health check
RUN apt-get -y install libfcgi0ldbl nano htop iotop lsof cron mariadb-client redis-tools wget

# Health check
RUN echo '#!/bin/bash' > /healthcheck && \
    echo 'env -i SCRIPT_NAME=/health SCRIPT_FILENAME=/health REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1' >> /healthcheck && \
    chmod +x /healthcheck

RUN apt-get update \
    && apt -y install rsync

# PHPUnit install
RUN composer global config --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer false \
    && composer global require phpunit/phpunit=^9 --dev \
    && composer global require --dev yoast/phpunit-polyfills \
    && composer global require wp-coding-standards/wpcs --dev \
    && composer global require phpcompatibility/php-compatibility --dev \
    && composer global require phpcompatibility/phpcompatibility-paragonie --dev \
    && composer global require phpcompatibility/phpcompatibility-wp --dev

ENV PATH="${PATH}:/root/.composer/vendor/bin"

RUN phpcs --config-set installed_paths /root/.composer/vendor/wp-coding-standards/wpcs,/root/.composer/vendor/phpcompatibility/php-compatibility,/root/.composer/vendor/phpcompatibility/phpcompatibility-paragonie,/root/.composer/vendor/phpcompatibility/phpcompatibility-wp

ENV WP_TESTS_PHPUNIT_POLYFILLS_PATH="/root/.composer/vendor/yoast/phpunit-polyfills"

RUN wget https://phpdoc.org/phpDocumentor.phar \
    && mv phpDocumentor.phar /usr/local/bin/phpdoc \
    && chmod +x /usr/local/bin/phpdoc

RUN git clone https://github.com/mozilla/mozjpeg.git \
    && cd mozjpeg \
    && mkdir build && cd build \
    && cmake .. \
    && make \
    && make install

# Clean up
RUN apt-get remove -y git && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


WORKDIR /app/public
