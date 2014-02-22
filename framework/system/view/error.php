<?php
\Core\Router::loadView("api/html/_template/full/top");
?>
<div class="alert alert-danger clearfix">
	<img src="/img/icons/tango/status/dialog-error.svg" width="150" height="150" class="pull-left" />
	<h3>Error <?php echo $acl->status_code ?> - <?php echo $acl->getStatusMessage() ?></h3>
	<?php 
	echo $acl->custom_message;
	?>
	<p><?php echo $acl->getHelper() ?></p>
</div>
<?php
\Core\Router::loadView("api/html/_template/full/bottom");