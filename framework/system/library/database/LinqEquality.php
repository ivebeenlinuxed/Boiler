<?php
namespace Library\Database;
abstract class LinqEquality {
	protected $fields;
	public $name = "";
	protected abstract function getSymbol();

	const FIELD = 0;
	const VALUE = 1;
	const RAW = 2;

	const SUBQUERY_NONE = 0;
	const SUBQUERY_ANY = 1;
	const SUBQUERY_SOME = 2;
	const SUBQUERY_IN = 3;

	function __construct($db, $obj_name="") {
		$this->name = $obj_name;
		$this->db = $db;
		$this->fields = array();
	}

	function eq($field, $value, $a=self::FIELD, $b=self::VALUE, $c=self::SUBQUERY_NONE) {
		$this->fields[] = array($field, "=", $value, $a, $b, $c);
		return $this;
	}

	function neq($field, $value, $a=self::FIELD, $b=self::VALUE, $c=self::SUBQUERY_NONE) {
		$this->fields[] = array($field, "!=", $value, $a, $b, $c);
		return $this;
	}

	function lt($field, $value, $a=self::FIELD, $b=self::VALUE, $c=self::SUBQUERY_NONE) {
		$this->fields[] = array($field, "<", $value, $a, $b, $c);
		return $this;
	}

	function gt($field, $value, $a=self::FIELD, $b=self::VALUE, $c=self::SUBQUERY_NONE) {
		$this->fields[] = array($field, ">", $value, $a, $b, $c);
		return $this;
	}

	function lteq($field, $value, $a=self::FIELD, $b=self::VALUE, $c=self::SUBQUERY_NONE) {
		$this->fields[] = array($field, "<=", $value, $a, $b, $c);
		return $this;
	}

	function gteq($field, $value, $a=self::FIELD, $b=self::VALUE, $c=self::SUBQUERY_NONE) {
		$this->fields[] = array($field, ">=", $value, $a, $b, $c);
		return $this;
	}

	function isnull($field) {
		$this->fields[] = array($field, "IS", "NULL", null);
		return $this;
	}

	function like($field, $value, $a=self::FIELD, $b=self::VALUE, $c=self::SUBQUERY_NONE) {
		$this->fields[] = array($field, "LIKE", $value, $a, $b, $c);
		return $this;
	}

	function nlike($field, $value, $a=self::FIELD, $b=self::VALUE, $c=self::SUBQUERY_NONE) {
		$this->fields[] = array($field, "NOT LIKE", $value, $a, $b, $c);
		return $this;
	}

	function subEq($eq) {
		$this->fields[] = array(false,false,$eq, -1, self::VALUE);
		return $this;
	}







	function getSQL() {
		$sql = "";
		if (count($this->fields) == 0) {
			return "";
		}
		$obj = "";
		if ($this->name != "") {
			$obj = "`".$this->name."`.";
		}

		foreach ($this->fields as $field) {

			switch ($field[3]) {
				case self::FIELD:
					$field[0] = "$obj`".$this->db->escape_string($field[0])."`";
					break;
				case self::VALUE:
					$field[0] = $this->getValue($field[0]);
					break;
			}
			switch ($field[4]) {
				case self::FIELD:
					if (is_object($field[2])) {
						var_dump($field);
						throw new DBException("Field value cannot be an object");
					}
					$field[2] = "$obj`".$this->db->escape_string($field[2])."`";
					break;
				case self::VALUE:
					$field[2] = $this->getValue($field[2]);
					break;
			}



			if (!($field[0] == "``" || $field[2] == "()")) {

				if ($field[3] === null) {
					$eq = $field[2];
					$e = $eq->getSQL();
					if ($e != "") {
						$sql .= "(".$e.")"." ".$this->getSymbol()." ";
					}
				} else {
					$sql .= $field[0]." ".$field[1]." ".$field[2]." ".$this->getSymbol()." ";
				}
			}

		}
		$sql = substr($sql, 0, strlen($sql)-strlen($this->getSymbol())-1);
		if ($sql == "``  () ") {
			return "";
		}
		return $sql;
	}

	private function getValue($va) { //,$SQ
		$v = "";
		if (is_int($va) || is_bool($va) || is_float($va) || is_string($va)) {
			$v = "'".$this->db->escape_string($va)."'";
		} elseif (is_subclass_of($va, "\Library\Database\LinqQuery")) {
			/*switch ($SQ) {
				case self::SUBQUERY_ANY:
			case self::SUBQUERY_IN:
			case self::SUBQUERY_SOME:
			$v = "";
			}*/
			$v = "(".$va->getSQL().")";
		} elseif (is_subclass_of($va, "\Library\Database\LinqEquality")) {
			$v = "(".$va->getSQL().")";
		} else {
			throw new LinqException("Unknown type: ".gettype($va));
		}
		return $v;
	}

	public function setName($name) {
		$this->name = $name;
		foreach ($this->fields as $field) {
			if (is_object($field[2]) && is_subclass_of($field[2], "\Library\Database\LinqEquality")) {
				$field[2]->setName($name);
			}
		}
	}
}
?>
