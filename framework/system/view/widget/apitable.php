<?php
$rows = $controller->data;
$class = $controller->class;
?>
<div class="pull-right col-sm-4">
	<?php 
	$se = new \Library\Widget\SearchExpression($class);
	foreach ($controller->columns as $column) {
		if ($column[3]) {
			$se->addColumn($column[0], $column[1]);
		}
	}
	$se->filters = $controller->filters;
	$se->Render();

	$where_string = json_encode($se->filters);
	?>
</div>
<table class="table table-striped table-bordered">
<thead>
	<tr>
		<?php 
		foreach ($controller->columns as $column) {
		?>
		<th><?php echo $column[0] ?></th>
		<?php
		}
		?>
	</tr>
</thead>
<tbody>
<?php
foreach ($rows as $row) {
	$key = $class::getPrimaryKey()[0];
	$row = new $class($row[$key]);
	?>
<tr>
	<?php 
	foreach ($controller->columns as $column) {
		?>
		<td><?php
		if ($column[2]) {
		?>
		<a href="/api/<?php echo $class::getTable() ?>/<?php echo $row->$key ?>.html" data-modal-result="<?php echo $row->$key ?>">
		<?php
		}
		$widget = $row->getWidgetByField($column[1]);
		$widget->edit_mode = false;
		$widget->Render();
		
		
		if ($column[2]) {
			?>
			</a>
			<?php	
		}?></td>
		<?php	
	}
	?>
</tr>
<?php 
}
?>
</tbody>
</table>
<div class="row">
	<div class="col-md-4 col-lg-4">
<?php 
$first_page = $controller->current_page-2;
$last_page = $first_page+4;
$total_rows = $controller->num_rows;

$total_pages = ceil($total_rows/$controller->page_size);

if ($total_pages < 5) {
	$first_page = 0;
	$last_page = $total_pages-1;
} else {
	if ($first_page < 0) {
		$first_page = 0;
		$last_page = 4;
	}
	
	if ($last_page > $total_pages-1) {
		$first_page = $total_pages-6;
		$last_page = $total_pages-1;
	}
}

$query = array();
$query['__where'] = $where_string;
$query['__X_PAGE'] = 0;
$url  = "/api/".$class::getTable().".html?";
?>
<ul class="pagination">
	<!-- BACK ARROW -->
  <li <?php 
if ($controller->current_page == 0) {
	echo "class='disabled'";
}
?>><a href="<?php 
if ($controller->current_page == 0) {
	echo "#";
} else {
	$query['__X_PAGE'] = ($controller->current_page-1)."/".$controller->page_size;
	echo $url.http_build_query($query);
}
?>">&laquo;</a></li>
  
  
  <!-- RESET TO START -->
  <?php 
  if ($first_page > 1) {
  	?>
  	<li><a href="<?php
	$query['__X_PAGE'] = "0"."/".$controller->page_size;
	echo $url.http_build_query($query); ?>">1</a></li>
    <li class="disabled"><a href="#">...</a></li>
    <?php 
    }
  ?>
    
    
  <!-- THE NUMBERS -->
  <?php 
  for ($i=$first_page; $i<=$last_page; $i++) {
  ?>
  <li <?php 
  if ($i == $controller->current_page) {
  	echo "class='active'";
  }
  ?>>
  <a href="<?php
	$query['__X_PAGE'] = $i."/".$controller->page_size;
	echo $url.http_build_query($query);
	?>">
  <?php echo $i+1 ?>
  </a>
  
  </li>
  <?php 
  }
  ?>
  
  
  
  
  
  <!-- RESET TO END -->
  <?php
  if ($last_page < $total_pages-2) {
  ?>
    <li class="disabled"><a href="#">...</a></li>
  	<li><a href="<?php
	$query['__X_PAGE'] = ($total_pages-1)."/".$controller->page_size;
	echo $url.http_build_query($query);
  	?>"><?php echo $total_pages ?></a></li>
  <?php 
  }
  
  
  ?>
  <!-- NEXT PAGE -->
    <li <?php 
if ($controller->current_page == $total_pages-1) {
	echo "class='disabled'";
}
?>><a href="<?php 
if ($controller->current_page == $total_pages-1) {
	echo "#";
} else {
	$query['__X_PAGE'] = ($controller->current_page+1)."/".$controller->page_size;
	echo $url.http_build_query($query);
}
?>">&raquo;</a></li>
</ul>
</div>
<div class="col-md-4 col-lg-4">
	<ul class="pagination">
		<li class="<?php echo $controller->page_size==5? "active" : "" ?>"><a href="<?php 
		$query['__X_PAGE'] = $controller->current_page."/5";
		echo $url.http_build_query($query);
		?>">5</a></li>
		<li class="<?php echo $controller->page_size==10? "active" : "" ?>"><a href="<?php 
		$query['__X_PAGE'] = $controller->current_page."/10";
		echo $url.http_build_query($query);
		?>">10</a></li>
		<li class="<?php echo $controller->page_size==20? "active" : "" ?>"><a href="<?php 
		$query['__X_PAGE'] = $controller->current_page."/20";
		echo $url.http_build_query($query);
		?>">20</a></li>
		<li class="<?php echo $controller->page_size==100? "active" : "" ?>"><a href="<?php 
		$query['__X_PAGE'] = $controller->current_page."/100";
		echo $url.http_build_query($query);
		?>">100</a></li>
		<li class="<?php echo $controller->page_size==1000? "active" : "" ?>"><a href="<?php 
		$query['__X_PAGE'] = $controller->current_page."/1000";
		echo $url.http_build_query($query);
		?>">1000</a></li>
	</ul>
</div>
<div class="col-md-4 col-lg-4">
	<ul class="pagination pull-right">
		<li>Total Records: <?php echo $total_rows ?></li>
	</ul>
</div>
</div>