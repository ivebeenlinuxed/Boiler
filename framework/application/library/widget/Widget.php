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
	const VENDOR_ITEM=0x400;
	const NUMERIC_UNIT=0x800;
	const ORGANISATION_CONTACT=0x1000;
	const PRODUCT_TYPE=0x2000;
	const PRIORITY=0x4000;
	const FOREIGN_KEY=0x8000;
	const WYSIWYG=0x10000;
	
	public $table;
	public $field;
	public $id;
	
	public $data_fields = array();
	
	public $edit_mode = true;
	
	public $result;
	
	
	
	public function setResult($result) {
		$this->result = $result;
	}
	
	public function setDataFields($fields) {
		$this->data_fields = $fields;
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
			case self::VENDOR_ITEM:
				return new \Library\Widget\Typeahead\VendorItem();
			case self::NUMERIC_UNIT:
				return new \Library\Widget\Complex\NumericUnit();
			case self::ORGANISATION_CONTACT:
				return new \Library\Widget\Typeahead\OrganisationContact();
			case self::PRODUCT_TYPE:
				return new \Library\Widget\Select\ProductType();
			case self::PRIORITY:
				return new \Library\Widget\Select\Priority();
			case self::FOREIGN_KEY:
				return new \Library\Widget\Complex\ForeignKey();
			case self::WYSIWYG:
				return new \Library\Widget\Complex\WYSIWYG();
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
			case self::VENDOR_ITEM:
				return "Vendor Item Typeahead";
			case self::NUMERIC_UNIT:
				return "Numeric Unit Spinner";
			case self::ORGANISATION_CONTACT:
				return "Organisation Contact";
			case self::PRODUCT_TYPE:
				return "Product Type Select";
			
		}
	}
	
	public function RenderEditlessSpan($result) {
		echo "<span data-table='{$this->table}' data-field='{$this->field}' data-id='{$this->id}'>{$result}</span>";
	}
	
	public function getPlainTextResult() {
		return $this->result;
	}
	
	public function getDataFields($writevalue=true) {
		$out = "";
		if ($this->table) {
			$out .= " data-table='{$this->table}'";
		}
		
		if ($this->field) {
			$out .= " data-field='{$this->field}'";
			$out .= " name='{$this->field}'";
		}
		
		if ($this->id) {
			$out .= " data-id='{$this->id}'";
		}
		
		if ($this->result && $writevalue) {
			$out .= " value='{$this->result}'";
		}
		

		foreach ($this->data_fields as $field=>$value) {
			$out .= " data-{$field}={$value}";
		}
		
		return $out;
	}
	
	public abstract function Render();
}