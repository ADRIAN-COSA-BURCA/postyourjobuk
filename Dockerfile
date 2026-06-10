FROM php:8.2-apache

# 1. Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql zip

# 2. Install Composer from the official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Enable Apache rewrite module
RUN a2enmod rewrite

# 4. Overwrite the default virtual host file with a clean, hardcoded DocumentRoot
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# 5. Ensure the global directory permissions allow overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf