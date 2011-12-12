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
	protected static $fofHandler = array("Controller\\ErrorDocument", "index");
	
	/**
	 * 
	 * Get correct controller, using the argument array
	 * @param array $controllerArray
	 */
	public static function getController($controllerArray) {
		if ((count($controllerArray) == 1 && $controllerArray[0] == "") || count($controllerArray) == 0 || is_array($controllerArray[0])) {
			return array("Controller\\".static::$defaultController, static::$defaultFunction, array());
		}
		for ($i=1; $i<=count($controllerArray) && !is_array($controllerArray[$i-1]); $i++) {
			$controllerArray[$i-1] = ucfirst($controllerArray[$i-1]);
			if (class_exists($c = "Controller\\".implode("\\", array_slice($controllerArray, 0, $i)), true)) {
				$cOK = $c;
				if (isset($controllerArray[$i]) && is_callable(array($c, $f = $controllerArray[$i]))) {
					return array($c, $f, array_slice($controllerArray, $i+1));
				}
				$cArgs = array_slice($controllerArray, $i);
			}
			$controllerArray[$i-1] = strtolower($controllerArray[$i-1]);
		}
		if (isset($cOK) && is_callable(array($cOK, static::$defaultFunction))) {
			return array($cOK, static::$defaultFunction, $cArgs);
		} else {
			return array("Controller\\".static::$defaultController, static::$defaultFunction, $controllerArray);
		}
		return self::$fofHandler;
	}
	
	public static function getErrorPage($error) {
		$obj = new self::$fofHandler[0];
		call_user_func_array(array($obj, self::$fofHandler[1]), array($error));
	}
	
	public static function loadView($view, $variables=array()) {
		if (strpos($view, ".") !== false) {
			throw new Exception("Cannot load views with dots in them");
		}
		foreach ($variables as $key=>$data) {
			$$key = $data;
		}
		include BOILER_LOCATION."application/view/$view.php";
	}
	
	public static function loadHelper($helper, $variables=array()) {
		if (strpos($view, ".") !== false) {
			throw new Exception("Cannot load views with dots in them");
		}
		foreach ($variables as $key=>$data) {
			$$key = $data;
		}
		if (file_exists(BOILER_LOCATION."system/helper/$helper.php")) {
			include_once BOILER_LOCATION."system/helper/$helper.php";
		}
		if (file_exists(BOILER_LOCATION."application/helper/$helper.php")) {
			include_once BOILER_LOCATION."application/helper/$helper.php";
		}
		
	}
}