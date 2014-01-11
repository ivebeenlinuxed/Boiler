<?php
namespace System\Controller\API;

use \Library\Data\DataStream;

abstract class ModelController extends \Controller\BaseController {
	protected $inputData;
	public $searchParams;
	protected $fetchObjects = false;
	protected $outputData = false;
	public $protocol = array();
	public $query = false;
	
	public $page = 0;
	public $page_size = 10;

	const OK = 200;
	const ERROR_NOT_FOUND = 404;
	const ERROR_FORBIDDEN = 403;
	const ERROR_INTERNAL = 500;
	const ERROR_NOT_IMPLEMENTED = 501;

	protected abstract function getModelClass();

	public function index($id=false) {
		//FIXME this should not happen!
		if ($id == "undefined") {
			$id = false;
		}
		
		if ($id == "add") {
			$this->add();
		}
		
		if (($u = $this->getCurrentUser()) == false) {
			$this->DisplayError("Not logged in");
			return;
		}
		
		$this->protocol = $this->ProtocolRequest();
		$this->searchParams = $this->ConditionalRequest();
		
		
		if ($_SERVER['HTTP_X_PAGE']) {
			$epg = explode("/", $_SERVER['HTTP_X_PAGE']);
			$this->page = (int)$epg[0];
			if ($epg[1]) {
				$this->page_size = (int)$epg[1];
			}
		}
		
		
		if ($_SERVER['REQUEST_METHOD'] == "PUT") {
			foreach ($this->parse_raw_http_request() as $block) {
				$_POST[$block['name']] = $block['block'];
			}
		}
		

		if ($_SERVER['REQUEST_METHOD'] == "POST" && !isset($this->searchParams['__GET_OVERRIDE'])) {
			$this->protocol['edit'] = true;
			$input = $this->InputHTTP();
			$fetch = $this->AlterCreate($input);
		} else {
			if ($_SERVER['REQUEST_METHOD'] == "POST") {
				$this->searchParams = $this->Input();
				$fetch = $this->FetchSpecial();
			} else {
				$fetch = $this->Fetch($id);
			}
			switch ($_SERVER['REQUEST_METHOD']) {
				case "DELETE":
					$del_fetch = $this->CompleteFetch($fetch);
					$this->AlterDelete($del_fetch);
					$fetch = false;
					break;
				case "PUT":
					$input = $this->Input();
					$put_fetch = $this->CompleteFetch($fetch);
					$fetch = $this->AlterChange($put_fetch, $input);
					break;
			}
		}
		
		

		$this->Output($fetch);
	}
	
	/**
	 * 
	 */
	protected function add() {
		$c = static::getModelClass();
		$key = $c::getPrimaryKey()[0];
		$obj = $c::Create();
		header("Location: /api/{$c::getTable()}/{$obj->$key}?__edit=1");
	}

	protected function ProtocolRequest() {
		$proto = array();
		foreach ($_GET as $key=>$value) {
			if (substr($key, 0, 4) == "__X_") {
				$_SERVER['HTTP_X_'.substr($key, 4)] = $value;
			}
			
			if (substr($key, 0, 2) == "__") {
				$keyb = substr($key, 2);
				$proto[$keyb] = $value;
				unset($_GET[$key]);
			}
		}
		unset($_GET['_pjax']);
		return $proto;
	}
	
	protected function ConditionalRequest() {
		if ($this->protocol['where']) {
			$conditions = json_decode($this->protocol['where']);
		} else {
			$conditions = array();
		}
		foreach ($_GET as $key=>$data) {
			$conditions[] = array($key, "LIKE", "%".$data."%");
		}
		return $conditions;
	}

	protected function DisplayError($error) {
		throw new \Exception($error);
		//die("ERROR $error");
	}

	protected function Input() {
		//switch (\Core\Router::$mode) {
		//	case \Core\Router::MODE_XML:
		//		$data = self::InputXML();
		//		break;
		//	case \Core\Router::MODE_JSON:
		//		$data = self::InputJSON();
		//		break;
		//	default:
				$data = self::InputHTTP();
		//		break;
		//}
		return $data;
	}

	protected function InputHTTP() {
		$rawdata = $_POST;
		$d = new DataStream();
		foreach ($_POST as $key=>$data) {
			if (substr($key, 0, 2) == "__") {
				$d->protocol[substr($key, 2)] = $data;
				unset($rawdata[$key]);
			}
		}
		if ($_POST != null) {
			$d->data = $rawdata;
		}
		return $d;
	}

	protected function InputJSON() {
		$rawdata = json_decode(file_get_contents("php://input"));
		$d = new DataStream();
		foreach ($rawdata as $key=>$data) {
			if (substr($key, 0, 2) == "__") {
				$d->protocol[substr($key, 2)] = $data;
				unset($rawdata[$key]);
			}
		}
		if ($rawdata != null) {
			$d->data = $rawdata;
		}
		var_dump($_POST, $d);
		return $d;

	}

	protected function InputXML() {
		$rawdata = \System\Library\StdLib::xml2object(file_get_contents("php://input"));
		$d = new DataStream();
		foreach ($_POST as $key=>$data) {
			if (substr($key, 0, 2) == "__") {
				$d->protocol[substr($key, 2)] = $data;
				unset($rawdata[$key]);
			}
		}
		$d->data = $rawdata;
		return $d;
	}

