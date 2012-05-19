<?php
namespace Controller;

class Frameworklicence {
	function index() {
		echo "TEST";
	}
	
	function phpunit() {
		if (\Core\Router::$settings['test']['enabled'] == 1) {
			include BOILER_LOCATION."../build/selenium/phpunit_coverage.php";
		}
	}
}