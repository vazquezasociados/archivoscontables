FROM phpdockerio/php:8.2-fpm
WORKDIR "/application"

# Instalar dependencias del sistema
RUN apt-get update \
    && apt-get -y --no-install-recommends install \
        git \
        locales \
        php8.2-mysql \
        php8.2-gd \
        php8.2-intl \
        php8.2-curl \
        php8.2-mbstring \
        php8.2-xml \
        php8.2-zip \
        curl \
        ca-certificates \
        gnupg2 \
        xz-utils \
    && locale-gen en_US.UTF-8 \
    && locale-gen es_AR.UTF-8 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Configurar locale
ENV LANG=es_AR.UTF-8 \
    LANGUAGE=es_AR:es \
    LC_ALL=es_AR.UTF-8

# Habilitar extensiones PHP explícitamente
RUN phpenmod intl

# Descarga y descomprime el archivo binario de Node.js
RUN curl -o node.tar.xz https://nodejs.org/dist/v20.12.2/node-v20.12.2-linux-x64.tar.xz \
    && tar -xf node.tar.xz --strip-components=1 -C /usr/local \
    && rm node.tar.xz

# Instalar Yarn
RUN apt-get update && apt-get install -y curl gnupg2 && \
    curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list && \
    apt-get update && apt-get install -y yarn

# Verificar que intl está habilitada
RUN php -m | grep intl
