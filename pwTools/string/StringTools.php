<?php

// TODO Compare (non)-keysensitive of two strings, no matter if entities or utf8 or normal ascii, ex. a&amp; == a&

class StringTools {
	
	public static function rightTrim($message, $strip) {
		$lines = explode($strip, $message);
		$last  = '';
		do {
			$last = array_pop($lines);
		} while (empty($last) && (count($lines)));
		return implode($strip, array_merge($lines, array($last)));
	}
	
	public static function equals($string1, $string2) {
		throw new Exception("Not implemented yet!");
	}
	
	public static function equalsIgnoreCase($string1, $string2) {
		throw new Exception("Not implemented yet!");
	}
	
	public static function equalsTrimmed($string1, $string2) {
		throw new Exception("Not implemented yet!");
	}

	public static function equalsIgnoreCaseTrimmed($string1, $string2) {
		throw new Exception("Not implemented yet!");
	}
	
}

?>