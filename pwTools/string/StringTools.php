<?php
class StringTools {
	
	public static function rightTrim($message, $strip) {
		$lines = explode($strip, $message);
		$last  = '';
		do {
			$last = array_pop($lines);
		} while (empty($last) && (count($lines)));
		return implode($strip, array_merge($lines, array($last)));
	}
}

?>