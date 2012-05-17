<?php
require "../config.php";
echo exec("mysql -u {$settings['database']['user']} -p{$settings['database']['passwd']} -h {$settings['database']['server']} -P {$settings['database']['port']} {$settings['database']['db']} < schema.sql");
