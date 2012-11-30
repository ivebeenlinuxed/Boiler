<?php
namespace System\Library;

class Lexical
{
	public static function conditionallyPluralize( $string, $count )
	{
		if ( intval( $count ) !== 0 )
			return self::pluralize( $string );

		return $string;
	}

	/**
	 * Returns the given underscored_word_group as a Human Readable Word Group.
	 * (Underscores are replaced by spaces and capitalized following words.)
	 *
	 * @param string $lowerCaseAndUnderscoredWord String to be made more readable
	 * 
	 * @return string Human-readable string
	 */
	public static function humanize($lowerCaseAndUnderscoredWord) {
		if (!($result = self::_cache(__FUNCTION__, $lowerCaseAndUnderscoredWord))) {
			$result = ucwords(str_replace('_', ' ', $lowerCaseAndUnderscoredWord));
			self::_cache(__FUNCTION__, $lowerCaseAndUnderscoredWord, $result);
		}
		return $result;
	}

	public static function pluralize($string)
	{

		$plural = array(
				array( '/(quiz)$/i',               "$1zes"   ),
				array( '/^(ox)$/i',                "$1en"    ),
				array( '/([m|l])ouse$/i',          "$1ice"   ),
				array( '/(matr|vert|ind)ix|ex$/i', "$1ices"  ),
				array( '/(x|ch|ss|sh)$/i',         "$1es"    ),
				array( '/([^aeiouy]|qu)y$/i',      "$1ies"   ),
				array( '/([^aeiouy]|qu)ies$/i',    "$1y"     ),
				array( '/(hive)$/i',               "$1s"     ),
				array( '/(?:([^f])fe|([lr])f)$/i', "$1$2ves" ),
				array( '/sis$/i',                  "ses"     ),
				array( '/([ti])um$/i',             "$1a"     ),
				array( '/(buffal|tomat)o$/i',      "$1oes"   ),
				array( '/(bu)s$/i',                "$1ses"   ),
				array( '/(alias|status)$/i',       "$1es"    ),
				array( '/(octop|vir)us$/i',        "$1i"     ),
				array( '/(ax|test)is$/i',          "$1es"    ),
				array( '/s$/i',                    "s"       ),
				array( '/$/',                      "s"       )
		);

		$irregular = array(
				array( 'move',   'moves'    ),
				array( 'sex',    'sexes'    ),
				array( 'child',  'children' ),
				array( 'man',    'men'      ),
				array( 'person', 'people'   )
		);

		$uncountable = array(
				'sheep',
				'fish',
				'series',
				'species',
				'money',
				'rice',
				'information',
				'equipment'
		);

		// save some time in the case that singular and plural are the same
		if (in_array(strtolower($string), $uncountable))
			return $string;

		// check for irregular singular forms
		foreach ($irregular as $noun) {
			if (strtolower($string) == $noun[0])
				return $noun[1];
		}

		// check for matches using regular expressions
		foreach ($plural as $pattern) {
			if (preg_match($pattern[0], $string))
				return preg_replace($pattern[0], $pattern[1], $string);
		}

		return $string;
	}
	
	public static function getClassName($table) {
		$name = explode("_", $table);
		$className = "";
		foreach ($name as $n) {
			$className .= ucfirst($n);
		}
		return $className;
	}
	
	public static function getClassNamePlural($table) {
		$name = explode("_", $table);
		$className = "";
		foreach ($name as $c=>$n) {
			if ($c == count($name)-1) {
				$n = \System\Library\Lexical::pluralize($n);
			}
			$className .= ucfirst($n);
	
		}
		return $className;
	}

}