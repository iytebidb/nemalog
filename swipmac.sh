#!/bin/sh
IYTE_DIR=/iyte/run/swipmac/

cd $IYTE_DIR
RUNNING=$(cat swipmac.pid)
[ $RUNNING -eq "1" ] && exit 0

echo "1" > swipmac.pid
php switch-get-ip-mac-table.php
echo 0 > swipmac.pid

