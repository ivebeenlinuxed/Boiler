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

	public $view = false;
	public $disposition = "full";

	const OK = 200;
	const ERROR_NOT_FOUND = 404;
	const ERROR_FORBIDDEN = 403;
	const ERROR_INTERNAL = 500;
	const ERROR_NOT_IMPLEMENTED = 501;

	protected abstract function getModelClass();

	public function index($id=false, $view=false) {
		// Underscored advanced data needs to be removed, before the novices get their turn
		$this->protocol = $this->ProtocolRequest();
		
		// Standard REST data processed
		$this->searchParams = $this->ConditionalRequest();
		
		// Get the class we're going to be working with
		$class = static::getModelClass();
		
		//Pagination needs to be initialised from headers
		if ($_SERVER['HTTP_X_PAGE']) {
			$epg = explode("/", $_SERVER['HTTP_X_PAGE']);
			$this->page = (int)$epg[0];
			if ($epg[1]) {
				$this->page_size = (int)$epg[1];
			}
		}
		
		if ($_SERVER['HTTP_X_DISPOSITION']) {
			$this->disposition = $_SERVER['HTTP_X_DISPOSITION'];
		}
		
		//PUT requests need to be processed manually
		if ($_SERVER['REQUEST_METHOD'] == "PUT") {
			foreach ($this->parse_raw_http_request() as $block) {
				$_POST[$block['name']] = $block['block'];
			}
		}
		
		//Create before deciding which view to use
		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$this->protocol['edit'] = true;
			$input = $this->InputHTTP();
			$data = $this->AlterCreate($input);
			$key = $class::getPrimaryKey()[0];
			$id = $data->$key;
		}

		
		if ($id === false || ((int)$id == 0 && $id != "0")) {
			//Get the default collection view;
			$view_type = "collection";
			$default_view = "list";
			//Array
			$data = $this->Fetch($id);
			$qData = $data->getResult();
			$num_rows = $qData->num_rows;
			$qData->free_result();
			$data->setLimit($this->page*$this->page_size, $this->page_size);
			$data = $data->Exec();
		} else {
			//Get the default views setup
			$view_type = "singular";
			$default_view = "view";
			//We're only ever going to have one row
			$num_rows = 1;
			
			//Object
			$data = $class::Fetch($id);
			switch ($_SERVER['REQUEST_METHOD']) {
				case "DELETE":
					$this->AlterDelete($data);
					$data = false;
					break;
				case "PUT":
					$input = $this->Input();
					$data = $this->AlterChange($data, $input);
					break;
			}
			
		}
		
		switch (\Core\Router::$mode) {
			case \Core\Router::MODE_JSON:
				$format = "json";
				break;
			default:
				$format = "html";
				break;
		}
		
		
		
		if ($view == false ){
			$view = $default_view;
		}
		
		$view_vars = array("num_rows"=>$num_rows, "controller"=>$this, "data"=>$data, "class"=>$class, "table"=>$class::getTable());
		
		//Find the view we're looking for
		if (\Core\Router::hasView($v = "api/{$format}/{$class::getTable()}/{$view_type}/{$view}/{$this->disposition}")) {
			\Core\Router::loadView($v, $view_vars);
		//Fallback to the generic view if we can
		} elseif (\Core\Router::hasView($v = "api/{$format}/_generic/{$view_type}/{$view}/{$this->disposition}")) {
			\Core\Router::loadView($v, $view_vars);
		//Fallback to the default view
		} elseif (\Core\Router::hasView($v = "api/{$format}/{$class::getTable()}/{$view_type}/{$default_view}/{$this->disposition}")) {
			\Core\Router::loadView($v, $view_vars);
		//Get the full default view
		} else {
			\Core\Router::loadView("api/{$format}/_generic/{$view_type}/{$default_view}/full", $view_vars);
		}
	}

	/**
	 *
	 */
	public function add() {
		$c = static::getModelClass();
		$key = $c::getPrimaryKey()[0];
		$obj = $c::Create(array());
		header("Location: /{$c::getTable()}/{$obj->$key}/edit");
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
		return $proto;
	}

	protected function ConditionalRequest() {
		if ($this->protocol['where']) {
			$conditions = json_decode($this->protocol['where']);
		} else {
			$conditions = array();
		}
		foreach ($_GET as $key=>$data) {
			if ($key == "_pjax") {
				continue;
			}
			$conditions[] = array($key, "LIKE", "%".$data."%");
		}
		return $conditions;
	}

	protected function DisplayError($error) {
		throw new \Exception($error);
		//die("ERROR $error");
	}

	protected function Input() {
		$data = self::InputHTTP();
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

	

	protected function OutputJSON($fetch) {
		$fetch = $this->CompleteFetch($fetch);
		$c = get_called_class();
		if (is_callable(array($this->getModelClass(), "OverrideApi"))) {
			$fetch = call_user_func(array($this->getModelClass(), "OverrideApi"), $fetch);
		}
		echo json_encode($fetch);
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
		foreach ($select as $row) {
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
