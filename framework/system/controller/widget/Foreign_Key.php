<?php
namespace Controller\Widget;

class Foreign_Key extends Widget {
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
			
			$select = $db->Select($class);
			$select->addCount("c");
			$r = $select->Exec();
			
			$data = null;
			if ($this->data_fields['filter']) {
				$filter = json_decode($this->data_fields['filter'], true);
				if (is_array($filter)) {
					$data = $class::getByAttributes($filter);
				}
			}
			if ($data === null) {
				$data = $class::getAll();
			}
			
			if (
					(isset($this->data_fields['force_browse']) && $this->data_fields['force_browse'] == false)
					|| (!isset($this->data_fields['force_browse']) && (int)$r[0]['c'] < 100)
			) {
				\Core\Router::loadView("widget/html/select", array("id"=>$id, "class"=>$class, "type"=>$class, "rows"=>$data, "controller"=>$this));
			} else {
				\Core\Router::loadView("widget/complex/foreign_key", array("type"=>$table, "controller"=>&$this));
			}
		} else {
			$out = "";
			if ($this->result !== null && (!isset($this->data_fields['link']) || $this->data_fields['link'] != false)) {
				$out .= "<a href='/api/{$table}/{$this->result}'>";
			}
			$out .= $this->getPlainTextResult();
			if ($this->result !== null && (!isset($this->data_fields['link']) || $this->data_fields['link'] != false)) {
				$out .= "</a>";
			}
			echo $out;
		}
	}
	
	public function getPlainTextResult() {
		if ($this->result !== null) {
			$class = $this->class;
			$row = new $class($this->result);
			return $row->getName();
		} else {
			return "---";
		}
	}
	


	public static function loader() {
		\Core\Router::loadView("widget/foreign_key/loader");
	}
}
