FROM php:8.2-fpm

# System-Abhängigkeiten installieren
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    default-mysql-client

# Cache leeren
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# PHP-Erweiterungen installieren
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

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
