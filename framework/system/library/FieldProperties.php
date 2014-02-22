<?php
namespace System\Library;

class FieldProperties {
	const VISIBILITY_SHOW = 0x04;
	const VISIBILITY_HIDDEN = 0x02;
	const VISIBILITY_PRIVATE = 0x01;
	public $widget = null;
	public $widget_data = array();
	public $title = null;
	public $visibility = 4;
	
	public function __construct() {
		$this->widget = \Library\Widget\Widget::TEXT;
	}
}
