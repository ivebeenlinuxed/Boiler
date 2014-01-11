<?php
namespace Library\Widget\Html;

class Text extends \Library\Widget\Widget {
	public $result = "";
	public function Render() {
		if ($this->edit_mode) {
			echo "<input type='text' class='form-control' data-table='{$this->table}' data-field='{$this->field}' data-id='{$this->id}' value='{$this->result}'{$this->RenderDataFields()} />";
		} else {
			echo "<span data-table='{$this->table}' data-field='{$this->field}' data-id='{$this->id}'>{$this->result}</span>";
		}
	}
	
	public function setResult($result) {
		$this->result = $result;
	}
}
