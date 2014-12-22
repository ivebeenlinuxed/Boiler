<?php

namespace Controller\Widget;

class Text extends Widget {
	public function loader() {
		\Core\Router::loadView ( "widget/text/loader" );
	}
	
	public function Render() {
		if ($this->edit_mode) {
			echo "<input is='text-widget' value=\"" . htmlentities ( $this->result ) . "\" {$this->getDataFields()} />";
		} else {
			echo "<span {$this->getDataFields()}>" . htmlentities ( $this->result ) . "</span>";
		}
	}
	
	
}
