FROM dunglas/frankenphp:1.4.4-php8.4.4-bookworm

RUN install-php-extensions \
	pdo_mysql \
	gd \
	intl \
	zip \
	opcache \
    pcntl

RUN apt update && apt install -y default-mysql-client

COPY . /app

ENTRYPOINT ["php", "artisan", "octane:frankenphp"]