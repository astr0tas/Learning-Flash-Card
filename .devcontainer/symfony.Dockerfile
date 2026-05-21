FROM php:8.4

# 1. Update and install dependencies in a single layer to optimize the build
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y \
        libpq-dev \
        curl \
        git \
        unzip \
        iputils-ping \
# 2. Compile PHP extensions
    && docker-php-ext-install -j 2 pdo pdo_pgsql

# 3. Install xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# 4. Create xdebug.ini file
RUN echo "zend_extension=xdebug.so\n\
xdebug.mode=debug\n\
xdebug.start_with_request=yes\n\
xdebug.discover_client_host=1\n\
xdebug.client_port=9003\n\
xdebug.client_host=127.0.0.1" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# 5. Install the Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt-get install -y symfony-cli

# 6. Install Composer (Required to install Symfony packages)
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer