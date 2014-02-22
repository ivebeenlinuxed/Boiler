<?php 
if ($controller->protocol['redirect']) {
	header("Location:  {$controller->protocol['redirect']}");
	return;
}
?>
<div class="api api-full api-list">
<ol class="breadcrumb">
  <li><a href="/">Home</a></li>
  <li class="active">List <?php echo $table ?></li>
</ol>
<a class="btn btn-success" href="/api/<?php echo $table ?>/add">Add</a>
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