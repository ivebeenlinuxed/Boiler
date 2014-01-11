<?php
namespace Library\Widget;
use Controller\Api\Material;
abstract class Widget {
	const USER=0x01;
	const MATERIAL=0x02;
	const ORGANISATION=0x04;
	const NUMERIC=0x08;
	const TEXT=0x10;
	const CUTTER=0x20;
	const DATE=0x40;
	const BOOLEAN=0x80;
	const UOM=0x100;
	const CURRENCY=0x200;
	
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
			case self::USER:
				return new \Library\Widget\Typeahead\User();
			case self::MATERIAL:
				return new \Library\Widget\Typeahead\Material();
			case self::ORGANISATION:
				return new \Library\Widget\Typeahead\Organisation();
			case self::NUMERIC:
				return new \Library\Widget\Html\Numeric();
			case self::TEXT:
				return new \Library\Widget\Html\Text();
			case self::CUTTER:
				return new \Library\Widget\Complex\Cutter();
			case self::DATE:
				return new \Library\Widget\Complex\Date();
			case self::CURRENCY:
				return new \Library\Widget\Complex\Currency();
			case self::BOOLEAN:
				return new \Library\Widget\Html\Checkbox();
			case self::UOM:
				return new \Library\Widget\Typeahead\UnitOfMeasure();
		}
	}
	
	public static function getWidgetName($class) {
		switch ($class) {
			case self::USER:
				return "User Typeahead";
			case self::MATERIAL:
				return "Material Typeahead";
			case self::ORGANISATION:
				return "Organisation Typeahead";
			case self::NUMERIC:
				return "HTML Numeric Spinner";
			case self::TEXT:
				return "HTML Textbox";
			case self::CUTTER:
				return "Cutter Selector";
			case self::DATE:
				return "Date Selector";
			case self::CURRENCY:
				return "Currency Input";
			case self::BOOLEAN:
				return "Boolean Checkbox";
			case self::UOM:
				return "Unit of Measure Selector";
			
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