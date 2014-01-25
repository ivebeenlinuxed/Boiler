<?php
namespace Library\Data;

class InputModule {
	public static function GetHTTP() {
		$rawdata = $_POST;
		$d = new DataStream();
		foreach ($_POST as $key=>$data) {
			if (substr($key, 0, 2) == "__") {
				$d->protocol[substr($key, 2)] = $data;
				unset($rawdata[$key]);
			}
		}
		$d->data = $rawdata;
	}
	
	public static function GetJSON() {
		
	}
	
	public static function GetXML() {
		
	}
}