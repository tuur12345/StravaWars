# Use the official PHP image with Apache
FROM php:8.2-apache

# Enable required extensions
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    git \
    zip \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy all project files
COPY . .

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Set correct permissions (adjust as needed)
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/vendor

# Use Symfony's public directory as web root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Configure Apache to use public/ as root
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80
