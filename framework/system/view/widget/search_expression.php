<?php 
$class = $controller->class;
?>
<div class="search-control" data-url="/api/<?php echo $class::getTable() ?>.html" id="<?php echo $controller->id ?>">
	<div class="input-group">
      <div class="form-control fake-control"><?php 
      foreach ($controller->filters as $filter) {
      	?>
      	<span class="label label-default" data-json="<?php echo urlencode(json_encode(array($filter[0], $filter[1], $filter[2]))) ?>"><?php echo implode(" ", $filter) ?> <a href="#">&times;</a></span>
      	<?php
      }
      ?></div>
      <span class="input-group-btn">
        <button class="btn btn-default form-btn-search" type="button">Search</button>
      </span>
    </div><!-- /input-group -->
	
	<div class="form-inline search-generator">
		<div class="form-group">
			<select class="form-control form-column"><?php 
			foreach ($controller->columns as $column) {
				?>
				<option value='<?php echo $column[1] ?>'>
					<?php echo $column[0] ?>
				</option>
				<?php
			}
			?>
			</select>
		</div>
		<?php
		foreach ($controller->columns as $id=>$column) {
			?>
		<div class="form-group form-expression <?php echo $id==0? "active" : "" ?>" id="<?php echo $controller->id ?>-column-<?php echo $column[1] ?>">
			<div class="form-group">
			<select class="form-control form-equality">
				<?php 
				if (
$class::getWidgetTypeByColumn($column[1]) == \Library\Widget\Widget::NUMERIC
				|| $class::getWidgetTypeByColumn($column[1]) == \Library\Widget\Widget::DATE
) {
					?>
				<option value=">">&gt;</option>
				<option value="<">&lt;</option>
				<option value=">=">&gt;=</option>
				<option value="<=">&lt;=</option>
				<option value="!=">!=</option>
				<option value="=" selected>=</option>
				<?php
				} else {
					?>
				<option value="!=">!=</option>
				<option value="=" selected>=</option>
				<option value="LIKE">LIKE</option>
				<option value="NOT LIKE" selected>NOT LIKE</option>

				<?php
				}

				?>
			</select>
			</div>
			<div class="form-group form-widget">
			<?php 
			$widget = $class::getWidgetByColumn($column[0]);
			$widget->Render();
			?>
			</div>
		</div>
		<?php
		}
		?>
		<div class="form-group">
			<button class="btn btn-success form-btn-add">Add</button>
		</div>
	</div>

</div>
