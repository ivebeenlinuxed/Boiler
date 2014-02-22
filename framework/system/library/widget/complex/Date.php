<?php
namespace Library\Widget\Complex;

class Date extends \Library\Widget\Widget {
	
	public function Render() {
		if ($this->edit_mode) {
			\Core\Router::loadView("widget/complex/date", array("controller"=>$this));
		} else {
			$this->RenderEditlessSpan($this->getPlainTextResult());
		}
	}
	
	public function getPlainTextResult() {
		return date("d/m/Y", $this->result);
	}
}