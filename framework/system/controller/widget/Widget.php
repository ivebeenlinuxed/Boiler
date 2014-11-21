<?php
namespace Controller\Widget;

abstract class Widget {
	public abstract function render_field();
	public abstract function render_anonymous();
	
	public function setDataFields($fields) {
		$this->data_fields = $fields;
	}
	
	public function getPlainTextResult() {
		return $this->result;
	}
}