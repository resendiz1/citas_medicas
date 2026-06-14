# FROM php:8.3-cli

# WORKDIR /var/www/html

# RUN apt-get update && apt-get install -y \
#     git unzip curl \
#     libzip-dev \
#     libpng-dev \
#     libonig-dev \
#     libxml2-dev \
#     libcurl4-openssl-dev \
#     libfreetype6-dev \
#     libjpeg62-turbo-dev \
#     nodejs npm \
#     && docker-php-ext-configure gd --with-freetype --with-jpeg \
#     && docker-php-ext-install \
#         pdo \
#         pdo_mysql \
#         mbstring \
#         zip \
#         xml \
#         curl \
#         bcmath \
#         gd \
#         sockets \
#         pcntl

# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# COPY . .

# RUN COMPOSER_MEMORY_LIMIT=-1 composer install \
#     --no-interaction \
#     --prefer-dist \
#     --optimize-autoloader \
#     --no-dev

# RUN npm install && npm run build

# RUN chmod -R 775 storage bootstrap/cache

# CMD php artisan serve --host=0.0.0.0 --port=${PORT}





FROM php:8.3-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git unzip curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    nodejs npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        xml \
        curl \
        bcmath \
        gd \
        sockets \
        pcntl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN COMPOSER_MEMORY_LIMIT=-1 composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-dev

RUN npm install && npm run build

RUN chmod -R 775 storage bootstrap/cache

CMD php artisan serve --host=0.0.0.0 --port=${PORT}