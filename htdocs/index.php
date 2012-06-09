<?php
/**
 * 
 * 
 */

define("START_MEM", memory_get_usage());
define("BOILER_LOCATION", __DIR__."/../framework/");
define("BOILER_TMP", __DIR__."/../tmp/");
define("BOILER_HTDOCS", __DIR__);

/**
 * Automatically loads the needed classes in the rest of the framework
 * 
 * @param string $load Class which is being loaded
 * 
 * @return null
 */
function autoload($load) {
	$e = explode("\\", $load);
	$class = array_pop($e);
	foreach ($e as $key=>$data) {
		$e[$key] = strtolower($data);
	}
	if (count($e) > 0) {
		if ($e[0] == "system") {
			$e = array_slice($e, 1);
			if (file_exists($loc = BOILER_LOCATION."system/".implode("/", $e)."/$class.php")) {
				include_once $loc;
				return;
			}
		} else {
			if (file_exists($loc = BOILER_LOCATION."application/".implode("/", $e)."/$class.php")) {
				include_once $loc;
				return;
			}

			if (file_exists($loc = BOILER_LOCATION."system/".implode("/", $e)."/$class.php")) {
				include_once $loc;
				return;
			}
		}
	}
}


spl_autoload_register("autoload");
Core\Router::Init();

if (!isset($_SERVER['no_run'])) {
	if (isset($_SERVER['_']))
		$call = Core\Router::getController(array_slice($_SERVER['argv'], 1));
	else
		$req = $_SERVER['REQUEST_URI'];
	if (strpos($req, "?")) {
		$req = substr($req, 0, strpos($req, "?"));
	}
	$call = Core\Router::getController(explode("/", trim($req, "/")));

	$obj = new $call[0];
	call_user_func_array(array($obj, $call[1]), $call[2]);
}

