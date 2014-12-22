<?php
/**
 * Extends the core DBObject with database settings from the config
 * 
 * PHP Version: 5.3
 * 
 * @category Model
 * @package  Boiler
 * @author   ivebeenlinuxed <will@bcslichfield.com>
 * @license  GPL v3.0 http://www.gnu.org/licenses/gpl-3.0.txt
 * @link     http://www.bcslichfield.com/
 *
 */

namespace Model;

/**
 * DBObject override for \Core\Model
 * 
 * DBObject is the main class that all models should extend.
 * It provides core functionality.
 * 
 * @category Model
 * @package  Boiler
 * @author   ivebeenlinuxed <will@bcslichfield.com>
 * @license  GPL v3.0 http://www.gnu.org/licenses/gpl-3.0.txt
 * @link     http://www.bcslichfield.com/
 * @see      \Core\Model
 *
 */
abstract class DBObject extends \System\Model\DBObject {

	public static $data_map;
	
	/*
	public static function getWidgetTypeByColumn($col) {
		if (static::$data_map[$col]) {
			return static::$data_map[$col];
		}
		return "\\Controller\\Widget\\Text";
	}
	
	public static function getWidgetByColumn($col) {
		$w = self::getWidgetTypeByColumn($field);
		$w = new $w;
		return $w;
	}
	
	public function getWidgetByField($field) {
		$w = self::getWidgetTypeByColumn($field);
		$w = new $w;
		$w->field = $field;
		$w->table = static::getTable();
		$w->id = $this->id;
		$w->setResult($this->$field);
		return $w;
	}
	*/
	
}