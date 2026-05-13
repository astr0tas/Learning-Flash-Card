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
    && docker-php-ext-install pdo pdo_pgsql opcache \
    && pecl install apcu \
    && docker-php-ext-enable apcu

# 2. Inject optimal OPcache settings for DEVELOPMENT
RUN echo "opcache.enable=1\n\
opcache.enable_cli=1\n\
opcache.memory_consumption=1024\n\
opcache.max_accelerated_files=20000\n\
opcache.validate_timestamps=1\n\
opcache.revalidate_freq=0" > /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# 3. Inject optimal APCu settings for DEVELOPMENT
RUN echo "apc.enable_cli=1\n\
apc.shm_size=512M" > /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini

# 4. Install the Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt-get install -y symfony-cli

# 5. Install Composer (Required to install Symfony packages)
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer