# Use the official PHP 8.2 image
FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy the application files to the container
COPY . .

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql

# Run Composer to install dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Expose the port the app will run on
EXPOSE 80

# Command to run the PHP server
CMD ["php", "-S", "0.0.0.0:80"]
