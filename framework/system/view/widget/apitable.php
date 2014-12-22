<?php
$rows = $controller->data;
$class = $controller->class;
$key = $class::getPrimaryKey()[0];
$query = array();
$query['__where'] = $_GET['__where'];
$query['__X_PAGE'] = ($controller->current_page)."/".$controller->page_size;
if ($controller->sort) {
	if ($controller->sort_asc) {
		$query['__order'] = json_encode(array(array($controller->sort, "ASC")));
	} else {
		$query['__order'] = json_encode(array(array($controller->sort, "DESC")));
	}
}
$query['__fields'] = json_encode($controller->columns);

$url  = "/api/".$class::getTable().".html?";
?>
<div class="pull-right col-sm-4">
	<?php 
	$se = new \Library\Widget\SearchExpression($class);
	$se->query = $query;
	foreach ($controller->toggle_fields as $column) {
		$se->addColumn($column, $column);
	}
	$se->filters = $controller->filters;
	$se->Render();

	$where_string = json_encode($se->filters);
	?>
</div>
<div class="dropdown pull-left">
  <a data-toggle="dropdown" href="#" class="">Column Selections <span class="caret"></span></a>
  <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
    <?php 
    foreach ($controller->toggle_fields as $field) {
		$new_query = $query;
		$current_fields = $controller->columns;
		if (in_array($field, $current_fields)) {
			$c = "active";
			array_splice($current_fields, array_search($field, $current_fields), 1);
		} else {
			$c = "";
			$current_fields[] = $field;
		}
		$new_query['__fields'] = json_encode($current_fields);
    ?>
    <li class="<?php echo $c ?>"><a href="<?php echo $url.http_build_query($new_query) ?>"><?php echo $field ?></a></li>
    <?php 
    }
    ?>
  </ul>
</div>
<table class="table table-striped table-bordered">
	<thead>
		<tr>
			<?php 
			foreach ($controller->columns as $column) {
			$fp = $class::getFieldPropertiesByColumn($column);

			?>
			<th><?php 
			echo $fp->title;
			$icon = "fa-sort";
			
			$new_query = $query;
			if (count($controller->order) > 0 && $controller->order[0][0] == $column) {
				if ($controller->order[0][1] != "DESC") {
					$icon = "fa-sort-asc";
					$new_query['__order'] = json_encode(array(array("{$column}", "DESC")));
				} else {
					$icon = "fa-sort-desc";
					unset($new_query['__order']);
				}
			} else {
				$new_query['__order'] = json_encode(array(array("{$column}", "ASC")));
			}
			?> <a href="<?php echo $url.http_build_query($new_query) ?>"><i class="fa <?php echo $icon ?>"></i></a></th>
			<?php
		}
		?>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($rows as $row) {
	?>
		<tr>
			<?php 
			foreach ($controller->columns as $column) {
		?>
			<td><?php
			if ($key == $column) {
		?> <a
				href="/api/<?php echo $class::getTable() ?>/<?php echo $row->$key ?>.html"
				data-modal-result="<?php echo $row->$key ?>"> <?php
		}
		$widget = $row->getWidgetByField($column);
		$widget->edit_mode = false;
		$widget->Render();


		if ($key == $column) {
			?>
			</a> <?php	
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


?>
		<ul class="pagination">
			<!-- BACK ARROW -->
			<li
			<?php
			if ($controller->current_page == 0) {
	echo "class='disabled'";
}
?>><a
				href="<?php 
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
			<li><a
				href="<?php
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
			<li
			<?php
			if ($i == $controller->current_page) {
  	echo "class='active'";
  }
  ?>><a
				href="<?php
	$query['__X_PAGE'] = $i."/".$controller->page_size;
	echo $url.http_build_query($query);
	?>"> <?php echo $i+1 ?>
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
			<li><a
				href="<?php
	$query['__X_PAGE'] = ($total_pages-1)."/".$controller->page_size;
	echo $url.http_build_query($query);
	?>"><?php echo $total_pages ?> </a></li>
			<?php 
  }


  ?>
			<!-- NEXT PAGE -->
			<li
			<?php
			if ($controller->current_page == $total_pages-1) {
	echo "class='disabled'";
}
?>><a
				href="<?php 
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
			<li class="<?php echo $controller->page_size==5? "active" : "" ?>"><a
				href="<?php 
		$query['__X_PAGE'] = $controller->current_page."/5";
		echo $url.http_build_query($query);
		?>">5</a></li>
			<li class="<?php echo $controller->page_size==10? "active" : "" ?>"><a
				href="<?php 
		$query['__X_PAGE'] = $controller->current_page."/10";
		echo $url.http_build_query($query);
		?>">10</a></li>
			<li class="<?php echo $controller->page_size==20? "active" : "" ?>"><a
				href="<?php 
		$query['__X_PAGE'] = $controller->current_page."/20";
		echo $url.http_build_query($query);
		?>">20</a></li>
			<li class="<?php echo $controller->page_size==100? "active" : "" ?>"><a
				href="<?php 
		$query['__X_PAGE'] = $controller->current_page."/100";
		echo $url.http_build_query($query);
		?>">100</a></li>
			<li class="<?php echo $controller->page_size==1000? "active" : "" ?>"><a
				href="<?php 
		$query['__X_PAGE'] = $controller->current_page."/1000";
		echo $url.http_build_query($query);
		?>">1000</a></li>
		</ul>
	</div>
	<div class="col-md-4 col-lg-4">
		<ul class="pagination pull-right">
			<li>Total Records: <?php echo $total_rows ?>
			</li>
		</ul>
	</div>
</div>
