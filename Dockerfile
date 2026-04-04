# 1. Dùng PHP 8.2 CLI Alpine
FROM php:8.2-cli-alpine

# 2. Cài đặt các thư viện hệ thống cần thiết (Dành cho mbstring, xml, pgsql, curl)
RUN apk add --no-cache \
    curl \
    libpq-dev \
    libxml2-dev \
    oniguruma-dev \
    libpng-dev \
    zip \
    unzip

# 3. Cài đặt các Extension PHP mà Laravel & Firebase bắt buộc phải có
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    xml \
    bcmath \
    ctype \
    fileinfo

# 4. Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Thiết lập thư mục làm việc
WORKDIR /app
COPY . /app

# 6. Tăng giới hạn bộ nhớ cho Composer (Đề phòng máy ảo Docker bị yếu)
ENV COMPOSER_MEMORY_LIMIT=-1

# 7. CHẠY CÀI ĐẶT (Đã thêm các cờ để bỏ qua kiểm tra môi trường lúc build)
RUN composer install --optimize-autoloader --no-scripts --ignore-platform-reqs
RUN php artisan config:clear && php artisan route:clear
# 8. Cấp quyền cho Laravel
RUN chmod -R 777 /app/storage /app/bootstrap/cache

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "/app/public"]