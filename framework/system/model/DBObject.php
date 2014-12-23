<?php
/**
 * DBObject.php
 *
 * This file contains MySQL handles and acts as a simple LINQ interface, using
 * PHP inbuilt late static bindings to create an object out of the calling class.
 * 
 * @category  Controller
 * @package   Portal
 * @author    Will Tinsdeall <will.tinsdeall@mercianlabels.com>
 * @copyright 2013 Mercian Labels
 * @license   http://www.mercianlabels.com All Rights Reserved
 * @version   $Id: 053a788e309ba1782d692cd2362b9d5c789a2d58 $
 * @link      http://www.mercianlabels.com
 * @since     Sat 24 Jul 2010 23:46:26
 */

namespace System\Model;

use Library\Database\DBDuplicationException;
use Library\Database\DBException;




/**
 * DBObject
 *
 * Class containing LINQ like interface using late static bindings
 *
 * @abstract
 *
 */
abstract class DBObject implements \Library\Database\LinqObject {

	public static $throwDuplicateException = true;


	/**
	 * @var int
	 */
	protected $ID;

	/**
	 * @var MySQLResource
	 */
	private static $masterDB;

	/**
	 * getTable
	 *
	 * Get table from child class
	 *
	 * @return string
	 */
	//public static abstract function getTable($read=false);

	/**
	 * getPrimaryKey
	 *
	 * Get primary key from child class
	 *
	 * @return string
	 */
	public static abstract function getPrimaryKey();

	/**
	 * Returns the LinqDB associated with this object (gets the mysqli database)
	 *
	 * @see \Library\Database\LinqDB
	 * @return \Library\Database\LinqDB;
	*/
	//public static abstract function getDB();

	public $DB;

	/**
	 * Used internally for the table name
	 *
	 * @deprecated
	 * @var string
	 */
	private $Table;

	/**
	 * Used interanally for primary key
	 *
	 * @deprecated
	 * @var string
	 */
	private $PrimaryKey;

	/**
	 * Whether to commit changes directly to database when changing a parameter
	 * 
	 * @var boolean
	 */
	private $autoCommit = false;
	
	/**
	 * __construct
	 *
	 * Create new object using primary key. Dynamically creates the database
	 * turples into properties
	 *
	 * If this key is a concatinated key an associative array may be used to pull the selected row
	 *
	 * @param string $Id The ID (from Primary Key columns) of the object to pull.
	 *
	 * @return void
	 */
	public function __construct($Id) {
		$c = get_called_class();
		$DB = $c::getDB();
		$Table = pg_escape_string($this->getTable(true));
		$PrimaryKey = $this->getPrimaryKey();
		$className = get_called_class();
		if (!is_array($PrimaryKey)) {
			$PrimaryKey = array($PrimaryKey);
		}

		if (!is_array($Id)) {
			$ID = array($PrimaryKey[0]=>$Id);
		} else {
			$ID = $Id;
		}

		if (count($ID) != count($PrimaryKey)) {

			throw new DBException("Primary key is the wrong length");
		}
		$select = $DB->Select($c);
		$and = $DB->getAndFilter();

		foreach ($PrimaryKey as $Key) {
			if (!isset($ID[$Key])) {
				throw new DBException("Required key component '$Key' missing.");
			}
			$and->eq($Key, $ID[$Key]);
		}
		$select->setFilter($and);
		$s = $select->Exec();
		if (count($s) == 1) {
			foreach ($s[0] as $Key=>$Data) {
				$this->$Key = $Data;
			}
		} else {
			throw new DBException("No '$c' object with ID '".json_encode($Id)."'");
		}
		
		$this->autoCommit = true;
	}

	public function getID() {
		if (!is_array($key = $this->getPrimaryKey())) {
			$key = array($key);
		}

		$out = array();
		foreach ($key as $k) {
			$out[$k] = $this->$k;
		}
		return $out;
	}

	public function isEqual(DBObject $o) {
		if (get_class($o) != get_called_class()) {
			return false;
		}

		if ($this->getUniqueIdentifier() != $o->getUniqueIdentifier()) {
			return false;
		}

		return true;
	}

	public static function Fetch($id) {
		$c = get_called_class();
		try {
			return new $c($id);
		} catch (DBException $e) {
			return false;
		}
	}

