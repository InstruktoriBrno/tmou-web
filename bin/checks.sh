#!/usr/bin/env bash

set -eu

composer install --no-interaction

./vendor/bin/phpstan analyse --level 7 App Tests
./vendor/bin/phpcs --standard=ruleset.xml App/ Tests/

