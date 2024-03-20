FROM php:apache

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install cron
RUN apt-get update && apt-get -y install cron

RUN echo "* * * * * root php /home/app/src/Commands/Core.php EmailTask.produce --message=hello" > /etc/cron.d/mycron

RUN chmod 600 /etc/cron.d/mycron

RUN crontab /etc/cron.d/mycron

# Install Supervisor
RUN apt-get update && apt-get install -y supervisor

RUN mkdir -p /var/log/supervisor

COPY consumer.conf /etc/supervisor/conf.d/consumer.conf

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]