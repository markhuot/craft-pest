FROM mcr.microsoft.com/vscode/devcontainers/php:0-8

RUN apt-get update && apt-get install -y mariadb-client libzip-dev libpng-dev
RUN su vscode -c "umask 0002 && . /usr/local/share/nvm/nvm.sh && nvm install 16 2>&1"
RUN docker-php-ext-install pdo pdo_mysql zip gd