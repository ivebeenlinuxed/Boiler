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

	public $filter;

	public function __construct($db, $obj, $name="t") {
		if (!is_a($db, "\Library\Database\LinqDB")) {
			throw new LinqException("Parameter 1 is not a LinqDB");
		}
		$this->db = $db;
		if ((is_object($obj) && (is_a($obj, "\Library\Database\LinqSelect") || is_a($obj, "\Library\Database\LinqUnion"))) || (class_exists($obj) && \System\Library\StdLib::is_interface_of($obj, "\Library\Database\LinqObject"))) {
			$this->obj = $obj;
			$this->name = $name;
		} elseif (class_exists($obj, true)) {
			throw new LinqException("Not a LINQ object");
		} else {
			$this->obj = $obj;
			$this->name = $name;
		}



		$this->fields = array();
		$this->filter = false;
		$this->join = array();
		$this->group = false;
		$this->order = array();
	}

	public function Select($name="t") {
		return new LinqSelect($this->db, $this, $name);
	}
	
	
	/**
	 * Gets a filter whos conjunction is AND
	 * 
	 * @return \Library\Database\LinqAND
	 */
	public function getAndFilter() {
		return $this->db->getAndFilter();
	}
	
	/**
	 * Gets a filter whos conjunction is OR
	 *
	 * @return Library::Database::LinqOR
	 */
	public function getOrFilter() {
		return $this->db->getOrFilter();
	}
	
	/**
	 * Gets a database FROM section
	 *
	 * @return string
	 */
	public function getFrom() {
		if (!is_object($this->obj) && class_exists($this->obj) && \System\Library\StdLib::is_interface_of($this->obj, "\Library\Database\LinqObject")) {
			$o = $this->obj;
			return pg_escape_identifier($o::getTable(true));
		} else {
			return "(".$this->obj->getSQL().") AS ".$this->name;
		}
	}
	
	/**
	 * Gets the database table
	 */
	public function getTable() {
		if (!is_object($this->obj) && class_exists($this->obj) && \System\Library\StdLib::is_interface_of($this->obj, "\Library\Database\LinqObject")) {
			$o = $this->obj;
			return pg_escape_identifier($o::getTable(true));
		} else {
			return pg_escape_identifier($this->name);
		}
	}
	
	/**
	 * Gets the fields to be selected in a SELECT query
	 * 
	 * @return string
	 */
	public function getSelects() {
		$sql = "";

		if (count($this->fields) > 0) {
			foreach ($this->fields as $field) {
				$sql .= $field[0];
				if ($field[1] != null) {
					$sql .= " AS ".pg_escape_identifier($field[1]);
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
	
	
	/**
	 * Get the joins on a query
	 * 
	 * @return string
	 */
	public function getJoins() {
		$sql = "";
		if (count($this->join) > 0) {
			foreach ($this->join as $j) {
				$sj = $j[2];
				$sql .= " ".$j[0]." JOIN ".$sj->getFrom()." ON ".$sj->getTable().".".pg_escape_literal($j[3])."=".$this->getTable().".".pg_escape_literal($j[1]);
				$sql .= $sj->getJoins();
			}


		}
		return $sql;
	}

	/**
	 * Add the filters to the query
	 * 
	 * @return Library::Database::LinqEquality
	 */
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
	
	/**
	 * Gets the SQL string of the query
	 * 
	 * @see Library::Database::LinqQuery.getSQL()
	 * @return string The SQL Query to be executed
	 */
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
				$sql .= " GROUP BY ".$this->getTable().".".pg_escape_literal($this->group[0]);
			} else {
				$sql .= " GROUP BY ".pg_escape_literal($this->group[0]);
			}
		}
		//if ($this->filter) {
		//	$sql .= " WHERE ".$this->filter->getSQL();
		//}
		if (count($this->order) > 0) {
			if ($this->order[0][0] === false) {
				$sql .= " ORDER BY RAND()";
			} elseif (isset($this->order[0][0])) {
				$sql .= " ORDER BY ";
				foreach ($this->order as $cols) {
					$sql .= pg_escape_identifier($cols[0]);
					if ($this->orderAsc) {
						$sql .= " {$cols[1]} ";
					} else {
						$sql .= " {$cols[1]} ";
					}
					$sql .= ", ";
				}
				$sql = substr($sql, 0, -2);
			}
		}

		if ($this->start !== false) {
			$sql .= " OFFSET {$this->start}";
		}
		
		if ($this->end !== false) {
			$sql .= " LIMIT {$this->end}";
		}
		
		return $sql;
	}
	
	/**
	 * Add a join query
	 * 
	 * @param string                        $field   The field in this query which to join
	 * @param \Library\Database\LinqSelect $select  The Select query to join to
	 * @param string                        $foreign The key in the joined query to partner with
	 * 
	 * @return \Library\Database\LinqSelect
	 */
	function joinLeft($field, $select, $foreign) {
		if ($select instanceof LinqSelect) {
			$this->join[] = array("LEFT", $field, $select, $foreign);
		} else {
			die("Not valid table");
		}
		return $this;
	}
	
	/**
	 * Add a join query
	 *
	 * @param string                        $field   The field in this query which to join
	 * @param \Library\Database\LinqSelect  $select  The Select query to join to
	 * @param string                        $foreign The key in the joined query to partner with
	 * 
	 * @return \Library\Database\LinqSelect
	 */
	function joinRight($field, $select, $foreign) {
		if ($select instanceof \Library\Database\LinqSelect) {
			$this->join[] = array("RIGHT", $field, $select, $foreign);
		} else {
			die("Not valid table");
		}
		return $this;
	}
	
	/**
	 * Adds a field to select
	 * 
	 * @param string $f  The field in the database to select
	 * @param string $as The returned name of the field
	 * 
	 * @return \Library\Database\LinqSelect
	 */
	function addField($f, $as=null) {
		if ($f != "*") {
			$f = pg_escape_identifier($f);
		}
		$this->fields[] = array($this->getTable().".".$f, $as);
		return $this;
	}
	
	/**
	 * Get the full name of the field, including the table/view name
	 * 
	 * @param string $f the field to get full name of
	 * 
	 * @return string
	 */
	function getFullName($f) {
		if ($f != "*") {
			$f = pg_escape_literal($f);
		}
		return $this->getTable().".".$f;
	}
	
	/**
	 * Adds a select, bypassing the escaping and cleansing routines
	 * 
	 * @param string $sum The raw query to select
	 * @param string $as  Name of variable result should be returned as
	 * 
	 * @return \Model\Database\LinqSelect
	 */
	function addRaw($sum, $as) {
		$this->fields[] = array($sum, pg_escape_string($as));
		return $this;
	}
	
	/**
	 * Adds a string of filters to the select
	 * 
	 * @param Library::Database::LinqEquality $f The filter to add
	 * 
	 * @return Model::Database::LinqSelect
	 */
	function setFilter($f) {
		if (!is_subclass_of($f, "\Library\Database\LinqEquality")) {
			throw new DBException("Must be a LINQ Equality");
		} else {
			$f->setName(trim($this->getTable(),"`"));
			$this->filter = $f;
		}
		return $this;
	}
	
	/**
	 * Adds a count to the select
	 * 
	 * @param string $field The returning name of the field
	 * @param string $name  Optional name of the column which to count unique values of
	 * 
	 * @return \Model\Database\LinqSelect
	 */
	function addCount($field, $name="*") {
		if ($name != "*") {
			$name = pg_escape_literal($name);
		}
		$this->fields[] = array("COUNT(".$name.")", pg_escape_string($field));
		return $this;
	}
	
	/**
	 * Adds a MAX value to the select
	 *
	 * @param string $field The returning name of the field
	 * @param string $name  Optional name of the column which to count unique values of
	 *
	 * @return \Model\Database\LinqSelect
	 */
	function addMax($field, $name) {
		$name = pg_escape_literal($name);
		$this->fields[] = array("MAX(".$name.")", pg_escape_string($field));
		return $this;
	}
	
	/**
	 * Adds a MIN value to the select
	 *
	 * @param string $field The returning name of the field
	 * @param string $name  Optional name of the column which to count unique values of
	 *
	 * @return \Model\Database\LinqSelect
	 */
	function addMin($field, $name) {
		$name = pg_escape_literal($name);
		$this->fields[] = array("MIN(".$name.")", pg_escape_string($field));
		return $this;
	}
	
	/**
	 * Adds a average value of a column to the select
	 *
	 * @param string $field The returning name of the field
	 * @param string $name  Optional name of the column which to count unique values of
	 *
	 * @return \Model\Database\LinqSelect
	 */
	function addAvg($field, $name) {
		$name = pg_escape_literal($name);
		$this->fields[] = array("AVG(".$name.")", pg_escape_string($field));
		return $this;
	}
	
	
	/**
	 * Adds a summed value of a column to the select
	 *
	 * @param string $field The returning name of the field
	 * @param string $name  Optional name of the column which to count unique values of
	 *
	 * @return \Model\Database\LinqSelect
	 */
	function addSum($field, $name) {
		$name = pg_escape_string($name);
		$this->fields[] = array("SUM(".$name.")", pg_escape_string($field));
		return $this;
	}
	
	/**
	 * Sets the LIMIT part of the query
	 * 
	 * @param int $start Starting position of the query
	 * @param int $end   Length of the query
	 * 
	 * @return \Model\Database\LinqSelect
	 */
	function setLimit($start, $end) {
		if (!is_int($start) || !is_int($end)) {
			throw new DBException("Limit must be integer");
		}
		$this->start = $start;
		$this->end = $end;
		return $this;

	}
	
	/**
	 * Set the "GROUP BY" Parameter
	 * 
	 * @param string $name Field or expression to group by
	 * @param int    $raw  Whether the value should bypass escaping and cleansing
	 * 
	 * @return \Library\Database\LinqSelect
	 */
	function setGroup($name, $raw=false) {
		$this->group = array($name, $raw);
		return $this;
	}
	
	
	/**
	 * Set the "ORDER BY" Parameter
	 * 
	 * @param string  $name Name of the field to order by
	 * @param boolean $asc  Whether to sort Ascending or not
	 * 
	 * @return \Library\Database\LinqSelect
	 */
	function setOrder($name, $asc=false) {
		return $this->addOrder($name, $asc);
	}
	
	function addOrder($name, $asc=false) {
		$this->order[] = array($name, $asc==true? "ASC" : "DESC");
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * 
	 * @see Library::Database::LinqQuery::Exec()
	 */
	public function Exec() {
		return $this->db->Exec($this->getSQL());
	}
	
	public function getResult() {
		return $this->db->getResult($this->getSQL());
	}
}
?>
