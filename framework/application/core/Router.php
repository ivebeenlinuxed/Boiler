<?php
/**
 * 
 * @author ivebeenlinuxed <will@bcslichfield.com>
 *
 */
namespace Core;

use Model\User;

class Router extends \System\Core\Router {
	
	protected static $listeners;
	
	
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
