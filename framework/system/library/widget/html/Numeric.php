<?php
namespace Library\Widget\Html;

class Numeric extends \Library\Widget\Widget {
	public $result = 0;
	public function Render() {
		if ($this->edit_mode) {
			echo "<input type='number' class='form-control' value='{$this->result}'{$this->RenderDataFields()} />";
		} else {
			echo "<span data-table='{$this->table}' data-field='{$this->field}' data-id='{$this->id}'>{$this->result}</span>";
		}
	}
	
	public function setResult($result) {
		$this->result = $result;
	}
}

