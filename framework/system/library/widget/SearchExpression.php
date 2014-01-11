<?php
namespace Library\Widget;

class SearchExpression {
	public $columns;
	
	public $table;
	
	public $filters  = array();
	
	public $id;
	
	public function __construct($table) {
		$this->id = uniqid("search-expression-");
		$this->table = $table;
	}
	
	public function addColumn($name, $field) {
		$this->columns[] = array($name, $field);
	}
	
	public function Render() {
		\Core\Router::loadView("widget/search_expression", array("controller"=>$this));
	}
}