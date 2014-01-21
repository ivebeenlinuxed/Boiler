<div class="container">
WELCOME TO YOUR HOME PAGE
<?php 
include BOILER_LOCATION."/application/model/*";
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