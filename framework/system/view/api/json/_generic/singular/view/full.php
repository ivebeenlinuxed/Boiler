<?php 
if (is_callable(array($class, "OverrideApi"))) {
	$data = call_user_func(array($class, "OverrideApi"), $data);
}
$json = array(
	"data"=>$data
);
echo json_encode($json);
?>