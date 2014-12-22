<?php
if (!defined("BOILER_LOCATION")) {
	$_SERVER['no_run'] = true;
	require_once "../../htdocs/index.php";
}


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

function getModels() {
	$settings = \Core\Router::$settings;
	$d = pg_connect("host={$settings['database']['server']} port={$settings['database']['port']} dbname={$settings['database']['db']} user={$settings['database']['user']} password={$settings['database']['passwd']}");
	
	$q = pg_query("SELECT * FROM information_schema.tables WHERE table_catalog='{$settings['database']['passwd']}' AND table_schema='public' AND table_type != 'VIEW'");
	$models = array();
	while ($data = pg_fetch_assoc()) {
		$models[$data['table_name']] = array("columns"=>array(), "multi"=>array(), "single"=>array(), "key"=>array());
	}
	
	foreach ($models as $table=>$model) {
		$q = pg_query("SELECT * FROM information_schema.columns WHERE table_name='{$table}'");
		while ($data = pg_fetch_assoc($q)) {
			$models[$table]['columns'][$data['column_name']] = $data['data_type'];
		}
		

		$q = pg_query("");
		
		$q = pg_query("SELECT               
  pg_attribute.attname, 
  format_type(pg_attribute.atttypid, pg_attribute.atttypmod) 
FROM pg_index, pg_class, pg_attribute 
WHERE 
  pg_class.oid = '{$table}'::regclass AND
  indrelid = pg_class.oid AND
  pg_attribute.attrelid = pg_class.oid AND 
  pg_attribute.attnum = any(pg_index.indkey)
  AND indisprimary");
		
		
		while ($data = pg_fetch_assoc($q)) {
			$models[$table]['key'][] = $data['attname'];
		}
	}
	
	foreach ($models as $table=>$model) {
		$sQuery = <<<EOF
SELECT
    tc.constraint_name, tc.table_name, kcu.column_name, 
    ccu.table_name AS foreign_table_name,
    ccu.column_name AS foreign_column_name 
FROM 
    information_schema.table_constraints AS tc 
    JOIN information_schema.key_column_usage AS kcu
      ON tc.constraint_name = kcu.constraint_name
    JOIN information_schema.constraint_column_usage AS ccu
      ON ccu.constraint_name = tc.constraint_name
WHERE constraint_type = 'FOREIGN KEY' AND tc.table_name='{$table}';
EOF;
		//FIXME FK doesn't work
		if (false) {
			$q = $d->query($sQuery);
			while ($data = $q->fetch_assoc()) {
				$models[$data['TABLE_NAME']]['multi'][$data['CONSTRAINT_NAME']] = array($data['COLUMN_NAME'], $data['REFERENCED_TABLE_NAME'], $data['REFERENCED_COLUMN_NAME']);
				$models[$data['REFERENCED_TABLE_NAME']]['single'][$data['CONSTRAINT_NAME']] = array($data['REFERENCED_COLUMN_NAME'], $data['TABLE_NAME'], $data['COLUMN_NAME']);
			}
		}
		
		
	}
	
	if (false) {
		foreach ($models as $table=>$model) {
			if (count($model['multi']) == 2 && count($model['columns']) == 2) {
				$models[$table]['link_table'] = true;
			} else {
				$models[$table]['link_table'] = false;
			}
		}
	}
	return $models;
}