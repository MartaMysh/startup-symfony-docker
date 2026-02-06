FROM php:8.2-fpm

# system deps
RUN apt-get update && apt-get install -y \
    git zip unzip libpq-dev libzip-dev libicu-dev libonig-dev \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    && rm -rf /var/lib/apt/lists/*

# install GD (wymagane przez PhpSpreadsheet)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# install extensions
RUN docker-php-ext-install pdo pdo_pgsql intl opcache zip

# composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# create www dir
WORKDIR /var/www/html

# optional user
RUN useradd -G www-data,root -u 1000 -m symfony

EXPOSE 9000

CMD ["php-fpm"]
