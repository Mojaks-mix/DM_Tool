#!/bin/bash

#starting cron job "producer" for testing the producer
service cron start

#starting supervisor 
/usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
