# Use an official PHP image with necessary extensions
FROM php:8.4-fpm

# Set the user to root
USER root

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
# Copy application files
COPY . /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    nginx \
    supervisor \
    curl \
    rsync \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql pcntl zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN composer install --no-dev --no-interaction --prefer-dist \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R ugo+w /var/www/html/storage /var/www/html/bootstrap/cache \
    && echo "upload_max_filesize=2G" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=2G" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit=256M" > /usr/local/etc/php/conf.d/memory-limit.ini \
    && rm -f /etc/nginx/sites-available/default

COPY nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Set up Supervisor config
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
# Expose the necessary port
EXPOSE 80

# Start Supervisor to manage both Nginx and PHP-FPM
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
