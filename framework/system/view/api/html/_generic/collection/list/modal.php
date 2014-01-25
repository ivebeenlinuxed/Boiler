<div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">List <?php echo $table ?></h4>
      </div>
      <div class="modal-body">
		<a class="btn btn-success" href="/<?php echo $table ?>/add">Add</a>
<?php 
$t = new \Library\Widget\APITable($class);
$t->page_size = $controller->page_size;
$t->current_page = $controller->page;
$t->data = $data;
$t->filters = $controller->searchParams;
$t->num_rows = $num_rows;

$key = $class::getPrimaryKey()[0];

foreach ($class::getDBColumns() as $col) {
	$t->addColumn($col, $col, $col==$key? true : false);
}
$t->Render();
?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->