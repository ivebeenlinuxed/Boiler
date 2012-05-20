<?php
namespace Controller;

class Framework {
	function index() {
		echo "TEST";
	}
	
	function phpunit() {
		if (\Core\Router::$settings['test']['enabled'] == 1) {
			include BOILER_LOCATION."../build/Selenium2PHPUnit/phpunit_coverage.php";
		}
	}
}