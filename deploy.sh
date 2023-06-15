#!/usr/bin/env bash
# Deployment script for polo-automatic (manually triggered) deployment of production environment
# Run: $ cd <PROJECT ROOT> && bash deploy.sh production
# Run: $ cd <PROJECT ROOT> && bash deploy.sh production no-pull

# Any subsequent(*) commands which fail will cause the shell script to exit immediately
set -e

# Get deployment mode and validate
MODE=${1}
NO_PULL=${2}

if [ -z "$MODE" ]; then
    echo "Environment is not specified. One of: production expected.";
    exit 1;
fi;


if [ "$MODE" != "production" ]; then
    echo "${MODE} is not valid environment";
    exit 1;
fi;

# Check if in project root
if [ ! -d ".git" ]; then
    echo "Not in project root!";
    exit 1;
fi

# Enter deployment mode
touch "UNDER_MAINTENANCE";

# Get root dir
rootdir=`pwd`
logdir=`pwd`/log

if [ "$NO_PULL" == "no-pull" ]; then
  echo "Skipping pulling new changes from current branch.";
else
  echo "Performing pulling new changes from current branch...";
  DEPLOY_HASH=`shasum deploy.sh`
  git pull
  DEPLOY_NEW_HASH=`shasum deploy.sh`
fi;

if [ "$DEPLOY_HASH" != "$DEPLOY_NEW_HASH" ]; then
    echo "Deploy.sh has changed in current upstream. Please restart the migration again with new migration script.";
    exit 2;
fi;

# Clean caches etc.
rm -rf temp/cache/*
rm -rf temp/proxies/*

# Install composer as aliases do not work in non-interactive scripts
if [ ! -f "composer.phar" ]; then
  echo "Installing composer...";
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
  php composer-setup.php
  php -r "unlink('composer-setup.php');"
fi;

# Prepare assets (but only when the necessary build utils are present)
echo "Installing dependencies";
which npm > /dev/null && npm install

if [ "$MODE" == "production" ]; then
    php composer.phar install --no-dev --optimize-autoloader --no-progress --no-interaction --no-suggest
fi;

# Migrate database
echo "Migrating database";
if [ "$MODE" == "production" ]; then
    php bin/console migrations:migrate
    rm -rf temp/cache/* # due to user mismatch
    rm -rf temp/proxies/* # due to user mismatch
fi;

echo "# Configure CRON"
if [ "$MODE" == "production" ]; then
    echo "CHECK MANUALLY!"
fi;

rm "UNDER_MAINTENANCE";
echo "Deploy completed!"
