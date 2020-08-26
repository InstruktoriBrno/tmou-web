#!/usr/bin/env bash

set -eu

./vendor/bin/phpstan.phar analyse --level 7 App Tests
./vendor/bin/phpcs --standard=ruleset.xml App/ Tests/

