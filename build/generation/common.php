<?php
if (!defined("BOILER_LOCATION")) {
	$_SERVER['no_run'] = true;
	require "../../htdocs/index.php";
}

$d = new mysqli(\Core\Router::$settings['database']['server'], \Core\Router::$settings['database']['user'], \Core\Router::$settings['database']['passwd'], \Core\Router::$settings['database']['db'], \Core\Router::$settings['database']['port']);

function getPHPArray($array) {
	return 'array("'.implode('","', $array).'")';
}

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

function getModels($d) {
	
	$q = $d->query("SHOW TABLES");
	$models = array();
	
	while ($data = $q->fetch_array()) {
		$models[$data[0]] = array("columns"=>array(), "multi"=>array(), "single"=>array(), "key"=>array());
	}
	
	foreach ($models as $table=>$model) {
		$q = $d->query("SHOW COLUMNS IN `$table`");
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
	return $models;
}