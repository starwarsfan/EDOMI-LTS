#!/usr/bin/env bash
# ============================================================================
#
# Created 2024-07-08 by StarWarsFan
#
# ============================================================================

# MikroTik RouterOS API 19001059
cd /usr/local/edomi/main/include/php
git clone https://github.com/jonofe/Net_RouterOS
cd Net_RouterOS
composer install --no-interaction
