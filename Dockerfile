# Image pour le déploiement (fly.io)
FROM php:8.3-apache

# extensions PHP : PDO MySQL + MongoDB
RUN apt-get update && apt-get install -y libssl-dev unzip git \
    && docker-php-ext-install pdo pdo_mysql \
    && pecl install mongodb-1.21.13 \
    && docker-php-ext-enable mongodb \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && chown -R www-data:www-data images

# fly.io utilise le port 8080
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && a2enmod rewrite

EXPOSE 8080
