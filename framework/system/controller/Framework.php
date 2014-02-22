<?php
namespace Controller;

class Framework {
	function index() {
	}
	
	function phpinfo() {
		phpinfo();
	}

	function phpunit() {
		if (\Core\Router::$settings['test']['enabled'] == 1) {
			include BOILER_LOCATION."../build/phpunit/Bindings/phpunit_coverage.php";
		}
	}

	public function docs() {
		if (!isset(\Core\Router::$settings['enable_docs']) || \Core\Router::$settings['enable_docs'] != true) {
			die("DOCS DISABLED");
			return;
		}
		$file = implode("/", func_get_args());
		if (strpos($file, "..") !== false) {
			return;
		}
		//var_dump(BOILER_LOCATION."../build/logs/docs/doxygen/html/".$orig_file."/index.html");
		$orig_file = $file;
		if (\Core\Router::$mode == \Core\Router::MODE_CSS) {
			header("Content-Type: text/css");
			$file .= ".css";
		} elseif (\Core\Router::$mode == \Core\Router::MODE_JS) {
			header("Content-Type: text/javascript");
			$file .= ".js";
		} elseif (\Core\Router::$mode == \Core\Router::MODE_PNG) {
			header("Content-Type: image/png");
			$file .= ".png";
		} elseif (\Core\Router::$mode == \Core\Router::MODE_HTML) {
			$file .= ".html";
		}
		if (is_file($f = BOILER_LOCATION."../build/logs/docs/doxygen/html/".$file)) {
			include $f;
		} elseif (is_file($f = BOILER_LOCATION."../build/logs/docs/doxygen/html/".$orig_file."/index.html")) {
			include $f;
		} else {
			"BAD";
		}
	}
}