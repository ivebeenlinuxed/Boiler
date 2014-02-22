<?php
namespace Library\Widget\Complex;

class ForeignKey extends \Library\Widget\Widget {
	public $class;
	public $name;
	public $id;
	
	public function setDataFields($fields) {
		parent::setDataFields($fields);
		$this->class = "\\Model\\".\System\Library\Lexical::getClassName($this->data_fields['table']);
	}
	
	public function Render() {
		$class = $this->class;
		$table = $this->data_fields['table'];
		if ($this->edit_mode) {
			$db = $class::getDB();
			
			$id = $class::getPrimaryKey()[0];
			$this->id = $id;
			
			$select = $db->Select($class);
			$select->addCount("c");
			$r = $select->Exec();
			if ((int)$r[0]['c'] < 30) {
				\Core\Router::loadView("widget/html/select", array("id"=>$id, "class"=>$class, "type"=>$class, "rows"=>$class::getAll(), "controller"=>$this));
			} else {
				\Core\Router::loadView("widget/typeahead/typeahead", array("type"=>$table, "controller"=>&$this));
			}
		} else {
			$out = "";
			if ($this->result && (!isset($this->data_fields['link']) || $this->data_fields['link'] != false)) {
				$out .= "<a href='/api/{$table}/{$this->result}'>";
			}
			$out .= $this->getPlainTextResult();
			if ($this->result && (!isset($this->data_fields['link']) || $this->data_fields['link'] != false)) {
				$out .= "</a>";
			}
			echo $out;
		}
	}
	
	public function getPlainTextResult() {
		if ($this->result) {
			$class = $this->class;
			$row = new $class($this->result);
			return $row->getName();
		} else {
			return "---";
		}
	}
}