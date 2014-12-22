<?php
namespace System\Core;
/**
 * Routes new requests to correct controller
 *
 * @category Core
 * @package  Boiler
 * @author   ivebeenlinuxed <will@bcslichfield.com>
 * @license  GNU v3.0 http://www.gnu.org/licenses/gpl-3.0.txt
 * @version  GIT: $Id: 74ebec5ae1937b078f562daaaa0e5852281cc95c $
 * @link     http://www.bcslichfield.com
 *
 */
abstract class Router {
	const MODE_JSON = 0;
	const MODE_HTML = 1;
	const MODE_XML = 2;
	const MODE_JPG = 3;
	const MODE_PNG = 4;
	const MODE_SVG = 5;
	const MODE_GIF = 6;
	const MODE_JS = 7;
	const MODE_CSS = 8;
	
	const DB_POSTGRES = 0;
	const DB_MYSQL = 1;
	
	public static $mode;

	public static $settings;
	/**
	 *
	 * Default controller for home page
	 * @var string
	 */
	protected static $defaultController = "Home";

	
	/**
	 *
	 * What disposition is the content displaying (full? modal? mobile?)
	 * @var string
	 */
	public static $disposition = "full";
	
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
	 * Used to hold all the listeners registered via addListener function
	 *
	 * @var array
	 * @see \Core\Router::addListener
	*/
	protected static $listeners = array();

