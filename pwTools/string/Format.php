<?php
class Format {
	
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
	
	public static function boolean2String($value) {
		if (!is_bool($value)) {
			throw new InvalidArgumentException("First argument has to be boolean!");
		}
		return ($value ? "TRUE" : "FALSE");
	}
	
}

?>