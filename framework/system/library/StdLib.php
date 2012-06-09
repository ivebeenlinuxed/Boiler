<?php
namespace System\Library;

class StdLib {
	public static function decode_entities($text) {
		$text= html_entity_decode($text,ENT_QUOTES,"ISO-8859-1"); //NOTE: UTF-8 does not work!
		$text= preg_replace('/&#(\d+);/me',"chr(\\1)",$text); //decimal notation
		$text= preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)",$text);  //hex notation
		return $text;
	}
	
	function xss_clean($data)
	{
		// Fix &entity\n;
		$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
	
		// Remove any attribute starting with "on" or xmlns
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
	
		// Remove javascript: and vbscript: protocols
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
	
		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
	
		// Remove namespaced elements (we do not need them)
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
	
		do
		{
			// Remove really unwanted tags
			$old_data = $data;
			$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
		}
		while ($old_data !== $data);
	
		// we are done...
		return $data;
	}


	public static function has_post($param) {
		foreach ($param as $data) {
			if (!isset($_POST[$data])) {
				return false;
			}
		}
		return true;
	}

	public static function processPostcode($pcode) {
		if (preg_match("/\A(\w{1,2}\d{1,2}\w?){1}(?<sp>\s{0,3})?(\d\w{2}){1}$/", $item, $m)) {
			return $m;
		} else {
			return false;
		}
	}

	public static function processURL($url) {
		if (!preg_match("/(?<protocol>\w+):\/\/(?<domain>[a-z0-9\-\._]+)[\/]?(?<path>[^\^\s^?]+)?(?<query>.*)?/", $url, $matches)) {
			return false;
		} else {
			return $matches;
		}
	}

	public static function findURL($url) {
		if (!preg_match_all("/(?<protocol>\w+):\/\/(?<domain>[a-z0-9\-\._]+)[\/]?(?<path>[^\?^\s]+)?(?<query>[^\s])?/", $url, $matches)) {
			return false;
		} else {
			$return = array();
			foreach ($matches as $key=>$match) {
				for ($i=0; $i<count($match); $i++) {
					if (!isset($return[$i])) {
						$return[$i] = array();
					}
					$return[$i][$key] = $match[$i];
				}
			}
		}
		return $return;
	}

	public static function has_request($param) {
		foreach ($param as $data) {
			if (!isset($_REQUEST[$data])) {
				return false;
			}
		}
		return true;
	}

	public static function array_contains($keys, $array) {
		foreach ($keys as $key) {
			if (!isset($array[$key])) {
				return false;
			}
		}
		return true;
	}

	public static function array_missing_key($keys, $array) {
		foreach ($keys as $key) {
			if (!isset($array[$key])) {
				return $key;
			}
		}
		return false;
	}

	public static function curl_process_output($o) {
		$output = array();
		$output['headers'] = array();
		$output['body'] = "";

		$bh = explode("\r\n\r\n", $o, 2);

		$output['body'] = $bh[1];

		$headers = explode("\r\n", $bh[0]);
		foreach ($headers as $h) {
			$eh = explode(":", $h, 2);
			if (isset($eh[1])) {
				$output['headers'][trim($eh[0])] = trim($eh[1]);
			} else {
				$output['headers']['HTTP'] = trim($eh[0]);
			}
		}
		return $output;
	}

	public static function is_email($email) {
		return (preg_match("/[a-z\d.-_\+]+@[a-z\d.-_\+]+\.[a-z.]+/", $email) > 0)? true : false;
	}

	public static function object_order($aObj, $property) {
		$cursor = 1;
		$comparison = 1;
		while ($cursor <= count($aObj)) {
			if ($aObj[$comparison]->$property > $aObj[$comparison-1]->$property) {
				//If property of the higher object is less than lower one swap
				$temp = $aObj[$comparison-1];
				$aObj[$comparison-1] = $aObj[$comparison];
				$aObj[$comparison] = $temp;



				//Compare two below next cycle
				$comparison--;
			} else {
				//If not, increase the cursor and begin comparing again
				$cursor++;
				$comparison = $cursor;
			}

			//If we have reached the last item to be compared increase the cursor
			if ($comparison == 0) {
				$cursor++;
				$comparison = $cursor;
			}

		}
		return $aObj;
	}

	public static function arrayarray_order($aObj, $property, $dec=false) {
		$comparisonKey = array();
		$data = array();
		$keyNum = true;
		foreach ($aObj as $Key=>$Data) {
			$comparisonKey[] = $Key;
			$data[] = $Data;
			if (!is_int($Key)) {
				$keyNum = false;
			}
		}
		$cursor = 1;
		$comparison = 1;
		do {
			$key = $comparison;
			$mkey = $comparison-1;
			if ($data[$key][$property] < $data[$mkey][$property]) {
				//If property of the higher object is less than lower one swap



				$temp = $data[$mkey];
				$data[$mkey] = $data[$key];
				$data[$key] = $temp;

				//Swap keys
				if (!$keyNum) {
					$temp = $comparisonKey[$comparison-1];
					$comparisonKey[$comparison-1] = $comparisonKey[$comparison];
					$comparisonKey[$comparison] = $temp;
				}
				//Compare two below next cycle
				$comparison--;
			} else {
				//If not, increase the cursor and begin comparing again
				$cursor++;
				$comparison = $cursor;
			}

			//If we have reached the last item to be compared increase the cursor
			if ($comparison == 0) {
				$cursor++;
				$comparison = $cursor;
			}
		} while ($cursor < count($aObj));

		if ($dec) {
			$data = array_reverse($data);
			$comparisonKey = array_reverse($comparisonKey);
		}

		if (!$keyNum) {
			$out = array();
			foreach ($data as $key=>$Obj) {
				$out[$comparisonKey[$key]] = $Obj;
			}
			$aObj = $out;
		} else {
			$aObj = $data;
		}

		return $aObj;
	}


	public static function is_interface_of($obj, $interface) {
		$r = new \ReflectionClass($obj);
		return $r->implementsInterface($interface);
	}

	public static function get_full_interface($iface) {

		$a = get_declared_classes();
		$out = array();
		foreach ($a as $net) {
			$r = new \ReflectionClass($net);
			if ($r->implementsInterface($iface) && !$r->isAbstract()) {
				$out[] = $net;
			}
		}
		return $out;

	}

	public static function curl_fetch($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		return curl_exec($ch);
	}

	public static function xml2object($data) {
		return json_decode(json_encode(xml2array($data)));
	}

	public static function xml2array($data) {
		$data = simplexml_load_string($data);
		return makeArray($data);
	}


	public static function makeArray($obj) {
		$arr = (array)$obj;
		if (empty($arr)) {
			$arr = "";
		} else {
			foreach ($arr as $key=>$value) {
				if (!is_scalar($value)) {
					$arr[$key] = makeArray($value);
				}
			}
		}
		return $arr;
	}
}
?>
