<?php
namespace Library\Data;

class OutputModule {
	public static function OutputHTTP($d) {
		if ($d->protocol['redirect']) {
			header("Location: ".$d->protocol['redirect']);
		}
	}
	
	public static function GetJSON() {
		
	}
	
	public static function GetXML() {
		
	}
}