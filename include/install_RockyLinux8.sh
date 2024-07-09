#!/usr/bin/env bash
# ============================================================================
#
# Created 2024-07-08 by StarWarsFan
#
# Helper script to install Edomi on RockyLinux 8
#
# ============================================================================

# Store path from where script was called,
# determine own location and cd there
callDir=$(pwd)
ownLocation="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd ${ownLocation}
. ./helpers_console.sh
_init

# Determine architecture
if [ $(uname -m) = 'aarch64' ]; then
    ARCH_SUFFIX="-aarch64"
fi

info "Installing required packages"
# Install what we need ;-)
dnf module enable -y \
    php:7.4
dnf install -y \
    epel-release
dnf install -y \
    http://rpms.remirepo.net/enterprise/remi-release-8.rpm
dnf update -y
dnf upgrade -y
dnf clean all

dnf install -y \
    ca-certificates \
    chrony \
    dos2unix \
    expect \
    file \
    git \
    hostname \
    httpd \
    mariadb-server \
    mc \
    mod_ssl \
    mosquitto \
    mosquitto-devel \
    nano \
    net-snmp-utils \
    net-tools \
    nss \
    oathtool \
    openssh-server \
    openssl \
    passwd \
    php \
    php-curl \
    php-gd \
    php-json \
    php-mbstring \
    php-mysqlnd \
    php-process \
    php-snmp \
    php-soap \
    php-xml \
    php-zip \
    php74-php-pecl-mosquitto."$(uname -i)" \
    python2 \
    rsync \
    sudo \
    tar \
    unzip \
    vsftpd \
    wget \
    dnf-utils
dnf clean all
rm -f /etc/vsftpd/ftpusers \
    /etc/vsftpd/user_list
info " -> Done"

info "Installing LBS requirements"
for currentInclude in ${ownLocation}/lbs-requirements/*; do
    info " -> Loading/executing include ${currentInclude}"
    . ${currentInclude}
done
info " -> Done"

info "Enable services"
systemctl enable chronyd
systemctl enable vsftpd
systemctl enable httpd
systemctl enable php-fpm
systemctl enable mariadb
systemctl enable sshd
systemctl start sshd
info " -> Done"

info "Configuring system"
sed -e "s/listen=.*$/listen=YES/g" \
    -e "s/listen_ipv6=.*$/listen_ipv6=NO/g" \
    -e "s/userlist_enable=.*/userlist_enable=NO/g" \
    -i /etc/vsftpd/vsftpd.conf
localectl set-locale LANG=de_DE.utf8
localectl set-x11-keymap de
localectl set-keymap de-nodeadkeys
timedatectl set-timezone Europe/Berlin

# Configure mariadb
cat <<EOF >/tmp/tmp.txt
key_buffer_size=256M
sort_buffer_size=8M
read_buffer_size=16M
read_rnd_buffer_size=4M
myisam_sort_buffer_size=4M
join_buffer_size=4M
query_cache_limit=8M
query_cache_size=8M
query_cache_type=1
wait_timeout=28800
interactive_timeout=28800
EOF

# STRICT_TRANS_TABLES (strict mode) needs to be disabled, otherwise statements
# like this will fail because of the empty values:
# INSERT INTO edomiProject.editLogicCmdList (targetid,cmd,cmdid1,cmdid2,cmdoption1,cmdoption2,cmdvalue1,cmdvalue2) \
#                                    VALUES ('2',    '1', '101', '',    '',        '',        null,     null)
echo "sql_mode=ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION" >>/tmp/tmp.txt
sed -i '/\[mysqld\]/r /tmp/tmp.txt' /etc/my.cnf.d/mariadb-server.cnf
info " -> Done"

info "Configuring FTP"
rm -f /etc/vsftpd/ftpusers
rm -f /etc/vsftpd/user_list
sed -i -e '/listen=/ s/=.*/=YES/' /etc/vsftpd/vsftpd.conf
sed -i -e '/listen_ipv6=/ s/=.*/=NO/' /etc/vsftpd/vsftpd.conf
sed -i -e '/userlist_enable=/ s/=.*/=NO/' /etc/vsftpd/vsftpd.conf
info " -> Done"

info "Configuring Apache webserver"
sed -i -e "s/#ServerName www\.example\.com/ServerName $SERVERIP/" /etc/httpd/conf/httpd.conf
sed -i -e "s#DocumentRoot \"/var/www/html\"#DocumentRoot \"$MAIN_PATH/www\"#" /etc/httpd/conf/httpd.conf
sed -i -e "s#<Directory \"/var/www\">#<Directory \"$MAIN_PATH/www\">#" /etc/httpd/conf/httpd.conf
sed -i -e "s#<Directory \"/var/www/html\">#<Directory \"$MAIN_PATH/www\">#" /etc/httpd/conf/httpd.conf
info " -> Done"

info "Configuring MariaDB"
systemctl start mariadb
/usr/bin/mysqladmin -u root password ""
mysql -e "DROP DATABASE test;"
mysql -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%'"
mysql -e "FLUSH PRIVILEGES;"
mysql -e "GRANT ALL ON *.* TO mysql@'%';"

# MySQL symlink
echo "Alias=mysqld.service" >/tmp/tmp.txt
sed -i '/\[Install\]/r /tmp/tmp.txt' /usr/lib/systemd/system/mariadb.service
ln -s '/usr/lib/systemd/system/mariadb.service' '/etc/systemd/system/mysqld.service'
systemctl daemon-reload
info " -> Done"

info "Configuring PHP"
sed -i -e '/short_open_tag =/ s/=.*/= On/' /etc/php.ini
sed -i -e '/post_max_size =/ s/=.*/= 100M/' /etc/php.ini
sed -i -e '/upload_max_filesize =/ s/=.*/= 100M/' /etc/php.ini
sed -i -e '/max_file_uploads =/ s/=.*/= 1000/' /etc/php.ini
info " -> Done"

info "Installing Edomi"
systemctl stop mariadb
cp -rf ${ownLocation}/../main /usr/local/edomi/
cp -rf ${ownLocation}/../www /usr/local/edomi/
cp -f ${ownLocation}/../edomi.ini /usr/local/edomi/
cp -f ${ownLocation}/../LICENSE /usr/local/edomi/
cp -f ${ownLocation}/../logicmonitor.ini /usr/local/edomi/

cp ${ownLocation}/scripts/edomi.service /etc/systemd/system/
systemctl enable edomi.service
info " -> Done"

info "Installing MySQL UDF log/sys"
# Enable lib_mysqludf_sys
systemctl start mariadb
mysql -u root mysql <${ownLocation}/scripts/installdb_mysqludf_log.sql
mysql -u root mysql <${ownLocation}/scripts/installdb_mysqludf_sys.sql
info " -> Done"

info "Tweak some default settings"
sed -i \
    -e 's#global_serverConsoleInterval=.*#global_serverConsoleInterval=false#' \
    -e "s#global_serverIP=.*#global_serverIP=''#" \
    -e "s/62\.75\.208\.51/edomi\.de/g" \
    /usr/local/edomi/edomi.ini
info " -> Done"
