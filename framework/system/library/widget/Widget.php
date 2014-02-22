<?php
namespace System\Library\Widget;


abstract class Widget {
	const NUMERIC=0x01;
	const TEXT=0x02;
	const DATE=0x04;
	const BOOLEAN=0x08;
	const CURRENCY=0x10;
	const FOREIGN_KEY=0x8000;
	const WYSIWYG=0x10000;
	
	public $table;
	public $field;
	public $id;
	
	public $data_fields = array();
	
	public $edit_mode = true;
	
	public $result;
	
	public function RenderDataFields() {
		$str = "";
		foreach ($this->data_fields as $field=>$value) {
			$str .= " data-{$field}={$value}";
		}
		return $str;
	}
	
	
	public function setResult($result) {
		$this->result = $result;
	}
	
	public static function getWidgetByClass($class) {
		switch ($class) {
			case self::NUMERIC:
				return new \Library\Widget\Html\Numeric();
			case self::TEXT:
				return new \Library\Widget\Html\Text();
			case self::DATE:
				return new \Library\Widget\Complex\Date();
			case self::CURRENCY:
				return new \Library\Widget\Complex\Currency();
			case self::BOOLEAN:
				return new \Library\Widget\Html\Checkbox();
		}
	}
	
	public static function getWidgetName($class) {
		switch ($class) {
			case self::USER:
			case self::NUMERIC:
				return "HTML Numeric Spinner";
			case self::TEXT:
				return "HTML Textbox";
			case self::DATE:
				return "Date Selector";
			case self::CURRENCY:
				return "Currency Input";
			case self::BOOLEAN:
				return "Boolean Checkbox";
			
		}
	}
	
	public function RenderEditlessSpan($result) {
		echo "<span data-table='{$this->table}' data-field='{$this->field}' data-id='{$this->id}'>{$result}</span>";
	}
	
	public function getPlainTextResult() {
		return $this->result;
	}
	
	public abstract function Render();
}