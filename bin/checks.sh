#!/usr/bin/env bash

set -eu

php -d memory_limit=512M ./vendor/bin/phpstan.phar analyse --level 7 App Tests
./vendor/bin/phpcs --standard=ruleset.xml App/ Tests/

