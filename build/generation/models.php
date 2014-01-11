<?php
require_once "common.php";





$models = getModels();

foreach ($models as $table=>$model) {
	$f = "../../framework/system/model/".($c = getClassName($table)).".php";
	
	if (isset(\Core\Router::$settings['generation']['system_extend'][$c])) {
		$extend = \Core\Router::$settings['generation']['system_extend'][$c];
	} else {
		$extend = "\Model\DBObject";
	}
	
	echo "BUILDING $f (extends $extend)\r\n";
	$className = getClassName($table);
	$pkey = '"'.implode('","', $models[$table]['key']).'"';
	$columnArray = getPHPArray(array_keys($models[$table]['columns']));
	
	$file = <<<EOF
<?php
/**
 * Autogenerated model for \\Model\\$className
 * 
 * PHP version 5.4
 *
 * @category Model
 * @package  Boiler
 * @author   Will Tinsdeall <will.tinsdeall@mercianlabels.com>
 * @license  GNU v3.0 http://www.gnu.org/licenses/gpl-3.0.txt
 * @link     http://www.mercianlabels.com
 *
 */
namespace System\Model;
	

EOF;
	
	$file .= <<<EOF
/**
 * Autogenerated model for \\Model\\$className
 * 
 * PHP version 5.4
 *
 * @category Model
 * @package  Boiler
 * @author   Will Tinsdeall <will.tinsdeall@mercianlabels.com>
 * @license  GNU v3.0 http://www.gnu.org/licenses/gpl-3.0.txt
 * @version  GIT: \$Id$
 * @link     http://www.mercianlabels.com
 *
 */
class $className extends $extend {
EOF;
	foreach ($models[$table]['columns'] as $column=>$desc) {
		if (substr($desc, 0, strlen("varchar")) == "varchar") {
			$type = "string";
		} elseif (substr($desc, 0, strlen("int")) == "int") {
			$type = "int";
		} elseif (substr($desc, 0, strlen("int")) == "bool") {
			$type = "boolean";
		} else {
			$type = "unknown_type";
		}
		
		$file .= <<<EOF

	
	/**
	* $desc
	* 
	* @var $type \$$column 
	*/
	public \$$column;
EOF;
	}
	$file .= <<<EOF
	
	/**
	 * Lists all the columns in the database
	 *
	 * @return array
	 */
	public static function getDBColumns() {
		return $columnArray;
	}
	
	/**
	 * Gets the table name (always returns "$table")
	 * 
	 * @param boolean \$read changes the table name if a database view is provided for reading, rather than a table
	 * 
	 * @return string
	 */
	public static function getTable(\$read=true) {
		return "$table";
	}
	
	/**
	 * Gets the primary key
	 * 
	 * @return array
	 */
	public static function getPrimaryKey() {
		return array($pkey);
	}
EOF;
	
	$addArgs = array();
	foreach ($models[$table]['key'] as $addArgs) {
		
	}
	/*
	$file .= <<<EOF
	public static function Add() {
		
	}
EOF;
	*/
	
	foreach ($models[$table]['multi'] as $col=>$key) {
		//if ($key[2] != $models[$key[1]]['key'][0] || count($models[$key[1]]['key']) != 1) {
		//	continue;
		//}
		$className = getClassName($key[1]);
		$selfName = getClassName($table);
		$column = getClassName($key[0]);
		echo " - get{$column}(): \\Model\\$className(\$this->$key[0])\r\n";
		$file .= <<<EOF

		
	/**
	 * Gets all $className associated with this object
	 * 
	 * @return System\Model\$className
	 */
	public function get{$column}() {
		return \\Model\\$className::Fetch(\$this->$key[0]);
	}
EOF;
		
		
		echo " - getBy$column($className \$class): self()\r\n";
		$file .= <<<EOF
			
		
	/**
	 * Gets all objects relating to $className
	 * 
	 * @param \$class \Model\$className Get objects relating to this class
	 * 
	 * @return array
	 */		
	public static function getBy$column($className \$class) {
		\$c = get_called_class();
		return \$c::getByAttribute("{$key[0]}", \$class->{$key[2]});
	}
EOF;
	}
	
	foreach ($models[$table]['single'] as $col=>$key) {
		$origClassName = $className = getClassName($key[1]);
		$selfName = getClassName($table);
		$column = getClassName($key[2]);
		
		$repeat = false;
		foreach ($models[$table]['single'] as $colB=>$keyB) {
			if ($col != $colB && $key[1] == $keyB[1]) {
				$repeat = true;
				break;
			}
		}
		
		$classNamePlural = getClassNamePlural($className);
		
		if ($repeat) {
			$name = "get{$classNamePlural}From{$column}";
		} else {
			$name = "get{$classNamePlural}";
		}
		
		echo " - {$name}(): \\Model\\{$origClassName}()\r\n";
		$file .= <<<EOF


	/**
	 * Gets all $classNamePlural relating to this model by the field {$key[2]}
	 * 
	 * @return array
	 */	
	public function {$name}() {
		return \\Model\\{$origClassName}::getBy{$column}(\$this);
	}
	
EOF;
	}
	
	if (count($models[$table]['multi']) > 1 || false) {
		$name = "";
		$args = "";
		$selector = "";
		$multi_keys = $models[$table]['multi'];
		$multi_keys = \System\Library\StdLib::arrayarray_order($multi_keys, 1);		
		foreach ($multi as $col=>$details) {
			$name .= ($cn = getClassName($details[1]));
			$selector .= "\"{$details[0]}\"=>\${$details[1]}->{$details[2]}, ";
			$args .= "$cn \${$details[1]}, ";
			var_dump($col, $details);
			//die();
		}
		$args = substr($args, 0, -2);
		$selector = substr($selector, 0, -2);
		echo "*** - getBy".$name."($args): self::getByAttributes(array($selector))\r\n";
		
		$file .= <<<EOF
		
		
	public function getBy{$name}($args) {
		return self::getByAttributes(array($selector));
	}
	
EOF;
	}

	echo " - bubbleUpdateResult(\$update_result)\r\n";
	$file .= <<<EOF

	public function bubbleUpdateResult(\$update_result, \$loop_control=array()) {
		if (in_array(get_class(), \$loop_control)) {
			return;
		}
		\$loop_control[] = get_class();
		\$update_result->module = get_class();
		\$c = get_class();
		\$update_result->module_table = \$c::getTable();
		
		\Library\RTCQueue::Send("/model/".self::getTable()."/{\$this->id}", \$update_result);
EOF;
	foreach ($models[$table]['multi'] as $col=>$key) {
		//if ($key[2] != $models[$key[1]]['key'][0] || count($models[$key[1]]['key']) != 1) {
		//	continue;
		//}
		$className = getClassName($key[1]);
		$selfName = getClassName($table);
		$column = getClassName($key[0]);
		$file .= "
		\$obj = \$this->get{$column}();
		if (\$obj) {
			\$obj->bubbleUpdateResult(\$update_result, \$loop_control);
		}\r\n";
	}

	$file .= <<<EOF
	}
EOF;
	$file .= <<<EOF

}
EOF;
	file_put_contents($f = __DIR__."/../../framework/system/model/".($c = getClassName($table)).".php", $file);
	if (!file_exists($f = __DIR__."/../../framework/application/model/".getClassName($table).".php")) {
		
		$file = <<<EOF
<?php
/**
 * Autogenerated model for \\Model\\$className
 * 
 * PHP version 5.4
 *
 * @category Model
 * @package  Boiler
 * @author   Will Tinsdeall <will.tinsdeall@mercianlabels.com>
 * @license  GNU v3.0 http://www.gnu.org/licenses/gpl-3.0.txt
 * @link     http://www.mercianlabels.com
 *
 */
namespace Model;

/**
 * Autogenerated model for \\Model\\$className
 * 
 * PHP version 5.4
 *
 * @category Model
 * @package  Boiler
 * @author   Will Tinsdeall <will.tinsdeall@mercianlabels.com>
 * @license  GNU v3.0 http://www.gnu.org/licenses/gpl-3.0.txt
 * @version  GIT: \$Id$
 * @link     http://www.mercianlabels.com
 *
 */
class $c extends \\System\\Model\\{$c} {
}
EOF;
		echo "CREATING $f\r\n";
		file_put_contents($f, $file);
	} else {
		echo "EXISTS $f\r\n";
	}
}
//var_dump($models);