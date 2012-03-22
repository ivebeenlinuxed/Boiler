<?php
namespace Library\Database;

class LinqSelect implements LinqQuery {
	public $obj;
	public $start = false;
	public $end = false;
	public $distinct = false;
	public $fields;
	public $join;
	public $name;
	public $db;
	public $group;
	public $order;
	public $orderAsc;
	
	public $filter;
	
	public function __construct($db, $obj, $name="t") {
		if (!is_a($db, "\Library\Database\LinqDB")) {
			throw new LinqException("Parameter 1 is not a LinqDB");
		}
		$this->db = $db;
		if ((is_object($obj) && is_a($obj, "\Library\Database\LinqSelect")) || (class_exists($obj) && \System\Library\StdLib::is_interface_of($obj,"\Library\Database\LinqObject")) ) {
			$this->obj = $obj;
			$this->name = $name;
		} elseif (class_exists($obj,"\Library\Database\LinqObject")) {
			throw new LinqException("Not a LINQ object");
		}
		
		
		
		$this->fields = array();
		$this->filter = false;
		$this->join = array();
		$this->group = false;
		$this->order = false;
	}
	
	public function Select($name="t") {
		return new LinqSelect($this->db, $this, $name);
	}
	
	public function getAndFilter() {
		return $this->db->getAndFilter();
	}
	
	public function getOrFilter() {
		return $this->db->getOrFilter();
	}
	
	public function getFrom() {
		if (!is_object($this->obj) && class_exists($this->obj) && \System\Library\StdLib::is_interface_of($this->obj, "\Library\Database\LinqObject")) {
			$o = $this->obj;
			return "`".$o::getTable()."`";
		} else {
			return "(".$this->obj->getSQL().") AS ".$this->name;
		}
	}
	
	public function getTable() {
		if (!is_object($this->obj) && class_exists($this->obj) && \System\Library\StdLib::is_interface_of($this->obj, "\Library\Database\LinqObject")) {
			$o = $this->obj;
			return "`".$o::getTable(true)."`";
		} else {
			return "`".$this->name."`";
		}
	}
	
	public function getSelects() {
		$sql = "";
		
		if (count($this->fields) > 0) {
			foreach ($this->fields as $field) {
				$sql .= $field[0];
				if ($field[1] != null) {
					$sql .= " AS `{$field[1]}`";
				}
				$sql .= ", ";
			}
		}
		
		
		
		if (count($this->join) > 0) {
			foreach ($this->join as $jo) {
				$j = $jo[2];
				$s = $j->getSelects();
				if ($s != "") {
					$sql .= " ".$s.", ";
				}
				/*
				$j = $jo[1];
				if (count($j->fields) == 0) {
					$j->fields[] = "*";
				}
				foreach ($j->fields as $field) {
					if ($field != "*") {
						$field = "`".$field."`";
					}
					$sql .= "`".$j->getTable()."`.".$field.", ";
				}
				*/
			}
		}
		
		if (substr($sql, -2) == ", ") {
			$sql = substr($sql, 0, strlen($sql)-2);
		}
		return $sql;
	}
	
	public function getJoins() {
		$sql = "";
		if (count($this->join) > 0) {
			foreach ($this->join as $j) {
				$sj = $j[2];
				$sql .= " ".$j[0]." JOIN ".$sj->getFrom()." ON ".$sj->getTable().".`".$j[3]."`=".$this->getTable().".`".$j[1]."`";
				$sql .= $sj->getJoins();
			}
			
			
		}
		return $sql;
	}
	
	public function getFilters() {
		if (!$this->filter) {
			$f = $this->db->getAndFilter();
		} else {
			$f = $this->filter;
		}
		foreach ($this->join as $j) {
			$f->subEq($j[2]->getFilters());
		}
		return $f;
	}
	
	public function getSQL() {
		$o = $this->obj;
		$sql = "SELECT";
		if ($this->distinct) {
			$sql .= " DISTINCT";
		}
		$s = $this->getSelects();
		if ($s == "") {
			$sql .= " ".$this->getTable().".*";
		} else {
			$sql .= " ".$s;
		}
		$sql .= " FROM ".$this->getFrom();
		$sql .= $this->getJoins();
		$where = $this->getFilters()->getSQL();
		if ($where != "") {
			$sql .= " WHERE ".$where;
		}
		
		
		
		if ($this->group !== false) {
			if ($this->group[1] == false) {
				$sql .= " GROUP BY ".$this->getTable().".`".$this->db->escape_string($this->group[0])."`";
			} else {
				$sql .= " GROUP BY ".$this->group[0];
			}
		}
		//if ($this->filter) {
		//	$sql .= " WHERE ".$this->filter->getSQL();
		//}
		if ($this->order !== false) {
			if ($this->order === null) {
				$sql .= " ORDER BY RAND()";
			} else {
				$sql .= " ORDER BY `".$this->db->escape_string($this->order)."`";
				if ($this->orderAsc) {
					$sql .= " ASC";
				} else {
					$sql .= " DESC";
				}
			}
		}
		
		if ($this->start !== false) {
			$sql .= " LIMIT {$this->start}";
			if ($this->end !== false) {
				$sql .= ",{$this->end}";
			}
		}
		return $sql;
	}
	
	function joinLeft($field, $select, $foreign) {
		if ($select instanceof LinqSelect) {
			$this->join[] = array("LEFT", $field, $select, $foreign);
		} else {
			die("Not valid table");
		}
		return $this;
	}
	
	function joinRight($field, $select, $foreign) {
		if ($select instanceof \Library\Database\LinqSelect) {
			$this->join[] = array("RIGHT", $field, $select, $foreign);
		} else {
			die("Not valid table");
		}
		return $this;
	}
	
	function addField($f, $as=null) {
		if ($f != "*") {
			$f = "`".$f."`";
		}
		$this->fields[] = array($this->getTable().".".$this->db->escape_string($f), $this->db->escape_string($as));
		return $this;
	}
	
	function getFullName($f) {
		if ($f != "*") {
			$f = "`".$f."`";
		}
		return $this->getTable().".".$this->db->escape_string($f);
	}
	
	function addRaw($sum, $as) {
		$this->fields[] = array($sum, $this->db->escape_string($as));
	}
	
	function setFilter($f) {
		if (!is_subclass_of($f, "\Library\Database\LinqEquality")) {
			die("Must be a LINQ Equality");
		} else {
			$f->setName(trim($this->getTable(),"`"));
			$this->filter = $f;
		}
		return $this;
	}
	
	function addCount($field, $name="*") {
		if ($name != "*") {
			$name = "`".$this->db->escape_string($name)."`";
		}
		$this->fields[] = array("COUNT(".$name.")", $this->db->escape_string($field));
		return $this;
	}
	
	function setLimit($start, $end) {
		if (!is_int($start) || !is_int($end)) {
			throw LinqException("Limit must be integer");
		}
		$this->start = $start;
		$this->end = $end;
		return $this;

	}
	
	function setGroup($name, $raw=false) {
		$this->group = array($name, $raw);
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
	
	function Exec() {
		return $this->db->Exec($this->getSQL());
	}
}
?>
