# ---------------------------------------
# Laravel 12 CI image (PHP 8.3 + Composer)
# ---------------------------------------
FROM php:8.3-cli-alpine AS base

# System tools & headers cho build extensions
RUN apk add --no-cache \
  bash git unzip zip curl \
  icu-dev libzip-dev oniguruma-dev \
  libpng-dev freetype-dev libjpeg-turbo-dev \
  postgresql-client postgresql-dev \
  mariadb-client \
  autoconf make g++ 

# PHP extensions thường dùng cho Laravel
# - gd (image) với freetype + jpeg
# - intl (string/locale)
# - bcmath (calc)
# - pcntl (parallel test)
# - pdo_mysql / pdo_pgsql (DB)
# - zip (archive)
# - opcache (tắt trong CI, nhưng cần ext)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" \
     bcmath intl pcntl pdo pdo_mysql pdo_pgsql gd zip opcache

# PECL (tùy chọn)
RUN pecl install redis \
  && docker-php-ext-enable redis

# Xdebug optional (bật khi cần coverage sâu)
ARG INSTALL_XDEBUG=false
RUN if [ "$INSTALL_XDEBUG" = "true" ]; then \
      pecl install xdebug && docker-php-ext-enable xdebug ; \
    fi

# Composer (từ official image, nhanh & chuẩn)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# PHP ini cơ bản cho CI
RUN { \
      echo "memory_limit=1024M"; \
      echo "zend.assertions=1"; \
      echo "error_reporting=E_ALL"; \
      echo "display_errors=On"; \
      echo "opcache.enable=0"; \ 
      echo "date.timezone=UTC"; \
    } > /usr/local/etc/php/conf.d/ci.ini

# Mặc định làm việc ở /app
WORKDIR /app

# Health tools note:
# - pg_isready có sẵn qua postgresql-client
# - mysqladmin ping có sẵn qua mariadb-client

CMD ["php", "-v"]