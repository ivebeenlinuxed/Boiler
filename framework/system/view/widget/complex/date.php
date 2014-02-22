<input type="text" data-type="date" class="form-control"<?php 
if ($controller->table) {
	echo " data-table='{$controller->table}'";
}
if ($controller->field) {
	echo " data-field='{$controller->field}'";
}
if ($controller->id) {
	echo " data-table='{$controller->id}'";
}

if ($controller->result) {
	echo " data-result='{$controller->result}'";
	echo " value='".date("d/m/Y", $controller->result)."'";
}
?> />