<?php
class TextFileFormat {
	
	private $chosen = -1;
	
	private static $enum = array(
			0 => 'UNIX',
			1 => 'WINDOWS',
			2 => 'OLDMAC',
			3 => 'UNDEFINED',
			4 => 'MIXED',
			5 => 'MAC'
			);
	
	public function __construct($chosen) {
		if(is_numeric($chosen) && $chosen >= 0 && $chosen < sizeof(self::$enum)) {
			$this->chosen = $chosen;
		} else {
			$this->chosen = self::toOrdinal($chosen);
		}
	}
	
	public function getOrdinal() {
		return self::toOrdinal($this->chosen);
	}
	
	public function getString() {
		return self::toString($this->chosen);
	}
	
	public static function toOrdinal($name) {
		return array_search($name, self::$enum);
	}
	
	public static function toString($ordinal) {
		return self::$enum[$ordinal];
	}
	
}

?>