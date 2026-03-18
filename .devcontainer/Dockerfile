FROM mcr.microsoft.com/devcontainers/php:3-8.4-bookworm

# Install the PostgreSQL driver requirements and compile the PHP extension
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Install the Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | sudo -E bash \
    && apt-get install -y symfony-cli