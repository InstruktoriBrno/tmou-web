#!/bin/bash
set -e

# Wait ~ 60 seconds for database to be available
echo "Waiting until database at db:3306 becomes available..."
RETRIES=30
until mysqladmin ping -h ${MYSQL_SERVER} --user ${MYSQL_USER} --password=${MYSQL_PASSWORD} --port ${MYSQL_PORT} --silent || [ $RETRIES -eq 0 ]; do
    echo "$((RETRIES--)) remaining attempts..."
    sleep 2
done

# Pass to original entry point
echo "Pass through to docker php entrypoint..."
docker-php-entrypoint apache2-foreground
