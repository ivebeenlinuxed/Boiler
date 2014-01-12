<?php
namespace Library\Widget;

class APITable {
	public $class;
	
	public $columns = array();
	
	public $data;
	
	public $page_size = 10;
	public $current_page = 0;
	public $num_rows = 0;
	
	public $filters = array();
	
	public function __construct($class) {
		$this->class = $class;
		$db = $class::getDB();
		$this->query = $db->Select($class);
	}
	
	public function addColumn($name, $column, $link=false, $search=true, $show=true) {
		$this->columns[] = array($name, $column, $link, $search, $show);
	}
	
	public function Render() {
		\Core\Router::loadView("widget/apitable", array("controller"=>$this));
	}
}