<?php
namespace System\Controller\API;

use \Library\Data\DataStream;

abstract class ModelController extends \Controller\BaseController {
	protected $inputData;
	public $searchParams;
	
	public $raw_data = false;
	protected $fetchObjects = false;
	protected $outputData = false;
	
	
	public $protocol = array();
	
	
	public $query = false;
	public $order = array();
	public $fields = array();

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

	public function __construct() {
		//PUT requests need to be processed manually
		if ($_SERVER['REQUEST_METHOD'] == "PUT") {
			foreach ($this->parse_raw_http_request() as $block) {
				$_POST[$block['name']] = $block['block'];
			}
		}
	}
	
	protected function doACL($method, $data=null) {
		$class = $this->getModelClass();
		
		$user = \Controller\BaseController::getCurrentUser();
		if (!$user) {
			$result = new \Library\ACL();
			$result->status_code = \Library\ACL::HTTP_UNAUTHORIZED;
		} elseif (!$user->isGroup(new \Model\Group(0))) {
			$result = new \Library\ACL();
			$result->status_code = \Library\ACL::HTTP_FORBIDDEN;
			$result->custom_message = "This user is no longer active";
		} elseif (is_callable(array($class, "ACLRequest"))) {
			$result = $class::ACLRequest($method, $data);
		} else {
			$result  = new \Library\ACL();
		}
		
		if ($result->status_code >= 200 && $result->status_code < 300) {
			return;
		} else {
			\Core\Router::loadView("error", array("acl"=>$result));
			die();
		}
	}
	
	public function index($id=false, $view=false) {
		
		
		// Underscored advanced data needs to be removed, before the novices get their turn
		$this->protocol = $this->ProtocolRequest();
		
		// Standard REST data processed
		$this->searchParams = $this->ConditionalRequest();
		
		// Sets the ORDER BY
		if ($this->protocol['order']) {
			$this->order = json_decode($this->protocol['order']);
		}
		
		
		// Get the class we're going to be working with
		$class = static::getModelClass();
		
		$this->fields = $this->getFields();
		
		
		
		
		if (!in_array($class::getPrimaryKey()[0], $this->fields)) {
			$this->fields[] = $class::getPrimaryKey()[0];
		}
		
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
		
		
		
		
		if ($_SERVER['HTTP_X_REQUEST_METHOD']) {
			$_SERVER['REQUEST_METHOD'] = $_SERVER['HTTP_X_REQUEST_METHOD'];
		}
		
		
		
		//Create before deciding which view to use
		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$this->doACL(\Library\ACL::METHOD_CREATE);
			$this->protocol['edit'] = true;
			$input = $this->InputHTTP();
			try {
				$data = $this->AlterCreate($input);
			} catch (\Library\Database\DBException $e) {
				$acl = new \Library\ACL();
				$acl->status_code = \Library\ACL::HTTP_BAD_REQUEST;
				$acl->custom_message = "Problem while creating: ".$e->getMessage();
				\Core\Router::loadView("error", array("acl"=>$acl));
				die();
			}
			$key = $class::getPrimaryKey()[0];
			$id = $data->$key;
			//header("Location: /api/".$class::getTable()."/{$id}");
			//return;
		}
		
