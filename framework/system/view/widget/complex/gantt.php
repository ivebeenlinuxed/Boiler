<div class="gantt" data-snapseconds="<?php echo $controller->getSnapSeconds() ?>" data-mindrag="<?php echo $controller->getSnapSeconds() ?>">
<div class="btn-group pull-right">
<a class="btn btn-default" href="<?php echo $controller->previous_href ?>">Previous</a>
<a class="btn btn-default" href="<?php echo $controller->now_href ?>">Now</a>
<a class="btn btn-default" href="<?php echo $controller->next_href ?>">Next</a><br /><br />
</div>
<ul class="nav nav-tabs">
<?php 
foreach ($controller->getTabDates() as $j=>$date) {
	$d = new DateTime("@".$date);
?>
<li class="<?php echo ($controller->isMajorToday($date) || ($j==0 && !$controller->dateInDisplayPeriod(time())))? "active" : "" ?>"><a href="#day<?php echo $j ?>" data-toggle="tab"><?php echo $d->format($controller->getMajorFormat()) ?></a></li>
<?php
}
?>
</ul>

<div class="tab-content">

<?php 
function render_labels($item, $level=0) {
	?>
	<div>
	<?php
	if (count($item->children) > 0) {
	?><strong><?php
	}
	?>
	<?php
	echo str_repeat("&nbsp;", $level*3).$item->name;
	
	if (count($item->children) > 0) {
	?></strong><?php 
	}
	?>
	</div>
	<?php
	if (count($item->children)) {
		$level++;
		foreach ($item->children as $child) {
			render_labels($child, $level);
		}
	}
}

function render_bars($item, $major_interval, $controller) {
?>
<div class="row-fluid gantt-row<?php
if (count($item->children) > 0) {
	echo " gantt-title";
}
?>" <?php 
if ($item->table && $item->field_start && $item->field_end && $item->field_id) {
	?>
	data-table="<?php echo $item->table ?>"
	data-startfield="<?php echo $item->field_start ?>"
	data-endfield="<?php echo $item->field_end ?>"
	data-idfield="<?php echo $item->field_id ?>"
	<?php
}

if ($item->preset) {
	?>
	data-preset="<?php echo htmlentities(json_encode($item->preset)) ?>"
	<?php
}
?>>
	<?php 
	$percent_per_second = 100/($controller->getMajorEnd($major_interval)-$major_interval);
	$callback = $item->bar_callback;
	if (is_callable($callback)) {
		foreach ($callback($major_interval, $controller->getMajorEnd($major_interval)) as $bar) {
			?><div<?php 
			if ($bar->id) {
				echo " data-id='{$bar->id}'";
			}
			?> class="gantt-overlay progress" style="margin-left: <?php 
			echo ($bar->start-$major_interval)*$percent_per_second;
			?>%; width: <?php 
			echo ($bar->end-$bar->start)*$percent_per_second;
			?>%;"><div class="progress-bar" style="width: 100%; <?php echo $bar->style ?>"><?php echo $bar->label ?></div></div><?php
		}
	}
	if (count($item->children) > 0) {
		render_time_bars($major_interval, $controller);
	} else {
		render_empty_bars($controller);
	}
	?>
</div>
<?php
	if (count($item->children) > 0) {
		foreach ($item->children as $child) {
			render_bars($child, $major_interval, $controller);
		}
	}
}

function render_time_bars($major_interval, $controller) {
	foreach ($controller->getMinorDates($major_interval) as $date) {
	?><div class="gantt-cell" style="width: <?php echo 100/$controller->getMinorIntervalCount() ?>%;"><?php echo date($controller->getMinorFormat(), $date)?></div><?php
	}
}

function render_empty_bars($controller) {
	$minor_count = $controller->getMinorIntervalCount();
	for ($i=0; $i<$minor_count; $i++) {
		?><div class="gantt-cell" style="width: <?php echo 100/$minor_count ?>%;">&nbsp;</div><?php
	}
}




foreach ($controller->getTabDates() as $j=>$date) {
	$d = new DateTime("@".$date);
?>
<div class="tab-pane <?php echo $controller->isMajorToday($date) || ($j==0 && !$controller->dateInDisplayPeriod(time()))? "active" : ""?>" data-timestart="<?php echo $date ?>" data-timeend="<?php echo $controller->getMajorEnd($date) ?>" id="day<?php echo $j ?>">
	<div class="row-fluid">
		<div class="gantt-col-label">
			<div><?php echo $d->format($controller->getMajorFormat()) ?></div>
			<?php 
			foreach ($controller->item_tree->children as $child) {
				render_labels($child);
			}
			?>
		</div>
		<div class="gantt-col-bars">
			<div class="row-fluid gantt-title">
			<?php render_time_bars($date, $controller) ?>
			</div>
			<?php 
			foreach ($controller->item_tree->children as $child) {
				render_bars($child, $date, $controller);
			}
			?>
			<?php 
			
			?>
		</div>
	</div>
</div>
<?php 
}
?>
</div>
</div>
