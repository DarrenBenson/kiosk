FROM php:8.2-apache

# Enable required PHP extensions
RUN docker-php-ext-install opcache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www

# Copy application structure
COPY --chown=www-data:www-data public/ /var/www/html/
COPY --chown=www-data:www-data config/config.php /var/www/config/
COPY --chown=www-data:www-data config/config.example.php /var/www/config/

# Create cache directory
RUN mkdir -p /var/www/cache && chown www-data:www-data /var/www/cache

# Expose port 80
EXPOSE 80