	/**
	 *
	 * Get correct controller, using the argument array
	 * @param array $controllerArray
	*/
	public static function getController($array) {
		preg_match("/[a-zA-Z0-9_]+(\.(?<extension>html|json|xml|jpg|png|svg|gif|js|css))?/", $array[count($array)-1], $matches);
		if (isset($matches['extension'])) {
			switch ($matches['extension']) {
				case "json":
					self::$mode = self::MODE_JSON;
					break;
				case "html":
					self::$mode = self::MODE_HTML;
					break;
				case "xml":
					self::$mode = self::MODE_XML;
					break;
				case "jpg":
					self::$mode = self::MODE_JPG;
					break;
				case "png":
					self::$mode = self::MODE_PNG;
					break;
				case "svg":
					self::$mode = self::MODE_SVG;
					break;
				case "gif":
					self::$mode = self::MODE_GIF;
					break;
				case "js":
					self::$mode = self::MODE_JS;
					break;
				case "css":
					self::$mode = self::MODE_CSS;
					break;
				default:
					self::$mode = self::MODE_HTML;
					break;
			}
			$array[count($array)-1] = substr($array[count($array)-1], 0, (strlen($matches['extension'])+1)*-1);
		} else {
			self::$mode = self::MODE_HTML;
		}
		$controllerArray = $array;
		
		
		if ((count($controllerArray) == 1 && $controllerArray[0] == "") || count($controllerArray) == 0 || is_array($controllerArray[0])) {
			return array("Controller\\".static::$defaultController, static::$defaultFunction, array());
		}
		for ($i=1; $i<=count($controllerArray) && !is_array($controllerArray[$i-1]); $i++) {
			if (strpos($controllerArray[$i-1], "_") !== false) {
				$controllerArray[$i-1] = implode("_",array_map(function($data) {return ucfirst($data);}, explode("_", $controllerArray[$i-1])));
			} else {
				$controllerArray[$i-1] = ucfirst($controllerArray[$i-1]);
			}
			if (class_exists($c = "Controller\\".implode("\\", array_slice($controllerArray, 0, $i)), true)) {
				$cOK = $c;
				if (isset($controllerArray[$i]) && is_callable(array($c, $f = $controllerArray[$i]))) {
					return array($c, $f, array_slice($controllerArray, $i+1));
				}
				$cArgs = array_slice($controllerArray, $i);
			} elseif (class_exists($c = "System\\Controller\\".implode("\\", array_slice($controllerArray, 0, $i)), true)) {
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

	/**
	 * Initiates bootstraps and the config
	 *
	 * @return null
	 */
	public static function Init() {
		if (file_exists(BOILER_LOCATION."../config.php")) {
			include BOILER_LOCATION."../config.php";
			self::$settings = $settings;
		}
		

		if (isset($_SERVER['HTTP_X_DISPOSITION'])) {
			self::$disposition = $_SERVER['HTTP_X_DISPOSITION'];
		}
		
		if (isset($_SERVER['HTTP_X_REQUEST_METHOD'])) {
			$_SERVER['REQUEST_METHOD'] = $_SERVER['HTTP_X_REQUEST_METHOD'];
		}
		session_start();
	}


	/**
	 * Get the error page
	 *
	 * @param int $error
	 *
	 * @return null
	 */
	public static function getErrorPage($error) {
		$obj = new self::$fofHandler[0];
		call_user_func_array(array($obj, self::$fofHandler[1]), array($error));
	}

	public static function loadView($routerViewObscuratedVariableToAvoidCollision, $variables=array()) {
		if (strpos($routerViewObscuratedVariableToAvoidCollision, ".") !== false) {
			throw new \Exception("Cannot load views with dots in them");
		}
		foreach ($variables as $key=>$data) {
			$$key = $data;
		}

		if (isset($variables['data'])) {
			$data = $variables['data'];
		}
		if (file_exists(BOILER_LOCATION."application/view/$routerViewObscuratedVariableToAvoidCollision.php")) {
			include BOILER_LOCATION."application/view/$routerViewObscuratedVariableToAvoidCollision.php";
		} else {
			include BOILER_LOCATION."system/view/$routerViewObscuratedVariableToAvoidCollision.php";
		}
	}
	
	public static function hasView($routerViewObscuratedVariableToAvoidCollision) {
		return
			file_exists(BOILER_LOCATION."application/view/$routerViewObscuratedVariableToAvoidCollision.php")
			|| file_exists(BOILER_LOCATION."system/view/$routerViewObscuratedVariableToAvoidCollision.php");
	}

	public static function getView($view, $variables=array(), $system=false) {
		$bufferASystemObscuratedVariableToAvoidCollision = ob_get_clean();
		ob_start();
		self::loadView($view, $variables, $system);
		$bufferBSystemObscuratedVariableToAvoidCollision = ob_get_clean();
		ob_start();
		echo $bufferASystemObscuratedVariableToAvoidCollision;
		return $bufferBSystemObscuratedVariableToAvoidCollision;
	}

	public static function loadHelper($helper, $variables=array()) {
		if (strpos($view, ".") !== false) {
			throw new Exception("Cannot load views with dots in them");
		}
		foreach ($variables as $key=>$data) {
			$$key = $data;
		}

		if (isset($variables['data'])) {
			$data = $variables['data'];
		}
		if (file_exists(BOILER_LOCATION."system/helper/$helper.php")) {
			include_once BOILER_LOCATION."system/helper/$helper.php";
		} elseif (file_exists(BOILER_LOCATION."application/helper/$helper.php")) {
			include_once BOILER_LOCATION."application/helper/$helper.php";
		}

	}

	/**
	 * Add a listener to the system
	 *
	 * @param string $signal   Signal on which to activate
	 * @param mixed  $callable A callable function
	 * @param array  $param    Parameters to append after event params
	 *
	 * @return null
	 */
	public static function addListener($signal, $callable, $param=array()) {
		if (!isset(self::$listeners[$signal])) {
			self::$listeners[$signal] = array();
		}
		self::$listeners[$signal] = array("callable"=>$callable, "param"=>$param);
	}

	/**
	 * Trigger an event in the system
	 *
	 * @param string $signal Signal to trigger
	 * @param array  $param  Event Arguments
	 *
	 * @return null
	 */
	public static function triggerEvent($signal, $param=array()) {
		if (!isset(self::$listeners[$signal])) {
			return;
		}
		foreach (self::$listeners[$signal] as $call) {
			call_user_func($call['callable'], array_merge($param, $call['param']));
		}
	}
}
