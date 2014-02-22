<input type="text" data-type='<?php echo $type ?>-typeahead' class="form-control typeahead<?php
if ($controller->result) {
	echo " valid";
}
?>"<?php
echo $controller->getDataFields(false);

if ($controller->result) {
	echo " data-result='{$controller->result}'";
	echo " value=\"".htmlentities($controller->getPlainTextResult())."\"";
}
?> />