	/**
	 * setAttribute
	 *
	 * Set attribute in database and in the object. Value must have _toString() method
	 *
	 * @param string  $name  Name of field
	 * @param mixed   $value Value of field
	 * @param boolean $set   Whether to set the attribute on the current class
	 *
	 * @return void
	 */
	public function setAttribute($name, $value, $set=true) {
		return $this->setAttributes(array($name=>$value), $set);
	}

	/**
	 * Stores an array of attribute changes into database
	 * 
	 * @param string[] $array Array of parameter changes
	 * @param string   $set   Whether to set the attribute to the class
	 * 
	 * @throws DBException
	 * @return boolean
	 */
	public function setAttributes($array, $set=true) {
		$c = get_called_class();
		$er = false;
		

		foreach ($c::getModelHierarchy() as $model) {
			$PrimaryKey = $model::getPrimaryKey();
			$DB = $model::getDB();
			$update = $DB->Update($model);
			
			$fields = array();
			foreach ($array as $key=>$data) {
				if (in_array($key, $model::getDBColumns())) {
					$fields[$key] = $data;
				}
			}
			
			if (count($fields) == 0) {
				continue;
			}
			
			foreach ($fields as $Key=>$Data) {
				$update->addSet($Key, $Data);
				if ($set) {
					$this->$Key = $Data;
				}
			}
	
			$filter = $DB->getAndFilter();
			foreach ($PrimaryKey as $Key) {
				$filter->eq($Key, $this->$Key);
				$update->setFilter($filter);
			}
			$update->Exec();
			if ($DB->errno != 0) {
				throw new DBException("Error occurred: ".self::getError());
			}
		}
		
		return true;
		
	}


	public static function getByAttribute($name, $value, $order=null, $start=null, $limit=null) {
		return self::getByAttributes(array($name=>$value), $order, $start, $limit);
	}

	/**
	 * Returns primary key as string
	 *
	 * @return null
	 */
	public function __toString() {
		$c = get_called_class();
		$p = $c::getPrimaryKey();
		return $this->$p;
	}


	public static function getByAttributes($array=null, $order=null, $start=null, $limit=null) {
		$class=get_called_class();
		$a = self::getIDByAttributes($array, $order, $start, $limit);
		$out = array();
		if (count($a) > 0) {
			foreach ($a as $value) {
				$c = new $class($value);
				$out[] = $c;
			}
		}
		return $out;
	}

	protected function DBDelete() {
		$c = get_called_class();
		$p = $c::getPrimaryKey();
		$DB = $c::getDB();
		if (!is_array($p)) {
			$p = array($p);
		}

		$sQ = "DELETE FROM ".pg_escape_identifier($c::getTable())." WHERE";
		foreach ($p as $key) {
			$sQ .= " ".pg_escape_identifier($key)."=".pg_escape_string($this->$key)." AND";
		}
		$sQ = substr($sQ, 0, -4);

		$DB->query($sQ);
	}

	/**
	 * Deletes the record
	 */
	public function Delete() {
		$this->DBDelete();
	}

	/**
	 * Performs a TRUNCATE
	 * @throws DBException
	 */
	public static function Truncate() {
		$c = get_called_class();
		$DB = $c::getDB();
		$sQ = "TRUNCATE `".$c::getTable()."`";
		$st = $DB->prepare($sQ);
		$st->execute();
		if ($st->errno != 0) {
			throw new DBException($st->error);
		}
		$st->close();
		//$q = $DB->query($sQ);
	}

	/**
	 * Performs "DELETE FROM `table`" query, useful for Foreign keys where TRUCNATE does not work, but is less efficient than TRUNCATE
	 *
	 * @throws DBException
	 *
	 * @return null
	 */
	public static function DeleteAll() {
		$c = get_called_class();
		$DB = $c::getDB();
		$sQ = "DELETE FROM `".$c::getTable()."`";
		$st = $DB->prepare($sQ);
		$st->execute();
		if ($st->errno != 0) {
			throw new DBException($st->error);
		}
		$st->close();
	}

