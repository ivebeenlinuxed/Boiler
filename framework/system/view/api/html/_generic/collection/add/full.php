<?php 
$new = $class::Create(array());
$key = $class::getPrimaryKey()[0];
header("Location: /api/{$table}/{$new->$key}");
?>