#!/usr/bin/env bash
# ============================================================================
#
# Created 2024-07-08 by StarWarsFan
#
# ============================================================================

# For Telegram-LBS 19000303 / 19000304
cd /usr/local/edomi/main/include/php
git clone https://github.com/php-telegram-bot/core
mv core php-telegram-bot
cd php-telegram-bot
composer install --no-interaction
