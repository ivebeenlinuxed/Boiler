<?php
namespace Library\Widget;

class APITable {
	public $table;
	
	public $columns = array();
	
	public $query;
	public $filter;
	
	public $page_size = 10;
	public $current_page = 0;
	
	public function __construct($class) {
		$this->table = $class;
		$db = $class::getDB();
		$this->query = $db->Select($class);
		$this->filter = $this->query->getAndFilter();
	}
	
	public function setQuery($query) {
		$this->query = $query;
		$this->filter = $this->query->filter;
	}
	
	public function addColumn($name, $column, $link=false, $search=true) {
		$this->columns[] = array($name, $column, $link, $search);
		$this->query->addField($column);
	}
	
	public function getFilter() {
		return $this->filter;
	}
	
	public function setFilter(\Library\Database\LinqEquality $eq) {
		$this->filter = $eq;
	}
	
	public function setPageSize($size) {
		$this->page_size = $size;
	}
	
	public function setCurrentPage($page) {
		$this->current_page = $page;
	}
	
	public function Render() {
		$this->query->setFilter($this->filter);
		\Core\Router::loadView("widget/apitable", array("controller"=>$this));
	}
}