FROM php:8.2-apache

# Mettre à jour les paquets et installer les extensions nécessaires
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && php -m | grep pdo_mysql