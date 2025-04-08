FROM php:8.2-cli

# Install dependencies
RUN apt-get update && apt-get install -y unzip zip git

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy your app
WORKDIR /app
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose the port PHP server will run on
EXPOSE 10000

# Start PHP server
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
