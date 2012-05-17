DB=$1
USER=$2
PASSWD=$3
HOST=$4
PORT=$5

if [ -e "../config.php" ]; then
	exit;
fi;

echo -n "Server Name [$HOST]: ";
read NEW_HOST;
echo -n "Server Port [$PORT]: ";
read NEW_PORT;
echo -n "Server User [$USER]: ";
read NEW_USER;
echo -n "Server Password [$PASSWD]: ";
read NEW_PASSWD;
echo -n "Database Name [$DB]: ";
read NEW_DB;

if [ -n "$NEW_HOST" ]; then
	HOST=$NEW_HOST;
fi;

if [ -n "$NEW_PORT" ]; then
	PORT=$NEW_PORT;
fi;

if [ -n "$NEW_USER" ]; then
	USER=$NEW_USER;
fi;

if [ -n "$NEW_PASSWD" ]; then
	PASSWD=$NEW_PASSWD;
fi;

if [ -n "$NEW_DB" ]; then
	DB=$NEW_DB;
fi;

echo "<?php \$settings=array();" > ../config.php
echo "\$settings['database'] = array();" >> ../config.php
echo "\$settings['database']['user'] = '$USER';" >> ../config.php
echo "\$settings['database']['passwd'] = '$PASSWD';" >> ../config.php
echo "\$settings['database']['server'] = '$HOST';" >> ../config.php
echo "\$settings['database']['port'] = '$PORT';" >> ../config.php
echo "\$settings['database']['db'] = '$DB';" >> ../config.php

