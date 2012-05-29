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

	/**
	 * Override to allow Router settings to take effect
	 * 
	 * @return \Library\Database\LinqDB
	 */
	public static function getDB() {
		return \Library\Database\LinqDB::getDB(\Core\Router::$settings['database']['server'], \Core\Router::$settings['database']['user'], \Core\Router::$settings['database']['passwd'], \Core\Router::$settings['database']['db']);
	}
}