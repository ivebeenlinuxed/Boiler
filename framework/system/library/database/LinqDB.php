<?php
namespace Library\Database;
class LinqDB extends \mysqli {
	protected $obj;
	private static $masterDB;
	public $db;



	function __construct($host=null, $user=null, $password=null, $db="", $port=null, $socket=null) {
		parent::__construct($host, $user, $password, $db, $port, $socket);


	}

	function Select($Obj, $name="t") {
		return new LinqSelect($this, $Obj, $name);
	}

	function Update($Obj) {
		return new LinqUpdate($this, $Obj);
	}

	function getAndFilter($obj="") {
		return new LinqAND($this, $obj);
	}

	function Union() {
		return new LinqUnion($this);
	}


	function getOrFilter($obj="") {
		return new LinqOR($this, $obj);
	}

	function getRawFilter($sql) {
		$f = new LinqRawFilter($this);
		$f->sql = $sql;
		return $f;
	}

	static function getDB($host=null, $user=null, $password=null, $db="") {
		if (!is_array(self::$masterDB)) {
			self::$masterDB = array();
		}
		$hash = sha1($host.$user.$password.$db);
		if (!isset(self::$masterDB[$hash])) {
			self::$masterDB[$hash] = new LinqDB($host, $user, $password, $db);
		}
		return self::$masterDB[$hash];
	}

	function Exec($s) {
		$o = $this->query($s);
		$out = array();

		if ($this->errno != 0) {
			throw new LinqException("Error in database query: ".$this->error);
		}


		if ($o === true) {
			return $o;
		}

		while ($a = $o->fetch_assoc()) {
			$out[] = $a;
		}
		return $out;
	}
}
?>
