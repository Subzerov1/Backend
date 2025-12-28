# استخدم صورة PHP مع Apache
FROM php:8.2-apache

# تثبيت الامتدادات اللازمة
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql mbstring zip

# نسخ المشروع
COPY . /var/www/html

# تعيين مجلد العمل
WORKDIR /var/www/html

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تثبيت الاعتمادات
RUN composer install --no-dev --optimize-autoloader

# نسخ إعدادات Apache
RUN a2enmod rewrite
