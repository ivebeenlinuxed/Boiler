<?php
namespace Controller\Widget;

class Date extends Widget {
	
	public function Render() {
		if ($this->edit_mode) {
			\Core\Router::loadView("widget/date/render", array("controller"=>$this));
		} else {
			$this->RenderEditlessSpan($this->getPlainTextResult());
		}
	}
	
	public function getPlainTextResult() {
		return date("d/m/Y", $this->result);
	}
	
	public function loader() {
		\Core\Router::loadView("widget/date/loader");
	}
}
