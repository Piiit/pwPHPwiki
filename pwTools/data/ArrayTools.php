<?php
class ArrayTools {
	
	public static function getIfExists(array $array, $key) {
		if (array_key_exists($key, $array)) {
			return $array[$key];
		}
		return null;		
	}
}

?>