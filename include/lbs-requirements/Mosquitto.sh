#!/usr/bin/env bash
# ============================================================================
#
# Created 2024-07-08 by StarWarsFan
#
# ============================================================================

# For Mosquitto-LBS
mkdir -p /usr/lib64/php/modules/

# Disabled for now, needs some binaries
#cp ${ownLocation}/php-modules${ARCH_SUFFIX}/mosquitto.so /usr/lib64/php/modules/
#cp ${ownLocation}/mariadb-plugins${ARCH_SUFFIX}/*        /usr/lib64/mariadb/plugin/
#chmod +x /usr/lib64/php/modules/mosquitto.so /usr/lib64/mariadb/plugin/lib_mysqludf_*
#
#echo 'extension=mosquitto.so' > /etc/php.d/50-mosquitto.ini
