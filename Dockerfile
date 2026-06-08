FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql zip

# Install Composer from the official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache rewrite module
RUN a2enmod rewrite