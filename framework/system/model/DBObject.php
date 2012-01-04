<?php
namespace System\Model;
use Library\Database\DBException;

/**
 * DBObject.php
 *
 * This file contains MySQL handles and acts as a simple LINQ interface, using
 * PHP inbuilt late static bindings to create an object out of the calling class.
 * 
 * @author star241
 * @version $Id$
 * @since Sat 24 Jul 2010 23:46:26 
 */


/**
 * DBObject
 *
 * Class containing LINQ like interface using late static bindings
 * 
 * @abstract
 */
abstract class DBObject implements \Library\Database\LinqObject {
/**
 * @var int
 */
	protected $ID;
	
/**
 * @static
 * @var MySQLResource
 */	
	private static $masterDB;

/**
 * getTable
 *
 * Get table from child class
 * 
 * @static
 * @return string
 */
	//public static abstract function getTable($read=false);

/**
 * getPrimaryKey
 *
 * Get primary key from child class
 * @static
 * @return string
 */
	public static abstract function getPrimaryKey();

	public static abstract function getDB();

 	public $DB;
 	
 	private $Table;
 	
 	private $PrimaryKey;
 	
/**
 * __construct
 * 
 * Create new object using primary key. Dynamically creates the database
 * turples into properties
 * 
 * @param string $Id
 * @return void
 */ 	
	public function __construct($Id) {
		$c = get_called_class();
		$this->DB = $c::getDB();
		$this->Table = $this->DB->escape_string($this->getTable(true));
		$this->PrimaryKey = $this->getPrimaryKey();
		$this->className = get_called_class();
		
		if (!is_array($this->PrimaryKey)) {
			$this->PrimaryKey = array($this->PrimaryKey);
		}
		
		if (!is_array($Id)) {
			$this->ID = array($this->PrimaryKey[0]=>$Id);
		} else {
			$this->ID = $Id;
		}
		
		if (count($this->ID) != count($this->PrimaryKey)) {
			
			throw new DBException("Primary key is the wrong length");
		}
		
		$select = $this->DB->Select($this->className);
		$and = $this->DB->getAndFilter();
		
		foreach ($this->PrimaryKey as $Key) {
			if (!isset($this->ID[$Key])) {
				throw new DBException("Required key component '$Key' missing.");
			}
			$and->eq($Key, $this->ID[$Key]);
		}
		$select->setFilter($and);
		$s = $select->Exec();
		if (count($s) == 1) {
			foreach ($s[0] as $Key=>$Data)
				$this->$Key = $Data;
		} else {
			throw new DBException("No object with ID '$Id'");
		}
	}

/**
 * setAttribute
 *
 * Set attribute in database and in the object. Value must have _toString() method
 * 
 * @param string $name
 * @param mixed $value
 * @return void
 */
	public function setAttribute($name, $value) {
		return $this->setAttributes(array($name=>$value));
	}
	
	public function setAttributes($array) {
		$er = false;
		$update = $this->DB->Update($this->className);
		
		foreach ($array as $Key=>$Data) {
			$update->addSet($Key, $Data);
			$this->$Key = $Data;
		}
		
		$filter = $this->DB->getAndFilter();
		foreach ($this->PrimaryKey as $Key) {
			$filter->eq($Key, $this->$Key);
			$update->setFilter($filter);
		}
		$update->Exec();
		if ($this->DB->errno != 0) {
			throw new DBException("Error occurred: ".self::getError());
		} else {
			return true;
		}
	}
	
	
	public static function getByAttribute($name, $value, $order=null, $start=null, $limit=null) {
		return self::getByAttributes(array($name=>$value), $order, $start, $limit);
	}
	
	public function __toString() {
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
		
		if (!is_array($p)) {
			$p = array($p);
		}
		
		$sQ = "DELETE FROM `".$c::getTable()."` WHERE";
		foreach ($p as $key) {
			$sQ .= "`".$this->DB->escape_string($key)."`='".$this->$key."' AND";
		}
		$sQ = substr($sQ, 0, -4);
		
		$this->DB->query($sQ);
	}
	
