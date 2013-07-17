<?php
namespace System\Controller\API;

use \Library\Data\DataStream;

abstract class ModelController extends \Controller\BaseController {
	protected $inputData;
	protected $searchParams;
	protected $fetchObjects = false;
	protected $outputData = false;

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
		$this->searchParams = $_GET;

		
		if ($_SERVER['REQUEST_METHOD'] == "PUT") {
			foreach ($this->parse_raw_http_request() as $block) {
				$_POST[$block['name']] = $block['block'];
			}
		}

		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$input = $this->Input();
			$fetch = $this->AlterCreate($input);
		} else {
			$fetch = $this->Fetch($id);
			switch ($_SERVER['REQUEST_METHOD']) {
				case "DELETE":
					$this->AlterDelete($fetch);
					$fetch = false;
					break;
				case "PUT":
					$input = $this->Input();
					$fetch = $this->AlterChange($fetch, $input);
					break;
			}
		}


		$this->ProtocolRequest();
		$this->Output($fetch);
	}

	protected function ProtocolRequest() {
	}

	protected function DisplayError($error) {
		throw new \Exception($error);
		//die("ERROR $error");
	}

	protected function Input() {
		$data = $this->InputHTTP();
		return $data;
	}

	public function InputHTTP() {
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

	public function InputJSON() {
		$rawdata = json_decode(file_get_contents("php://input"));
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

	public function InputXML() {
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
	public function OutputHTTP($d) {
		if ($d->protocol['redirect']) {
			header("Location: ".$d->protocol['redirect']);
		}
		echo "OK";
	}

	public function OutputJSON($fetch) {
		echo json_encode($fetch);
	}

	public function OutputXML() {

	}

	protected function Fetch($id=false) {
		$class = $this->getModelClass();
		$key = $class::getPrimaryKey();


		if ($id !== false) {
			$data = $this->FetchRequest(array($key[0]=>$id), false);
			if (!is_array($data)) {
				return $data;
			}
			if (count($data) != 1) {
				throw new \Exception(self::ERROR_NOT_FOUND);
			}
			return $data;
		}
		$data = $this->FetchRequest($this->searchParams, true);
		if (!is_array($data)) {
			return $data;
		}
		return $data;
	}

	protected function FetchRequest($search, $fuzzy=true) {
		$class = $this->getModelClass();
		if (!$fuzzy) {
			return $class::getByAttributes($search);
		} else {
			$out = array();
			foreach ($search as $field=>$data) {
				$out = array_merge($out, $class::Search($field, $data));
			}
			return $out;
		}
	}

	protected function AlterWithObject() {

		return self::OK;
	}


	protected function AlterDelete($fetch) {
		foreach ($fetch as $obj) {
			if (($code = $this->DeleteRequest($obj)) != self::OK) {
				return $code;
			}
		}
		return self::OK;
	}

	protected function DeleteRequest($obj) {
		$obj->Delete();
		return self::OK;
	}

	protected function AlterChange($fetch, $input) {
		$output = array();
		foreach ($fetch as $obj) {
			$code = $this->ChangeRequest($obj, $input->data);
			$output[] = $obj;
		}
		return $output;
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