	protected function Output($fetch) {
		switch (\Core\Router::$mode) {
			case \Core\Router::MODE_XML:
				self::OutputXML($fetch);
				break;
			case \Core\Router::MODE_JSON:
				self::OutputJSON($fetch);
				break;
			default:
				self::OutputHTTP($fetch);
				break;
		}
	}
	protected function OutputHTTP($d) {
		$c = $this->getModelClass();
		$table = $c::getTable();
		if (is_a($d, "\Library\Database\LinqSelect")) {
			switch ($_SERVER['HTTP_X_DISPOSITION']) {
				case "modal":
					$view = "/list/modal";
					break;
				default:
					$view = "/list/full";
					break;
			}
		} elseif ($this->protocol['edit']) {
			switch ($_SERVER['HTTP_X_DISPOSITION']) {
				case "modal":
					$view = "/edit/modal";
					break;
				default:
					$view = "/edit/full";
					break;
			}
		} else {
			switch ($_SERVER['HTTP_X_DISPOSITION']) {
				case "modal":
					$view = "/view/modal";
					break;
				default:
					$view = "/view/full";
					break;
			}
		}
		if (\Core\Router::hasView("api/{$table}{$view}")) {
			\Core\Router::loadView("api/{$table}{$view}", array("table"=>$table, "class"=>$c, "data"=>$d, "controller"=>$this));
		} else {
			\Core\Router::loadView("api/_generic{$view}", array("table"=>$table, "class"=>$c, "data"=>$d, "controller"=>$this));
			
		}
		
		
		if ($d->protocol['redirect']) {
			header("Location: ".$d->protocol['redirect']);
		}
	}

	protected function OutputJSON($fetch) {
		$fetch = $this->CompleteFetch($fetch);
		$c = get_called_class();
		if (is_callable(array($this->getModelClass(), "OverrideApi"))) {
			$fetch = call_user_func(array($this->getModelClass(), "OverrideApi"), $fetch);
		}
		echo json_encode($fetch);
	}

	protected function OutputXML() {

	}

	protected function Fetch($id=false) {
		$class = $this->getModelClass();
		$key = $class::getPrimaryKey();


		if ($id !== false) {
			$data = $class::Fetch($id);
			return $data;
		}
		$data = $this->FetchRequest($this->searchParams, true);
		return $data;
	}

	protected function FetchRequest($search, $fuzzy=true) {
		$class = $this->getModelClass();
		
		
		
		$ids = array();
		$db = $class::getDB();
		$select = $db->Select($class);
		$and = $select->getAndFilter();
		foreach ($search as $param) {
			switch ($param[1]) {
				case "=":
					$and->eq($param[0], $param[2]);
					break;
				case "!=":
					$and->neq($param[0], $param[2]);
					break;
				case "<":
					$and->lt($param[0], $param[2]);
					break;
				case ">":
					$and->gt($param[0], $param[2]);
					break;
				case "<=":
					$and->lteq($param[0], $param[2]);
					break;
				case ">=":
					$and->gteq($param[0], $param[2]);
					break;
				case "LIKE":
					$and->like($param[0], $param[2]);
					break;
				case "NOT LIKE":
					$and->nlike($param[0], $param[2]);
					break;
				default:
					new \Library\APIException("The equality symbol was note recognised");
			}
		}
		$select->setFilter($and);
		$this->query = $select;
		return $select;
		
		
	}
	
	protected function CompleteFetch($select) {
		$class = static::getModelClass();
		$key = $class::getPrimaryKey()[0];
		$out = array();
		foreach ($select->Exec() as $row) {
			$out[] = new $class($row[$key]);
		}
		return $out;
	}

	protected function AlterWithObject() {

		return self::OK;
	}


	protected function AlterDelete($fetch) {
		return $this->DeleteRequest($fetch);
	}

	protected function DeleteRequest($obj) {
		$obj->Delete();
		return self::OK;
	}

	protected function AlterChange($fetch, $input) {
		$this->ChangeRequest($fetch, $input->data);
		return $fetch;
	}

	protected function ChangeRequest($obj, $fields) {
		$obj->setAttributes($fields);
	}

	protected function AlterCreate($input) {
		return $this->CreateRequest($input->data);
	}

	protected function CreateRequest($fields) {
		$class = $this->getModelClass();
		return $class::Create($fields);
	}

	protected function parse_raw_http_request()
	{
		// read incoming data
		$input = file_get_contents('php://input');

		// grab multipart boundary from content type header
		preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
			
		// content type is probably regular form-encoded
		if (!count($matches)) {
			// we expect regular puts to containt a query string containing data
			parse_str(urldecode($input), $a_data);
			$out = array();
			foreach ($a_data as $k=>$d) {
				$b = array("block"=>$d, "name"=>$k);
				$out[] = $b;
			}
			return $out;
		}
			
		$boundary = $matches[1];
		// split content by boundary and get rid of last -- element
		preg_match_all("/--$boundary\r\n(.+)\r\n--$boundary--/ms", $input, $blocks);


		$out_blocks = array();
		foreach ($blocks[1] as $block) {
			$out = array("block"=>$block, "headers"=>array(), "name"=>null);
			while (preg_match("/\A([a-zA-Z-]+): (.+)/", $out['block'], $hdr)) {
				$out['headers'][$hdr[1]] = $hdr[2];
				$out['block'] = substr($out['block'], strlen($hdr[0])+2);
			}
			$out['block'] = substr($out['block'], 1);
			preg_match('/name=\"([^\"]*)\"/', $out['headers']['Content-Disposition'], $name);
			$out['name'] = $name[1];
			$out_blocks[] = $out;
		}
		return $out_blocks;
	}
}
