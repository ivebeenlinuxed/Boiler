<?php
namespace System\Library;

class ACL {
	const METHOD_UPDATE = 0x01;
	const METHOD_CREATE = 0x02;
	const METHOD_DELETE = 0x04;
	const METHOD_GET = 0x08;
	
	
	const HTTP_OK=200;
	const HTTP_CREATED=201;
	const HTTP_ACCEPTED=202;
	
	
	const HTTP_BAD_REQUEST=400;
	const HTTP_UNAUTHORIZED=402;
	const HTTP_PAYMENT_REQUIRED=402;
	const HTTP_FORBIDDEN=403;
	const HTTP_NOT_FOUND=404;
	
	const HTTP_METHOD_NOT_ALLOWED = 405;
	const HTTP_NOT_ACCEPTABLE = 406;
	
	const HTTP_CONFLICT = 409;
	const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
	
	public $status_code = 200;
	
	public $custom_message = null;
	
	public function getStatusMessage() {
		switch ($this->status_code) {
			case self::HTTP_OK:
				return "OK";
			case self::HTTP_CREATED:
				return "Created";
			case self::HTTP_ACCEPTED:
				return "Accepted";
			case self::HTTP_BAD_REQUEST:
				return "Bad Request";
			case self::HTTP_UNAUTHORIZED:
				return "Unauthorized";
			case self::HTTP_PAYMENT_REQUIRED:
				return "Payment Required";
			case self::HTTP_FORBIDDEN:
				return "Forbidden";
			case self::HTTP_NOT_FOUND:
				return "Not Found";
			case self::HTTP_METHOD_NOT_ALLOWED:
				return "Method Not Allowed";
			case self::HTTP_NOT_ACCEPTABLE:
				return "Method Not Acceptable";
			case self::HTTP_CONFLICT:
				return "Conflict";
			case self::HTTP_REQUEST_ENTITY_TOO_LARGE:
				return "Request Entity Too Large";
		}
	}
	
	public function getHelper() {
		switch ($this->status_code) {
			case self::HTTP_UNAUTHORIZED:
				return "<a href='/user/login'>Please login to the system</a>";
		}
	}
}