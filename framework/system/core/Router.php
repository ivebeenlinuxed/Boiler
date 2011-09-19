<?php
namespace System\Core;

abstract class Router {
	protected static $defaultController = "Home";
	protected static $defaultFunction = "index";
	
	protected static $fofHandler = array("Controller\\ErrorDocument", "404");
	
	public static function getController($controllerArray) {
		var_dump($controllerArray);
		if (count($controllerArray) == 1 && $controllerArray[0] == "") {
			return array("Controller\\".self::$defaultController, self::$defaultFunction);
		}
		for ($i=1; $i<=count($controllerArray); $i++) {
			if (class_exists($c = "Controller\\".implode("\\", array_slice($controllerArray, 0, $i)), true)) {
				$cOK = $c;
				$cOffset = $i;
				if (is_callable(array($c, $f = $controllerArray[$i+1]))) {
					return array($c, $f, "Controller\\".implode("\\", array_slice($controllerArray, $i+1)));
				}
			}
		}
		if (isset($cOK) && is_callable(array($cOK, self::$defaultFunction))) {
			return array($cOK, self::$defaultFunction, array_slice($controllerArray, $cOffset));
		}
		return self::$fofHandler;
	}
}