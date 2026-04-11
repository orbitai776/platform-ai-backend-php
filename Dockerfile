# --- GIAI ĐOẠN 1: BUILDER (Nơi nấu nướng cực nặng) ---
FROM php:8.2-cli-alpine AS builder

# Cài bộ đồ nghề thợ xây
RUN apk add --no-cache $PHPIZE_DEPS pcre-dev libpq-dev libxml2-dev oniguruma-dev
RUN docker-php-ext-install pdo pdo_pgsql
# Nấu Redis và các extension khác
RUN docker-php-ext-install pdo pdo_pgsql mbstring xml bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis

# Cài Composer và dọn dẹp thư viện Dev
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-scripts --ignore-platform-reqs

# --- GIAI ĐOẠN 2: RUNTIME (Chỉ lấy món ăn, bỏ lại bếp núc) ---
FROM php:8.2-cli-alpine

# Chỉ cài những thư viện hệ thống tối thiểu để chạy (Không có bộ thợ xây)
RUN apk add --no-cache libpq-dev oniguruma-dev libxml2-dev

# COPY "Thành phẩm" từ stage builder sang (Đây là bí kíp để nhẹ!)
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d/docker-php-ext-redis.ini /usr/local/etc/php/conf.d/
COPY --from=builder /usr/local/etc/php/conf.d/docker-php-ext-pdo_pgsql.ini /usr/local/etc/php/conf.d/
COPY --from=builder /app /app

WORKDIR /app

# Dọn dẹp cache và cấp quyền (Lần này sẽ cực nhanh)
RUN rm -f bootstrap/cache/*.php && \
    chmod -R 775 storage bootstrap/cache

EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "/app/public"]