#!/usr/bin/env bash
# ============================================================================
#
# Created 2024-07-08 by StarWarsFan
#
# ============================================================================

# Get composer
cd /tmp
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === file_get_contents('https://composer.github.io/installer.sig')) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
mv composer.phar /usr/local/bin/composer
mkdir -p /usr/local/edomi/main/include/php
