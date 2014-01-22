<div class="container">
WELCOME TO YOUR HOME PAGE
<?php 
$dir = opendir(BOILER_LOCATION."/application/model/");
while ($d = readdir($dir)) {
	if ($d == "." || $d == "..") {
		continue;
	}
	if (!class_exists("\\Model\\".\System\Library\Lexical::getClassName(substr($d, 0, -4)))) {
		include BOILER_LOCATION."/application/model/$d";
	}
}
?>
<ul>
<?php
foreach (get_declared_classes() as $class) {
	if (strpos($class, "Model\\") === 0) {
		if ($class == "Model\\DBObject") {
			continue;
		}
		echo "<li><a href='/{$class::getTable()}'>{$class::getTable()}</a></li>";
	}
}
?>
</ul>
</div>