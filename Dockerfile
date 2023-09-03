# Use an official PHP runtime as a parent image
FROM php:8.2-fpm

# Set the working directory to /app
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the local Laravel code to the container
COPY . .

# Install Laravel dependencies
RUN composer install

# Expose port 8000 and start PHP-FPM
EXPOSE 8000

CMD ["php-fpm"]