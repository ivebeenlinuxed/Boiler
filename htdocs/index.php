<?php
define("BOILER_LOCATION", __DIR__."/../framework/");


function __autoload($load) {
	$e = explode("\\", $load);
	$class = array_pop($e);
	foreach ($e as $key=>$data)
		$e[$key] = strtolower($data);
	
	if ($e[0] == "system") {
		$e = array_slice($e, 1);
		if (file_exists($loc = BOILER_LOCATION."system/".implode("/", $e)."/$class.php")) {
			include $loc;
			return;
		}
	} else {
		if (file_exists($loc = BOILER_LOCATION."application/".implode("/", $e)."/$class.php")) {
			include $loc;
			return;
		}
		
		if (file_exists($loc = BOILER_LOCATION."system/".implode("/", $e)."/$class.php")) {
			include $loc;
			return;
		}
	}
	
}


if (isset($_SERVER['_']))
	$call = Core\Router::getController(array_slice($_SERVER['argv'], 1));
else
	$call = Core\Router::getController(explode("/", substr($_SERVER['REQUEST_URI'], 1)));