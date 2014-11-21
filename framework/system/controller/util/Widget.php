<?php
namespace Controller\Util;

use Model\DBObject;
class Widget {	
	public function render() {
		$obj = \Model\DBObject::getByString($_GET['table'], $_GET['id']);
		$w = $obj->getWidgetByField($_GET['field']);
		
		unset($_GET['value']);
		$w->setDataFields($_GET);
		$w->Render();
	}
	
	public function misc($id) {
		$w = \Library\Widget\Widget::getWidgetByClass($id);
		$w->setResult($_GET['value']);
		unset($_GET['value']);
		$w->setDataFields($_GET);
		$w->Render();
	}
	
	public function loader($id) {
		 \Library\Widget\Widget::getLoaderByClass($id);
	}
}