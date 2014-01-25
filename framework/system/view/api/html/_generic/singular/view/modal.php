			<?php 
			if (!isset($edit_mode)) {
	$edit_mode = false;
}
$table = get_class($data);
$key = $table::getPrimaryKey()[0];
?><div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"
				aria-hidden="true">&times;</button>
			<h4 class="modal-title">
				View
				<?php echo $table::getTable() ?>
			</h4>
		</div>
		<div class="modal-body">

			<ol class="breadcrumb">
				<li><a href="/">Home</a>
				</li>
				<li><a href="/<?php echo $table::getTable() ?>">List <?php echo $table::getTable() ?>
				</a>
				</li>
				<li class="active"><?php echo ($edit_mode? "Edit" : "View")." #".$data->$key ?>
				</li>
			</ol>
			<?php
			if (!$edit_mode) {
	?>
			<a
				href="/<?php echo $table::getTable() ?>/<?php echo $data->$key ?>/edit"
				class="btn btn-success pull-right">Edit</a>
			<?php
	} else {
	?>
			<a
				href="/<?php echo $table::getTable() ?>/<?php echo $data->$key ?>"
				class="btn btn-success pull-right">Save</a>
			<?php
	}
	?>
			<h2>
				<?php echo $table::getTable() ?>
				#
				<?php echo $data->$key ?>
			</h2>
			<div class="form-horizontal">
			<?php 
			foreach ($table::getDBColumns() as $col) {
	?>
			<div class="<?php echo $edit_mode? "form-group" : "row" ?>">
				<label for="inputEmail3" class="col-sm-2 control-label"><?php echo $col ?>
				</label>
				<div class="col-sm-10">
					<?php 
					$widget = $data->getWidgetByField($col);
					$widget->edit_mode = $edit_mode;
					$widget->Render();
			?>
				</div>
			</div>
			<?php 
	}
	?>
			</div>
		</div>
	</div>
</div>

