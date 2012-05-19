<?php
namespace Library\Database;
class LinqRawFilter extends LinqEquality {
	public $sql;

	public function getSQL() {
		return $this->sql;
	}

	protected function getSymbol() {
	}
}
?>
