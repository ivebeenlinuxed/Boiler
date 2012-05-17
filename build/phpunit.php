<?php
$_SERVER['no_run'] = true;
require __DIR__."/../htdocs/index.php";
require __DIR__."/phpwebdriver/WebDriver.php";
spl_autoload_register("autoload");
\Core\Router::Init();
