<?php 

?>
<div class="input-group" id="<?php echo ($id = uniqid("widget-password")) ?>">
	<input
	<?php 
	if ($controller->table) {
		echo " data-table='{$controller->table}'";
	}
	
	if ($controller->id) {
		echo " data-id='{$controller->id}'";
	}
	
	if ($controller->table) {
		echo " data-id='{$controller->id}'";
	}
	
	
	?>
	 type="password"
		class="form-inline form-control col-sm-9" id="inputPassword"
		placeholder="Password" value="<?php echo $controller->result ?>"> <span
		class="input-group-addon"> <i class="glyphicon glyphicon-eye-open"
		onclick="t=$('#<?php echo $id ?> input'); t.attr('type', t.attr('type')=='password'? 'text' : 'password'); return false;"></i>
		&nbsp;<i class="glyphicon glyphicon-cog" data-toggle="modal"
		data-target="#passgenModal"
		onclick="$('#passgenModal').attr('data-input', 'inputPassword')"></i>
	</span>
</div>
