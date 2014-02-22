<select class="form-control" data-type='select-immediate' <?php
echo $controller->getDataFields(false);
?>>
<?

foreach ($rows as $row) {
	?>
	<option value="<?php echo $row->$id ?>"<?php echo $controller->result==$row->$id? " selected" : "" ?>><?php echo $row->getName() ?></option>
	<?php
}
?>
</select>