FROM php:8.2-apache

# Enable Apache rewrite
RUN a2enmod rewrite

# Install system packages
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libpq-dev \
    && docker-php-ext-install \
    mysqli \
    pdo \
    pdo_pgsql \
    pgsql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy Composer files first
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy the rest of the project
COPY . .

# Regenerate autoload (optional but recommended)
RUN composer dump-autoload --optimize

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80