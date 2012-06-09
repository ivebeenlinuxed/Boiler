<?php
require __DIR__."/../config.php";
echo exec("mysqldump -d -u {$settings['database']['user']} -p{$settings['database']['passwd']} -h {$settings['database']['server']} -P {$settings['database']['port']} {$settings['database']['db']} > '".__DIR__."/schema.sql'");
