# Use an official PHP runtime as a parent image
FROM php:8.4-cli

# Install dependencies for Symfony and Composer
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    && curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy the current directory contents into the container
COPY . .

# Install PHP extensions and Composer dependencies
RUN apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo pdo_mysql

# Run Composer to install dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Expose the port the app will run on
EXPOSE 8080

# Start the PHP server
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
