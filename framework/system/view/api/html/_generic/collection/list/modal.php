<div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">List <?php echo $table ?></h4>
      </div>
      <div class="modal-body">
		<a class="btn btn-success" href="/api/<?php echo $table ?>/add?__X_DISPOSITION=modal">Add</a>
        <?php 
$t = new \Library\Widget\APITable($class);
$t->page_size = $controller->page_size;
$t->current_page = $controller->page;
$t->data = $data;
$t->filters = $controller->searchParams;
$t->num_rows = $num_rows;
$t->order = $controller->order;

$key = $class::getPrimaryKey()[0];

foreach ($class::getDBColumns() as $col) {
	$fp = $class::getFieldPropertiesByColumn($col);
	if ($fp->visibility > 1) {
		$t->toggle_fields[] = $col;
	}
	
	if (in_array($col, $controller->fields)) {
		$t->columns[] = $col;
	}
}
$t->Render();
?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->