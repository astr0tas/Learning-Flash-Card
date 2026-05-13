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
    && docker-php-ext-install pdo pdo_pgsql

# 3. Install the Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt-get install -y symfony-cli

# 4. Install Composer (Required to install Symfony packages)
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer