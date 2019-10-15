#!/bin/bash

YESTERDAY=`php -r "echo date('Y-m-d', time() - 60 * 60 * 24);"`
CRON_KEY="bedRIvM9glqIuzr0s1RDnZ8rssIyDo"
wget "https://www.tmou.cz/cron/payments?apiKey=${CRON_KEY}&start=${YESTERDAY}&end=${YESTERDAY}"
