<?php

// TODO Compare (non)-keysensitive of two strings, no matter if entities or utf8 or normal ascii, ex. a&amp; == a&

class StringTools {
	
	private static $_indentation = 0;
	const MIDDLE = 0;
	const START = 1;
	const END = 2;
	
	public static function showLineNumbers($textInput) {
		if (!is_string($textInput)) {
			throw new InvalidArgumentException("First argument has to be string!");
		}
		if (strlen($textInput) == 0) {
			return $textInput;
		}
		$text = explode("\n", $textInput);
		$t = "";
		foreach($text as $k => $line) {
			$t .= sprintf("%5d %s\n", $k+1, $line);
		}
		return $t;
	}
	
	public static function preFormat($textInput) {
		return "<pre>".$textInput."</pre>";
	}
	
	public static function preFormatShowLineNumbers($textInput) {
		self::preFormat(self::showLineNumbers($textInput));
	}
	
	public static function boolean2String($value) {
		if (!is_bool($value)) {
			throw new InvalidArgumentException("First argument has to be boolean!");
		}
		return ($value ? "true" : "false");
	}
	
	//TODO Better use a single htmlIndentation method that handles entire html pages... (this should become deprecated)
	public static function htmlIndent($type_txt="", $startend=self::MIDDLE, $newline = true, $spaces = true) {
	
		if (is_numeric($type_txt)) {
			self::$_indentation += $type_txt;
			return "";
		}
	
		$startend = strtolower($startend);
	
		if ($startend == self::END) {
			self::$_indentation--;
		}
	
		if ($spaces) {
			$spaces = "";
			for ($i = 0; $i < self::$_indentation; $i++) {
				$spaces .= "  ";
			}
		}
	
		if ($newline) {
			$newline = "\n";
		}
	
		if ($startend == self::START) {
			self::$_indentation++;
		}
	
		// 		if (array_key_exists($type_txt, $WIKI_GLOBALS[tags])) {
		// 			if (is_array($WIKI_GLOBALS[tags][$type_txt])) {
		// 				return $spaces.$WIKI_GLOBALS[tags][$type_txt][$startend].$newline;
		// 			}
		// 			return $spaces.$WIKI_GLOBALS[tags][$type_txt].$newline;
		// 		}
		return $spaces.$type_txt.$newline;
	}
	
	public static function htmlIndentPrint($type_txt="", $startend="", $newline = true, $spaces = true) {
		echo self::htmlIndent($type_txt, $startend, $newline, $spaces);
	}
	
	public static function htmlIndentReset() {
		self::$_indentation = 0;
	}
	
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