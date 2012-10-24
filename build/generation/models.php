<?php
require_once "common.php";





$models = getModels();

foreach ($models as $table=>$model) {
	$f = "../../framework/system/model/".($c = getClassName($table)).".php";
	
	echo "BUILDING $f\r\n";
	$file = <<<EOF
<?php
namespace System\Model;
	

EOF;
	$className = getClassName($table);
	$pkey = '"'.implode('","', $models[$table]['key']).'"';
	$columnArray = getPHPArray(array_keys($models[$table]['columns']));
	
	$file .= <<<EOF
class $className extends \Model\DBObject {
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
	 * @var boolean \$read changes the table name if a database view is provided for reading, rather than a table
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
		echo " - get{$className}(): \\Model\\$className(\$this->$key[0])\r\n";
		$file .= <<<EOF

		
	/**
	 * Gets all $className associated with this object
	 * 
	 * @return System\Model\$className
	 */
	public function get{$className}() {
		return new \\Model\\$className(\$this->$key[0]);
	}
EOF;
		
		
		echo " - getBy$className($className \$class): self()\r\n";
		$file .= <<<EOF
			
		
	/**
	 * Gets all objects relating to $className
	 * 
	 * @var \$class \Model\$className Get objects relating to this class
	 * 
	 * @return array
	 */		
	public static function getBy$className($className \$class) {
		\$c = get_called_class();
		return \$c::getByAttribute("{$key[0]}", \$class->{$key[2]});
	}
EOF;
	}
	
	foreach ($models[$table]['single'] as $col=>$key) {
		$origClassName = $className = getClassName($key[1]);
		$selfName = getClassName($table);
		
		if ($models[$key[1]]['link_table']) {
			foreach ($models[$key[1]]['multi'] as $t=>$data) {
				if ($data[1] != $table) {
					$className = getClassName($data[1]);
					break;
				}
			}
		}
		
		$classNamePlural = getClassNamePlural($className);
		echo " - get{$classNamePlural}(): \\Model\\{$origClassName}()\r\n";
		$file .= <<<EOF


	public function get{$classNamePlural}() {
		return \\Model\\{$origClassName}::getBy{$selfName}(\$this);
	}
	
EOF;
	}
	
	$file .= <<<EOF

}
EOF;
	file_put_contents($f = __DIR__."/../../framework/system/model/".($c = getClassName($table)).".php", $file);
	if (!file_exists($f = __DIR__."/../../framework/application/model/".getClassName($table).".php")) {
		
		$file = <<<EOF
<?php
namespace Model;

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