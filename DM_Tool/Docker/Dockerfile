FROM php:apache

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install cron
RUN apt-get update && apt-get -y install cron && apt-get install nano

RUN echo "* * * * * root php /home/app/src/Commands/Core.php EmailTask.produce --message=hello" >> /etc/crontab

# Install Supervisor
RUN apt-get update && apt-get install -y supervisor

RUN mkdir -p /var/log/supervisor

#Adding consumer configuration process to supervisor
COPY consumer.conf /etc/supervisor/conf.d/consumer.conf

#Entry point commands
COPY startup-commands.sh /home/startup-commands.sh

RUN chmod +x /home/startup-commands.sh

ENTRYPOINT ["/home/startup-commands.sh"]
