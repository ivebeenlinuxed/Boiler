<?php
namespace Library\Database;
class LinqDB {
	protected $obj;
	private static $masterDB;
	public $db;
	protected $resource;



	function __construct($host=null, $user=null, $password=null, $db="", $port=null, $socket=null) {
		$this->resource = pg_connect("host={$host} port={$port} dbname={$db} user={$user} password={$password}");
	}
	
	/**
	 * Get a select query
	 * 
	 * @param string $Obj  Object to select
	 * @param string $name "AS" in the database query
	 * 
	 * @return \Library\Database\LinqSelect
	 */
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

	static function getDB($host=null, $user=null, $password=null, $db="", $port) {
		if (!is_array(self::$masterDB)) {
			self::$masterDB = array();
		}
		$hash = sha1($host.$user.$password.$db.$port);
		if (!isset(self::$masterDB[$hash])) {
			self::$masterDB[$hash] = new LinqDB($host, $user, $password, $db, $port);
		}
		return self::$masterDB[$hash];
	}
	
	public function query($s) {
		if (defined("DEBUG") && DEBUG == true) {
			var_dump($s);
		}
		return pg_query($this->resource, $s);
	}
	
	public function getResult($s) {
		return $this->query($s);
	}

	function Exec($s) {
		if (defined("DEBUG") && DEBUG == true) {
			var_dump($s);
		}
		$o = $this->getResult($s);
		$out = array();

		if ($this->errno != 0) {
			throw new LinqException("Error in database query: ".$this->error);
		}


		if ($o === true) {
			return $o;
		}

		while ($a = pg_fetch_assoc($o)) {
			$out[] = $a;
		}
		return $out;
	}
}
?>
