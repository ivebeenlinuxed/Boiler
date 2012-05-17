<?php
namespace Library\Database;
class LinqUnion implements LinqQuery {
	private $selectQry;
	
	function __construct($DB) {
		$this->selectQry = array();
		$this->db = $DB;
	}
	
	function addSelect(LinqSelect $s) {
		$this->selectQry[] = $s;
		return $this;
	}
	
	function getSQL() {
		$out = array();
		if (count($this->selectQry) == 0) {
			throw new LinqException("'UNION' must have at least one SELECT query");
		}
		
		foreach ($this->selectQry as $q) {
			$out[] = $q->getSQL();
		}
		
		return "(".implode(") UNION (", $out).")";
	}
	
	function Exec() {
		return $this->db->Exec($this->getSQL());
	}
}
?>
