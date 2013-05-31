#!/bin/sh
IYTE_DIR=/iyte/run/dhcpd/
DHCPD_DIR=/var/lib/dhcpd/
ETC_DIR=/etc/

cd $IYTE_DIR
RUNNING=$(cat lease.pid)
[ $RUNNING -eq "1" ] && exit 0

echo "1" > lease.pid
rm -f dhcpd.leases
rm -f dhcpd.conf

cd $ETC_DIR
cp -f dhcpd.conf $IYTE_DIR

cd $DHCPD_DIR
cp -f dhcpd.leases $IYTE_DIR

cd $IYTE_DIR
php dhcp-lease-poll.php
echo 0 > lease.pid