	public function Delete() {
		$this->DBDelete();
	}
	
	public static function Truncate() {
		$DB = self::getDB();
		$sQ = "TRUNCATE `".$c::getTable()."`";
		$DB->query($sQ);
	}
	
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
			throw new DBException("Primary key is the wrong length");
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
	
	private static function getIDByAttributes($array=null, $order=null, $start=null, $limit=null) {
		$out = array();
		$c = get_called_class();
		$DB = $c::getDB();
		$select = $DB->Select($c);
		$and = $DB->getAndFilter();
		
		if (isset($array)) {
			foreach ($array as $Key=>$Data) {
				$and->eq($Key, $Data);
			}
		}
		if (isset($order)) {
			if ($order == "RAND()") {
				$select->setOrder(null);
			} else {
				if (substr($order, -1) == "-") {
					$select->setOrder(substr($order, 0, -1), false);
				} elseif (substr($order, -1) == "+") {
					$select->setOrder(substr($order, 0, -1), true);
				} else {
					
					$select->setOrder($order);
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
			throw new DBException(self::getError($DB));
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
	
	
	
	public static function Search($expression, $field) {
		$c = get_called_class();
		$DB = $c::getDB();
		if (strpos(" ", $expression) !== false) {
			$a = explode(" ", $expression);
		} else {
			$a = array($expression);
		}
		
		$select = $DB->Select($c);
		$and = $DB->getAndFilter();
		
		
		foreach ($a as $s) {
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
	
	public static function getAll($order=null, $start=null, $limit=null) {
		return self::getByAttributes(null, $order, $start, $limit);
	}
	
	public static function getIdByCreate($Array) {
		
		$c=get_called_class();
		$DB = $c::getDB();
		$class['Table'] = $c::getTable(false);
		$class['PrimaryKey'] = $c::getPrimaryKey();
		
		if (!is_array($class['PrimaryKey'])) {
			$class['PrimaryKey'] = array($class['PrimaryKey']);
		}
		
		$sQ = "INSERT INTO `".$class['Table']."` (";
		foreach ($Array as $Key=>$Data) {
			$sQ .= "`".$DB->escape_string($Key)."`, ";
		}
		$sQ = substr($sQ, 0, -2);
		$sQ .= ") VALUES (";
		
		foreach ($Array as $Key=>$Data) {
			if ($Data !== false) {
				$sQ .= "'".$DB->escape_string($Data)."', ";
			} else {
				$sQ .= "NULL, ";
			}
		}
		$sQ = substr($sQ, 0, -2);
		$sQ .= ")";
		$DB->query($sQ);
		$id = $DB->insert_id;
		if ($DB->errno != 0) {
			throw new DBException(self::getError($DB));
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
	
	
	public static function Create($Array) {
		
		$c = get_called_class();
		$id = self::getIdByCreate($Array);
		return new $c($id);
		
	}
	
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
	
	public function removeByAttribute($field, $value, $start=null, $limit=null) {
		self::removeByAttributes(array($field=>$value), $start, $limit);
	}
	
	public function removeByAttributes($array, $start=null, $limit=null) {
		$c=get_called_class();
		$DB = $c::getDB();
		$sQ = "DELETE FROM `".$c::getTable(false)."`";
		if ($array != null) {
			$sQ .= " WHERE ";
			foreach ($array as $Key=>$Data) {
				$sQ .= "`".$DB->escape_string($Key)."`= '".$DB->escape_string($Data)."' AND ";
			}
			$sQ = substr($sQ, 0, -4);
		}
		if ($start != null) {
			$sQ .= " LIMIT ".$DB->escape_string($start);
			if ($limit != null) {
				$sQ .= ",".$DB->escape_string($limit);
			}
		}
		$q = $DB->query($sQ);
		if ($DB->errno != 0) {
			throw new DBException(self::getError($DB));
		}
		return true;
	}
}
?>
