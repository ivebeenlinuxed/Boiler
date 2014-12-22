<?php
namespace Controller\Widget;

abstract class Widget {
	public $edit_mode = true;
	public $result = null;
	public abstract function Render();
	
	public function RenderAnonymous() {
		$this->setDataFields(array("table"=>"-", "field"=>"-", "id"=>"temp" . rand ( 1, 10000 )));
		$this->Render();
	}
	
	public function getDataFields() {
		$str = "";
		foreach ($this->data_fields as $field=>$value) {
			$str .= " data-{$field}={$value}";
		}
		return $str;
	}
	
	public function setDataFields($fields) {
		$this->data_fields = $fields;
	}
	
	public function setResult($result) {
		$this->result = $result;
	}
	
	public function getPlainTextResult() {
		return $this->result;
	}
}
