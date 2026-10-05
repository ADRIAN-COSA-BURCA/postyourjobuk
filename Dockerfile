FROM php:8.2-apache

# 1. Install system dependencies including those required for Azure SDK (cURL, XML, Mbstring, Zip)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    libonig-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# 2. Install required PHP extensions (MySQL, Zip, cURL, mbstring, xml)
RUN docker-php-ext-install mysqli pdo pdo_mysql zip curl mbstring xml

# 3. Install Composer directly via curl
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# 4. Enable Apache rewrite module
RUN a2enmod rewrite

# 5. Configure Apache DocumentRoot to point to your 'public' folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 6. Allow .htaccess overrides globally
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# 7. Set working directory to your project root
WORKDIR /var/www/html

# 8. Copy all application files into the container
COPY . /var/www/html/