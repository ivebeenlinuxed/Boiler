<?php
$_SERVER['no_run'] = true;
require "../../htdocs/index.php";
require "common.php";

$models = getModels();

$cn = array("DBObject.php");
foreach ($models as $table=>$model) {
	$cn[] = ($c = getClassName($table)).".php";
}

$dir = opendir("../../framework/system/model/");

while (($f = readdir($dir)) !== false) {
	if ($f == "." || $f == "..") {
		continue;
	} elseif (array_search($f, $cn) !== false) {
		echo "FILE $f: ACTIVE\r\n";
	} else {
		echo "FILE $f: INACTIVE\r\n";
		unlink("../../framework/system/model/".$f);
	}
}