		if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
			$this->doACL(\Library\ACL::METHOD_DELETE, $id);
			$data = $class::Fetch($id);
			$this->AlterDelete($data);
			$id = false;
		}

		
		if ($id === false || ((int)$id == 0 && $id != "0")) {
			$this->doACL(\Library\ACL::METHOD_GET);
			$view = $id;
			$id = false;
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
			$this->raw_data = $data;
			$data = $this->CompleteFetch($data);
		} else {
			$this->doACL(\Library\ACL::METHOD_GET, $id);
			//Get the default views setup
			$view_type = "singular";
			$default_view = "view";
			
			//We're only ever going to have one row
			$num_rows = 1;
			
			//Object
			$data = $class::Fetch($id);
			if ($_SERVER['REQUEST_METHOD'] == "PUT") {
				$input = $this->Input();
				$data = $this->AlterChange($data, $input);
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
		
		if ($view == false) {
			$view = $default_view;
		}
		$view_vars = array("num_rows"=>$num_rows, "controller"=>$this, "data"=>$data, "class"=>$class, "table"=>$class::getTable());
		
		if (\Core\Router::hasView("/api/$format/_template/{$this->disposition}/top")) {
			\Core\Router::loadView("/api/$format/_template/{$this->disposition}/top");
		}
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
		
		if (\Core\Router::hasView("/api/$format/_template/{$this->disposition}/bottom")) {
			\Core\Router::loadView("/api/$format/_template/{$this->disposition}/bottom");
		}
	}
	
	protected function getFields() {
		$class = $this->getModelClass();
		//Get fields requested
		if ($this->protocol['fields']) {
			return json_decode($this->protocol['fields']);
		} else {
			$fields = array();
			foreach ($class::getDBColumns() as $col) {
				$fp = $class::getFieldPropertiesByColumn($col);
				if ($fp->visibility == \Library\FieldProperties::VISIBILITY_SHOW) {
					$fields[] = $col;
				}
			}
			return $fields;
		}
		
		//Apply security
		foreach ($this->fields as $offset=>$field) {
			$fp = $class::getFieldPropertiesByColumn($field);
			if ($fp->visibility == \Library\FieldProperties::VISIBILITY_PRIVATE) {
				array_splice($this->fields, $offset, 1);
			}
		}
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
		/*
		foreach ($_POST as $key=>$data) {
			if (substr($key, 0, 2) == "__") {
				$d->protocol[substr($key, 2)] = $data;
				unset($rawdata[$key]);
			}
		}
		*/
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
		$data = $this->__FetchRequest(true);
		return $data;
	}
	
	protected function getFilterFromInput($select, $search, $current=null) {
		if ($current) {
			$and = $current;
		} else {
			$and = $select->getAndFilter();
		}
		foreach ($search as $offset=>$param) {
			if ($offset == 1) {
				if (is_string($param)) {
					switch ($param) {
						case "OR":
							$and = $select->getOrFilter();
							break;
						default:
							$and = $select->getAndFilter();
							break; 
					}
					continue;
				}
			} else {
				$and = $select->getAndFilter();
			}
			
			if (is_array($param[0])) {
				$this->getFilterFromInput($select, $param, $and);
			}
			
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
		return $and;
	}
	
	public function getSelectsFromInput($select, $input) {
		foreach ($input as $field) {
			if (is_string($field)) {
				$select->addField($field);
			} if (is_array($field)) {
				switch ($field[0]) {
					case "SUM":
						$select->addSum($field[1]);
				}
			}
		}
	}
	
	protected function getUnderlyingSelect() {
		$class = $this->getModelClass();
		$ids = array();
		$db = $class::getDB();
		$select = $db->Select($class);
		foreach ($class::getDBColumns() as $col) {
			$select->addField($col);
		}
		return $select;
	}
	
	protected function getWrappedSelect() {
		$class = $this->getModelClass();
		$db = $class::getDB();
		return $db->Select($this->getUnderlyingSelect());
	}
	

	protected function __FetchRequest($fuzzy=true) {
		$select = $this->getWrappedSelect();
		
		$this->getSelectsFromInput($select, $this->fields);
		$search = $this->searchParams;
		$and = $this->getFilterFromInput($select, $search);
		$select->setFilter($and);
		foreach ($this->order as $o) {
			$select->setOrder($o[0], $o[1] != "DESC");
		}
		$this->query = $select;
		
		
		return $select;


	}

	public function CompleteFetch($select) {
		$class = static::getModelClass();
		$key = $class::getPrimaryKey()[0];
		$out = array();
		foreach ($select as $row) {
			$o = new $class($row[$key]);
			
			foreach ($row as $field=>$data) {
				if (!isset($o->field)) {
					$o->$field = $data;
				}
			}
			$out[] = $o;
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
		return $this->__CreateRequest($input->data);
	}

	public function __CreateRequest($fields) {
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
		} else {
			// Fetch each part
			$boundary = $matches[1];
			$parts = array_slice(explode($boundary, $raw_data), 1);
			$data = array();
			
			foreach ($parts as $part) {
				// If this is the last part, break
				if ($part == "--\r\n") break;
			
				// Separate content from headers
				$part = ltrim($part, "\r\n");
				list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);
			
				// Parse the headers list
				$raw_headers = explode("\r\n", $raw_headers);
				$headers = array();
				foreach ($raw_headers as $header) {
					list($name, $value) = explode(':', $header);
					$headers[strtolower($name)] = ltrim($value, ' ');
				}
			
				// Parse the Content-Disposition to get the field name, etc.
				if (isset($headers['content-disposition'])) {
					$filename = null;
					preg_match(
					'/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
					$headers['content-disposition'],
					$matches
					);
					list(, $type, $name) = $matches;
					if (isset($matches[4])) {
						//is a file
						$filename = $matches[4];
					}
					
					// handle your fields here
					switch ($name) {
						// this is a file upload
						case 'userfile':
							file_put_contents($filename, $body);
							break;
			
							// default for all other files is to populate $data
						default:
							$data[$name] = substr($body, 0, strlen($body) - 2);
							break;
					}
				}
			
			}
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
