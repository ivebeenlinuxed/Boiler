<div class="container">
WELCOME TO YOUR HOME PAGE
<?php 
$dir = opendir(BOILER_LOCATION."/application/model/");
while ($d = readdir($dir)) {
	if (!class_exists(\System\Library\Lexical::getClassName(substr($d, 0, -4)))) {
		include BOILER_LOCATION."/application/model/$d";
	}
}
?>
<ul>
<?php
foreach (get_declared_classes() as $class) {
	if (strpos($class, "\\Model\\") === 0) {
		echo "<li><a href='/{$class::getTable()}'>{$class::getTable()}</a></li>";
	}
}
?>
</ul>
</div>