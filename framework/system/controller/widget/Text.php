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
			echo "<span data-table='{$this->table}' data-field='{$this->field}' data-id='{$this->id}'>" . htmlentities ( $this->result ) . "</span>";
		}
	}
	
	
}
