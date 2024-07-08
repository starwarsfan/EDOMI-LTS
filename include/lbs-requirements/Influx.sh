#!/usr/bin/env bash
# ============================================================================
#
# Created 2024-07-08 by StarWarsFan
#
# ============================================================================

# Influx Data Archives 19002576
mkdir -p /usr/local/edomi/www/admin/include/php/influx-client
cd /usr/local/edomi/www/admin/include/php/influx-client
composer require influxdata/influxdb-client-php
