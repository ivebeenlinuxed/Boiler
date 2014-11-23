<input is="date-widget" class="form-control"<?php 
echo $controller->getDataFields(false);

if ($controller->result) {
	echo " data-result='{$controller->result}'";
	echo " value='".$controller->getPlainTextResult()."'";
}
?> />