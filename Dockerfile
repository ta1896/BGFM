FROM php:8.4-fpm

# System-Abhängigkeiten installieren
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    default-mysql-client \
    libfcgi-bin

# Cache leeren
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# PHP-Erweiterungen installieren
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

# PHP-FPM Healthcheck Script installieren
RUN curl -o /usr/local/bin/php-fpm-healthcheck https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
    && chmod +x /usr/local/bin/php-fpm-healthcheck

# OPcache für maximale Performance in Production konfigurieren
RUN echo "opcache.enable=1\n\
opcache.enable_cli=1\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=8\n\
opcache.max_accelerated_files=10000\n\
opcache.revalidate_freq=0\n\
opcache.validate_timestamps=0\n\
opcache.save_comments=1\n\
opcache.fast_shutdown=1" > /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Composer installieren
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Arbeitsverzeichnis setzen
WORKDIR /var/www

# Bestehenden Anwendungs-Code kopieren
COPY . .

# Berechtigungen für Laravel setzen
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Port freigeben
EXPOSE 9000
CMD ["php-fpm"]
