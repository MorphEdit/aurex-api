FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip

# Enable Apache mod_rewrite (required for .htaccess routing)
RUN a2enmod rewrite

# Copy custom Apache config (DocumentRoot → /public)
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first (layer cache — only re-installs when deps change)
COPY composer.json composer.json

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy application files
COPY . .

# Ensure log/storage dirs exist and are writable
RUN mkdir -p logs storage/rate_limits \
    && chown -R www-data:www-data logs storage \
    && chmod -R 775 logs storage

EXPOSE 80
