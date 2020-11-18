FROM php:7.4-apache
COPY . /var/www/html
RUN DEBIAN_FRONTEND=noninteractive apt-get update && apt-get install -y wget curl
RUN DEBIAN_FRONTEND=noninteractive apt-get install -y \
    libpq-dev \
    cron \
    libxml2-dev \
    git \
    nano \
    python \
    locales \
    libmcrypt-dev \
    libzip-dev \
    && docker-php-ext-install -j$(nproc) pdo_mysql zip
RUN a2enmod rewrite && \
    service apache2 restart
ADD crons/crontab /etc/cron.d/root
RUN chmod 0644 /etc/cron.d/root
RUN crontab /etc/cron.d/root
RUN touch /var/log/cron.log
CMD ( cron -f -l 8 & ) && apache2-foreground
EXPOSE 80