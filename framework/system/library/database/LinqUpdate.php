<?php
namespace Library\Database;
class LinqUpdate implements LinqQuery {
	public function __construct($db, $obj, $name="t") {
		if (!is_a($db, "\Library\Database\LinqDB")) {
			throw new LinqException("Parameter 1 is not a LinqDB");
		}
		$this->DB = $db;
		if (class_exists($obj) && \System\Library\StdLib::is_interface_of($obj,"\Library\Database\LinqObject")) {
			$this->obj = $obj;
			$this->name = $name;
		} else {
			throw new LinqException("Not a LINQ object");
		}

		$this->start = false;
		$this->end = false;

		$this->set = array();
		$this->filter = false;
		$this->order = false;
		$this->join = array();
	}

	function Exec() {
		return $this->DB->Exec($this->getSQL());
	}

	function setFilter($f) {
		if (!is_subclass_of($f, "\Library\Database\LinqEquality")) {
			die("Must be a LINQ Equality");
		} else {
			$f->name = trim($this->getTable(),"`");
			$this->filter = $f;
		}
		return $this;
	}


	public function getTable() {
		$o = $this->obj;
		return "`".$o::getTable(true)."`";
	}

	public function addSet($field, $data=false) {
		$this->set[$field] = $data;
		return $this;
	}

	public function getFilters() {
		if (count($this->filter) > 0) {
			if (!$this->filter) {
				$f = $this->DB->getAndFilter();
			} else {
				$f = $this->filter;
			}
		}


		if (count($this->join) > 0) {
			foreach ($this->join as $j) {
				$f->subEq($j[2]->getFilters());
			}
		}
		return $f;
	}

	function setLimit($end) {
		if (!is_int($end)) {
			throw LinqException("Limit must be integer");
		}
		$this->end = $end;
		return $this;

	}

	function setOrder($name, $asc=false) {
		$this->order = $name;
		if ($asc) {
			$this->orderAsc = true;
		} else {
			$this->orderAsc = false;
		}
		return $this;
	}

	public function getSQL() {
		$o = $this->obj;
		$sql = "UPDATE ";
		$sql .= " ".$this->getTable();

		$sql .= " SET ";

		if (count($this->set) == 0) {
			throw new LinqException('No fields to set');
		}

		foreach ($this->set as $Key=>$Data) {
			if ($Data === false) {
				$sql .= "`".$this->DB->escape_string($Key)."`=DEFAULT, ";
			} elseif ($Data === null) {
				$sql .= "`".$this->DB->escape_string($Key)."`=NULL, ";
			} else {
				$sql .= "`".$this->DB->escape_string($Key)."`='".$this->DB->escape_string($Data)."', ";
			}
		}
		$sql = substr($sql, 0, -2);


		$where = $this->getFilters()->getSQL();
		if ($where != "") {
			$sql .= " WHERE ".$where;
		}

		if ($this->order !== false) {
			$sql .= " ORDER BY `".$this->DB->escape_string($this->order)."`";
			if ($this->orderAsc) {
				$sql .= " ASC";
			} else {
				$sql .= " DESC";
			}
		}


		if ($this->end !== false) {
			$sql .= " LIMIT {$this->end}";
		}
		return $sql;
	}
}
?>
