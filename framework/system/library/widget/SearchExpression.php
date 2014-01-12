<?php
namespace Library\Widget;

class SearchExpression {
	public $columns;
	
	public $class;
	
	public $filters  = array();
	
	public $id;
	
	public function __construct($class) {
		$this->id = uniqid("search-expression-");
		$this->class = $class;
	}
	
	public function addColumn($name, $field) {
		$this->columns[] = array($name, $field);
	}
	
	public function Render() {
		\Core\Router::loadView("widget/search_expression", array("controller"=>$this));
	}
}