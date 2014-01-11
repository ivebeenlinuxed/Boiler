<?php
/**
 * 
 * @author ivebeenlinuxed <will@bcslichfield.com>
 *
 */
namespace Core;

use Model\User;

class Router extends \System\Core\Router {
	const MODE_JSON = 0;
	const MODE_HTML = 1;
	const MODE_XML = 2;
	const MODE_JPG = 3;
	const MODE_PNG = 4;
	const MODE_SVG = 5;
	const MODE_GIF = 6;
	
	public static $mode;
	
	protected static $listeners;
	
	/**
	 * Example of how to use extentions
	 */
	 
	public static function getController($array) {
		
		preg_match("/[a-zA-Z0-9_]+(\.(?<extension>html|json|xml|jpg|png|svg|gif))?/", $array[count($array)-1], $matches);
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
				default:
					self::$mode = self::MODE_HTML;
					break;
			}
			$array[count($array)-1] = substr($array[count($array)-1], 0, (strlen($matches['extension'])+1)*-1);
		} else {
			self::$mode = self::MODE_HTML;
		}
		return parent::getController($array);
	}
	
	
	public static function addEventListener($signal, $callback) {
		if (!isset(self::$listeners[$signal])) {
			self::$listeners[$signal] = array();
		}
		self::$listeners[$signal][] = $callback;
	}
	
	public static function triggerEvent($signal, $args) {
		if (!isset(self::$listeners[$signal])) {
			return;
		}
		foreach (self::$listeners[$signal] as $callback) {
			call_user_func_array($callback, $args);
		}
	}
	
	public static function triggerFilter($signal, $args) {
		if (!isset(self::$listeners[$signal])) {
			return;
		}
		foreach (self::$listeners[$signal] as $callback) {
			$args = call_user_func_array($callback, $args);
		}
		return $args;
	}
	
}
