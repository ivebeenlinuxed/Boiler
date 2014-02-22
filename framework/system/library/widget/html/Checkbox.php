<?php
namespace System\Library\Widget\Html;

class Checkbox extends \Library\Widget\Widget {
	public $result = 0;
	public function Render() {
		if ($this->edit_mode) {
			$checked = "";
			if ($this->result == 1) {
				$checked = " checked";
			}
			echo "<input type='checkbox' data-type='checkbox' class='form-control' value='1' data-selected='1' data-deselected='0' {$checked}{$this->getDataFields()} />";
		} else {
			echo "<span data-table='{$this->table}' data-field='{$this->field}' data-id='{$this->id}'>".$this->getPlainTextResult()."</span>";
		}
	}
	
	public function getPlainTextResult() {
		return $result==1? "Y" : "N";
	}
}