	/**
	 * Checks if a record exists
	 *
	 * @param mixed $id The ID of the record
	 *
	 * @throws DBException
	 * @throws \Library\Database\DBException
	 *
	 * @return boolean
	 */
	public static function Exists($id) {
		$c = get_called_class();
		$p = $c::getPrimaryKey();
		$db = $c::getDB();
		if (!is_array($p)) {
			$p = array($p);
		}

		if (!is_array($id)) {
			$id = array($p[0]=>$id);
		}


		if (count($id) != count($p)) {
			throw new \Library\Database\DBException("Primary key is the wrong length");
		}
		$select = $db->Select($c);
		$select->addCount("c");
		$and = $db->getAndFilter();

		foreach ($p as $Key) {
			if (!isset($id[$Key])) {
				throw new \Library\Database\DBException("Required key component '$Key' missing.");
			}
			$and->eq($Key, $id[$Key]);
		}


		$select->setFilter($and);


			
		$a = $select->Exec();
		if ($a[0]['c'] == "1") {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get ID of rows found 
	 * 
	 * @param string $array  Filter of fields
	 * @param string $orders Array or string of orders
	 * @param string $start  Query offset
	 * @param string $limit  Limit count
	 * 
	 * @throws \Library\Database\DBException
	 * @return multitype:|multitype:multitype:unknown  unknown
	 */
	private static function getIDByAttributes($array=null, $orders=null, $start=null, $limit=null) {
		$out = array();
		$c = get_called_class();
		$DB = $c::getDB();
		$select = $DB->Select($c);
		$and = $DB->getAndFilter();
		if (!is_array($orders)) {
			$orders = array($orders);
		}
		
		if (isset($array)) {
			foreach ($array as $Key=>$Data) {
				if ($Data === false) {
					$and->isnull($Key);
				} else {
					$and->eq($Key, $Data);
				}
			}
		}
		if (isset($orders)) {
			if ($orders[0] == "RAND()") {
				$select->setOrder(null);
			} else {
				foreach ($orders as $order) {
					if (is_string($order) && substr($order, -1) == "-") {
						$select->setOrder(substr($order, 0, -1), false);
					} elseif (is_string($order) && substr($order, -1) == "+") {
						$select->setOrder(substr($order, 0, -1), true);
					} elseif (is_array($order)) {
						$select->setOrder($order[0], $order[1]);
					} else {
						$select->setOrder($order);
					}
				}
			}
		}
		if (isset($start)) {
			if (isset($limit)) {
				$select->setLimit($start, $limit);
			} else {
				$select->setLimit($start);
			}
		}
		$select->setFilter($and);
		$q = $select->Exec();
		if ($DB->errno != 0) {
			throw new \Library\Database\DBException(self::getError($DB));
		}
		$p = $c::getPrimaryKey();

		if (!is_array($p)) {
			$p = array($p);
		}

		if (count($q) == 0) {
			return array();
		}

		foreach ($q as $a) {
			if (is_array($p)) {
				$outt = array();
				foreach ($p as $key) {
					$outt[$key] = $a[$key];
				}
				$out[] = $outt;
			} else {
				$out[] = $a[$p];
			}
		}
		return $out;
	}


	/**
	 * Searches the database using automatically inserted wildcards
	 *
	 * @param string          $field      The fields to search
	 * @param string|string[] $expression Expression to look for
	 *
	 * @throws DBException
	 *
	 * @return multitype:Ambigous <unknown, string> The Primary Key
	 */
	public static function Search($field, $expression) {
		$c = get_called_class();
		$DB = $c::getDB();
		if (@strpos(" ", $expression) !== false) {
			$a = explode(" ", $expression);
		} else {
			$a = array($expression);
		}

		$select = $DB->Select($c);
		$and = $DB->getAndFilter();


		foreach ($a as $s) {
			if ($s == "") {
				continue;
			}
			$and->like($field, "%".$s."%");
		}

		$select->setFilter($and);
		$q = $select->Exec();
		if ($DB->errno != 0) {
			throw new DBException(self::getError($DB));
		}

		$oout = array();
		foreach ($q as $a) {
			if (is_array($p = $c::getPrimaryKey())) {
				$outt = array();
				foreach ($p as $key) {
					$outt[$key] = $a[$key];
				}
				$oout[] = $outt;
			} else {
				$oout[] = $a[$p];
			}
		}

		$out = array();
		if (count($oout) > 0) {
			foreach ($oout as $value) {
				$c = new $c($value);
				$out[] = $c;
			}
		}
		return $out;
	}

	/**
	 * Gets all the records from a table
	 *
	 * @param string $order
	 * @param int    $start
	 * @param int    $limit
	 *
	 * @return self
	 */
	public static function getAll($order=null, $start=null, $limit=null) {
		return self::getByAttributes(null, $order, $start, $limit);
	}

	/**
	 * Creates a new record in the MySQL database and returns in the Primary Key
	 *
	 * @param string[] $Array The values of the record
	 *
	 * @throws DBDuplicationException
	 * @throws DBException
	 *
	 * @return string
	 */
	public static function getIdByCreate($Array) {

		$c=get_called_class();
		$DB = $c::getDB();
		$class['Table'] = $c::getTable(false);
		$class['PrimaryKey'] = $c::getPrimaryKey();

		if (!is_array($class['PrimaryKey'])) {
			$class['PrimaryKey'] = array($class['PrimaryKey']);
		}
		
		if (count($Array) == 0) {
			foreach ($class['PrimaryKey'] as $key) {
				$Array[$key] = false;
			}
		}

		$sQ = "INSERT INTO `".$class['Table']."`";
			$sQ .= " (";
			foreach ($Array as $Key=>$Data) {
				$sQ .= pg_escape_identifier($Key).", ";
			}
			$sQ = substr($sQ, 0, -2);
			$sQ .= ") VALUES (";
	
			foreach ($Array as $Key=>$Data) {
				if ($Data !== false && $Data !== null) {
					$sQ .= pg_escape_literal($Data).", ";
				} else {
					$sQ .= "NULL, ";
				}
			}
			$sQ = substr($sQ, 0, -2);
			$sQ .= ")";
		$DB->query($sQ);
		$id = $DB->insert_id;
		if ($DB->errno != 0) {
			$e = self::getError($DB);
			if (substr($e, 0, 15) == "Duplicate entry") {
				if (self::$throwDuplicateException) {
					throw new DBDuplicationException($e);
				}
			} else {
				throw new DBException($e);
			}
		}

		if ($id != null) {
			return array($class['PrimaryKey'][0]=>$id);
		}



		$out = array();
		foreach ($class['PrimaryKey'] as $key) {
			$out[$key] = $Array[$key];
		}
		return $out;
	}
	
	/**
	 * Get all \Model namespaced models that this class inherits (including itself)
	 * 
	 * @return string[]
	 */
	public static function getModelHierarchy() {
		$c = get_called_class();
		
		$rc = new \ReflectionClass($c);
		$parents = array($c);
		while ($parent = $rc->getParentClass()) {
			if ($parent->inNamespace() && $parent->getNamespaceName() == "Model" && $parent->getShortName() != "DBObject") {
				$parents[] = $parent->getName();
			}
			$rc = $parent;
		}
		return $parents;
	}

	/**
	 * Creates a new record in the MySQL and returns result as an Object
	 *
	 * @param string[] $Array
	 * @return self
	 */
	public static function Create($Array) {
		$c = get_called_class();
		$id = self::getIdByCreate($Array);
		return new $c($id);

	}

	/**
	 * Gets the error of the database context
	 *
	 * @param \MySQLi $DB
	 * @throws Exception
	 */
	protected function getError($DB = "") {
		if (!isset($this) && $DB == "") {
			throw new Exception("No database in context");
		}

		if (isset($this)) {
			return $this->DB->error;
		} else {
			return $DB->error;
		}
	}

	/**
	 * Deletes records
	 *
	 * @param string $field Field to search for
	 * @param string $value Value of the field
	 * @param int    $start Record to start delete
	 * @param int    $limit Record to end delete
	 *
	 * @return null
	 */
	public static function removeByAttribute($field, $value, $start=null, $limit=null) {
		self::removeByAttributes(array($field=>$value), $start, $limit);
	}

	/**
	 * Deletes records using an associative array as search parameter
	 *
	 * @param string[] $array Associative array of search parameters
	 * @param int      $start Start at this record of search
	 * @param int      $limit End and this record
	 *
	 * @throws DBException
	 *
	 * @return null
	 */
	public static function removeByAttributes($array, $start=null, $limit=null) {
		$c=get_called_class();
		$DB = $c::getDB();
		$sQ = "DELETE FROM `".$c::getTable(false)."`";
		if ($array != null) {
			$sQ .= " WHERE ";
			foreach ($array as $Key=>$Data) {
				$sQ .= pg_escape_identifier($Key)."= ".pg_escape_literal($Data)." AND ";
			}
			$sQ = substr($sQ, 0, -4);
		}
		if ($start != null) {
			$sQ .= " OFFSET ".((int)$start);
		}
		
		if ($limit != null) {
			$sQ .= " LIMIT ".((int)$limit);
		}
		
		$q = $DB->query($sQ);
		if ($DB->errno != 0) {
			throw new DBException(self::getError($DB));
		}
		return true;
	}

	/**
	 * Gets a string which acts as a unique identifier for the object
	 *
	 * @return string
	 */
	public function getUniqueIdentifier() {
		$id = $this->getID();
		$out = "";
		foreach ($id as $i) {
			$out .= "{$i}-";
		}
		return substr($out, 0, -1);
	}
	
	
	public function getName() {
		if (in_array("name", static::getDBColumns())) {
			$name = "name";
		} elseif (in_array("title", static::getDBColumns())) {
			$name = "title";
		} elseif (in_array("description", static::getDBColumns())) {
			$name = "description";
		} else {
			$name = static::getPrimaryKey()[0];
		}
		return $this->$name;
	}
	
	
	public static $data_map;
	
	/**
	 * Override to allow Router settings to take effect
	 * 
	 * @return \Library\Database\LinqDB
	 */
	public static function getDB() {
		return \Library\Database\LinqDB::getDB(\Core\Router::$settings['database']['server'], \Core\Router::$settings['database']['user'], \Core\Router::$settings['database']['passwd'], \Core\Router::$settings['database']['db'], \Core\Router::$settings['database']['port']);
	}
	
	
	public static function getWidgetTypeByColumn($col) {
		if (static::$data_map[$col]) {
			$r = static::$data_map[$col];
			if (is_int($r)) {
				return $r;
			} else {
				return $r->widget;
			}
		}
		return "\\Controller\\Widget\\Text";
	}
	
	public static function getFieldPropertiesByColumn($col) {
		$class = get_called_class();
		if (($map = static::$data_map[$col]) && is_object($map)) {
			if (!$map->title) {
				$map->title = \System\Library\Lexical::humanize($col);
			}
		} else {
			$fks = $class::getForeignKeys();
			if (is_array($fks) && isset($fks[$col])) {
				$key = $fks[$col];
				$map = new \Library\FieldProperties();
				$map->widget = "\\Controller\\Widget\\ForeignKey";
				$map->title = \System\Library\Lexical::humanize($col);
				$map->widget_data['table'] = $key->table;
				$map->visibility = \Library\FieldProperties::VISIBILITY_SHOW;
				
			} else {
				$map = new \Library\FieldProperties();
				$map->widget = "\\Controller\\Widget\\Text";
				$map->title = \System\Library\Lexical::humanize($col);
				$map->visibility = \Library\FieldProperties::VISIBILITY_SHOW;
			}
		}
		return $map;
	}
	
	public static function getWidgetByColumn($col) {
		$map = self::getFieldPropertiesByColumn($col);
		$w = new $map->widget;
		$w->setDataFields($map->widget_data);
		$w->table = static::getTable();
		$w->field = $col;
		return $w;
	}
	
	public function getWidgetByField($field) {
		$map = self::getFieldPropertiesByColumn($field);
		$w = new $map->widget;
		$fields = $map->widget_data;
		$map->widget_data['field'] = $field;
		$map->widget_data['table'] = static::getTable();
		$map->widget_data['id'] = $this->id;
		
		$w->setDataFields($map->widget_data);
		//$w = \Library\Widget\Widget::getWidgetByClass(self::getWidgetTypeByColumn($field));
		//$w->field = $field;
		//$w->table = static::getTable();
		//$w->id = $this->id;
		$w->setResult($this->$field);
		return $w;
	}
}
?>
