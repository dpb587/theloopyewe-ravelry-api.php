#!/bin/bash

set -e
set -u

cd repository/

echo "{\"access_token\":\"$RAVELRY_TEST_ACCESS_TOKEN\",\"access_token_secret\":\"$RAVELRY_TEST_ACCESS_TOKEN_SECRET\"}" > .ravelryapi

curl https://getcomposer.org/composer.phar > composer.phar
php composer.phar config --global github-oauth.github.com "$COMPOSER_GITHUB_TOKEN"

php composer.phar install --prefer-dist
./vendor/bin/phpunit
