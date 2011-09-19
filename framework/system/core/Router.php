<?php
namespace System\Core;
/**
 * 
 * Routes new requests to correct controller
 * @author ivebeenlinuxed
 *
 */
abstract class Router {
	/**
	 * 
	 * Default controller for home page
	 * @var string
	 */
	protected static $defaultController = "Home";
	
	/**
	 * 
	 * If no callable function can be found in path, call this one
	 * @var string
	 */
	protected static $defaultFunction = "index";
	
	/**
	 * 
	 * Default 404 Error Handler
	 * @var array
	 */
	protected static $fofHandler = array("Controller\\ErrorDocument", "fof");
	
	/**
	 * 
	 * Get correct controller, using the argument array
	 * @param array $controllerArray
	 */
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