<?php
namespace Library\Widget\Complex;

class WYSIWYG extends \Library\Widget\Widget {
	
	public function Render() {
		if ($this->edit_mode) {
			\Core\Router::loadView("widget/complex/wysiwyg", array("controller"=>$this));
		} else {
			$this->RenderEditlessSpan($this->getPlainTextResult());
		}
	}
	
	
}