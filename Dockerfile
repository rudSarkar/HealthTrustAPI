# Use an official PHP runtime as a parent image
FROM php:8.2.8-fpm

# Set the working directory in the container
WORKDIR /var/www/html

# Install system dependencies for PHP and Python
RUN apt-get update && \
    apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    python3 \
    python3-pip \
    python3.11-venv \
    && rm -rf /var/lib/apt/lists/* 

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd pdo pdo_mysql

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the Laravel application files into the container
COPY . .

# Install Laravel application dependencies using Composer
RUN composer install

# Create a virtual environment for Python
RUN python3 -m venv /venv

# Activate the virtual environment and install Python dependencies
RUN /venv/bin/python -m pip install -r public/python_script/requirements.txt

RUN . /venv/bin/activate

RUN php artisan migrate
RUN artisan BangladeshGeocode:setup
RUN artisan db:seed --verbose
RUN artisan storage:link

# Expose the port where PHP-FPM will listen
EXPOSE 8000

# Start PHP-FPM
CMD [ "php", "artisan", "serve", "--host=0.0.0.0" ]