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
	
	public function docs() {
		if (!isset(\Core\Router::$settings['enable_docs']) && \Core\Router::$settings['enable_docs'] != true) {
			return;
		}
		$file = implode("/", func_get_args());
		if (strpos($file, "..") !== false) {
			return;
		}
		if (is_file($f = BOILER_LOCATION."../build/logs/docs/doxygen/html/".$file)) {
			if (substr($file, -4) == ".css") {
				header("Content-Type: text/css");
			} elseif (substr($file, -3) == ".js") {
				header("Content-Type: text/javascript");
			} elseif (substr($file, -4) == ".png") {
				header("Content-Type: image/png");
			}
			include $f;
		} elseif (is_file($f = BOILER_LOCATION."../build/logs/docs/doxygen/html/".$file."/index.html")) {
			include $f;
		} else {
			"BAD";
		}
	}
}