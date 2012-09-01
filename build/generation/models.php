<?php
$_SERVER['no_run'] = true;
require "../htdocs/index.php";


function getClassName($table) {
	$name = explode("_", $table);
	$className = "";
	foreach ($name as $n) {
		$className .= ucfirst($n);
	}
	return $className;
}

function getClassNamePlural($table) {
	$name = explode("_", $table);
	$className = "";
	foreach ($name as $c=>$n) {
		if ($c == count($name)-1) {
			$n = \System\Library\Lexical::pluralize($n);
		}	
		$className .= ucfirst($n);
		
	}
	return $className;
}


$d = new mysqli(\Core\Router::$settings['database']['server'], \Core\Router::$settings['database']['user'], \Core\Router::$settings['database']['passwd'], \Core\Router::$settings['database']['db'], \Core\Router::$settings['database']['port']);
$q = $d->query("SHOW TABLES");
$models = array();

while ($data = $q->fetch_array()) {
	$models[$data[0]] = array("columns"=>array(), "multi"=>array(), "single"=>array(), "key"=>array());
}

foreach ($models as $table=>$model) {
	$q = $d->query("SHOW COLUMNS IN $table");
	while ($data = $q->fetch_assoc()) {
		$models[$table]['columns'][$data['Field']] = $data['Type'];
		if ($data['Key'] == "PRI") {
			$models[$table]['key'][] = $data['Field'];
		}
	}
}

foreach ($models as $table=>$model) {
	$sQuery = <<<EOF
SELECT i.TABLE_NAME, k.COLUMN_NAME, i.CONSTRAINT_TYPE, i.CONSTRAINT_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME
FROM information_schema.TABLE_CONSTRAINTS i
LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME
WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY'
AND i.TABLE_SCHEMA = DATABASE()
AND i.TABLE_NAME = '$table'
EOF;
	$q = $d->query($sQuery);
	while ($data = $q->fetch_assoc()) {
		$models[$data['TABLE_NAME']]['multi'][$data['CONSTRAINT_NAME']] = array($data['COLUMN_NAME'], $data['REFERENCED_TABLE_NAME'], $data['REFERENCED_COLUMN_NAME']);
		$models[$data['REFERENCED_TABLE_NAME']]['single'][$data['CONSTRAINT_NAME']] = array($data['REFERENCED_COLUMN_NAME'], $data['TABLE_NAME'], $data['COLUMN_NAME']);
	}
}

foreach ($models as $table=>$model) {
	if (count($model['multi']) == 2 && count($model['columns']) == 2) {
		$models[$table]['link_table'] = true;
	} else {
		$models[$table]['link_table'] = false;
	}
}

foreach ($models as $table=>$model) {
	
	$file = <<<EOF
<?php
namespace System\Model;
	

EOF;
	$className = getClassName($table);
	$pkey = '"'.implode('","', $models[$table]['key']).'"';
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
	* @var \$$column $type
	*/
	public \$$column;
EOF;
	}
	$file .= <<<EOF

	/**
	 * Gets the table name (always returns "$table")
	 * 
	 * @var \$read boolean changes the table name if a database view is provided for reading, rather than a table
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
	foreach ($models[$table]['multi'] as $col=>$key) {
		if ($key[2] != $models[$key[1]]['key'][0] || count($models[$key[1]]['key']) != 1) {
			continue;
		}
		$className = getClassName($key[1]);
		$selfName = getClassName($table);
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
		if ($model['link_table']) {
			foreach ($model['multi'] as $column=>$data) {
				if ($data[1] != $table) {
					$linkTable = getClassName($data[1]);
					break;
				}
			}
			$file .= <<<EOF
			
	/**
	 * Extrapolation of the $className <-> $linkTable via this link table
	 * 
	 * @return array
	 */
	public static function getBy$className($className \$class) {
		\$out = array();
		foreach (self::getByAttribute("{$key[0]}", \$class->{$key[2]}) as \$c) {
			\$out[] = new \\Model\\$linkTable(\$c->{$key[0]});
		}
		return \$out;
	}
EOF;
		} else {
			$file .= <<<EOF
			
	/**
	 * Gets all objects relating to $className
	 * 
	 * @var \$class \Model\$className
	 * 
	 * @return array
	 */		
	public static function getBy$className($className \$class) {
		\$c = get_called_class();
		return \$c::getByAttribute("{$key[0]}", \$class->{$key[2]});
	}
EOF;
		}
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
		$file .= <<<EOF


	public static function get{$classNamePlural}() {
		return \Model\{$origClassName}::getBy{$selfName}(\$this);
	}
	
EOF;
	}
	
	$file .= <<<EOF

}
EOF;
	file_put_contents("../framework/system/model/".($c = getClassName($table)).".php", $file);
	
	if (!file_exists($f = "../framework/application/model/".getClassName($table).".php")) {
		
		$file = <<<EOF
<?php
namespace Model;

class $c extends \\System\\Model\\{$c} {
}
EOF;
		echo "NOT EXIST $f\r\n";
		file_put_contents($f, $file);
	} else {
		echo "EXISTS $f\r\n";
	}
}
//var_dump($models);