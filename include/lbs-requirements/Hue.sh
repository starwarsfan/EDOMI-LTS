#!/usr/bin/env bash
# ============================================================================
#
# Created 2024-07-08 by StarWarsFan
#
# ============================================================================

# Philips HUE Bridge 19000195
# As long as https://github.com/sqmk/Phue/pull/143 is not merged, fix phpunit via sed
cd /usr/local/edomi/main/include/php
git clone https://github.com/sqmk/Phue
cd Phue
sed -i "s/PHPUnit/phpunit/g" composer.json
composer install --no-interaction
