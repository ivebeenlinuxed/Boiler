<?php
namespace System\Library\Widget\Html;

class Text extends \Library\Widget\Widget {
	public $result = "";
	public function Render() {
		if ($this->edit_mode) {
			echo "<input type='text' class='form-control' value=\"".htmlentities($this->result)."\" {$this->getDataFields()} />";
		} else {
			echo "<span data-table='{$this->table}' data-field='{$this->field}' data-id='{$this->id}'>".htmlentities($this->result)."</span>";
		}
		}
	
		public function setResult($result) {
		$this->result = $result;
	}
}
