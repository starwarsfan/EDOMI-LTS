#!/usr/bin/env bash
# ============================================================================
#
# Created 2024-07-08 by StarWarsFan
#
# ============================================================================

# Alexa
ln -s /etc/pki/ca-trust/extracted/pem/tls-ca-bundle.pem /etc/pki/tls/cacert.pem
sed -i \
    -e '/\[curl\]/ a curl.cainfo = /etc/pki/ca-trust/extracted/pem/tls-ca-bundle.pem' \
    -e '/\[openssl\] a openssl.cafile = /etc/pki/ca-trust/extracted/pem/tls-ca-bundle.pem' \
    /etc/php.ini

# Alexa Control 19000809
cd /etc/ssl/certs
wget https://curl.haxx.se/ca/cacert.pem -O /etc/ssl/certs/cacert-Mozilla.pem
echo "curl.cainfo=/etc/ssl/certs/cacert-Mozilla.pem" >> /etc/php.d/curl.ini
