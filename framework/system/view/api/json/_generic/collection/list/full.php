<?php 
$json = array(
		"pagination"=>array(
				"page"=>$controller->page,
				"records"=>$num_rows,
				"records_per_page"=>$controller->page_size,
				"total_pages"=>ceil($controller->num_rows/$controller->page_size),
				"next_page"=>$controller->page+1
		),
		"data"=>$data
);
echo json_encode($json);
?>