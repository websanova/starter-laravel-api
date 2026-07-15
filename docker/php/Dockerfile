FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    zip \
    bcmath \
    gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=mysql:8.0 /usr/bin/mysqldump /usr/bin/mysqldump

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN git config --global --add safe.directory /var/www/html

ARG USER_ID
ARG GROUP_ID
RUN groupadd -g ${GROUP_ID} app && useradd -u ${USER_ID} -g ${GROUP_ID} -m app

WORKDIR /var/www/html

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER app

CMD ["/usr/local/bin/entrypoint.sh"]
