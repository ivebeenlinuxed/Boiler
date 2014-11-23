<div is="fk-widget" class="input-group"<?php
		echo $controller->getDataFields (false);
		?> 
		
		>
	<input type="text" class="form-control" disabled value="<?php echo $controller->getPlainTextResult() ?>" data-result="<?php echo $controller->result ?>"
		id="<?php echo ($id = uniqid("data-widget-complex")) ?>"> 
		
		<span class="input-group-btn">
		
			<a href="/api/<?php echo $controller->data_fields['table'] ?>"
			data-type="api-modal" class="btn btn-default"
			data-modal-return="#<?php
			
	echo $id ?>" type="button">Browse</a>
	<?php 
	if ($controller->data_fields['add']) {
	?>
			<button type="button" class="btn btn-default dropdown-toggle"
				data-toggle="dropdown">
				<span class="caret"></span> <span class="sr-only">Toggle Dropdown</span>
			</button>
			<ul class="dropdown-menu" role="menu">
				<li><a href="/api/<?php echo $controller->data_fields['table'] ?>/add">Add</a></li>
			</ul>
	<?php 
	}
	?>
	</span>
</div>
