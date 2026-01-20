FROM php:8.2-apache

# Enable required PHP extensions
RUN docker-php-ext-install opcache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY --chown=www-data:www-data . .

# Create cache directory
RUN mkdir -p cache && chown www-data:www-data cache

# Expose port 80
EXPOSE 80
