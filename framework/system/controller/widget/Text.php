<?php

namespace Controller\Widget;

class Text extends Widget {
	public function loader() {
		\Core\Router::loadView ( "widget/text/loader" );
	}
	public function render_field($args = null) {
		if (is_array ( $args )) {
			$this->setDataFields ( $args );
		} else {
			$this->setDataFields ( $_GET );
		}
		if ($this->edit_mode) {
			echo "<input is='text-widget' value=\"" . htmlentities ( $this->result ) . "\" {$this->getDataFields()} />";
		} else {
			echo "<span data-table='{$this->table}' data-field='{$this->field}' data-id='{$this->id}'>" . htmlentities ( $this->result ) . "</span>";
		}
	}
	public function render_anonymous() {
		$this->setDataFields ( array (
				"table" => "-",
				"field" => "-",
				"id" => "temp" . rand ( 1, 10000 ) 
		) );
		if ($this->edit_mode) {
			echo "<input type='text' class='form-control' value=\"" . htmlentities ( $this->result ) . "\" {$this->getDataFields()} />";
		} else {
			echo "<span data-table='{$this->table}' data-field='{$this->field}' data-id='{$this->id}'>" . htmlentities ( $this->result ) . "</span>";
		}
	}
}