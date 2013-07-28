<?php

//TODO access not through data-keys... getData(key)!

class Log {
	
	const NOLEVEL = 0;
	const ERROR   = 1;  
	const WARNING = 2;  
	const INFO    = 3;  
	const DEBUG   = 4;  
	const OFF     = 5;
	
	private $_logbook = array();
	private $_dateFormat = "Y/m/d h:i:s";
	private $_logLevel = self::INFO;
	
	public function __construct($level = self::INFO) {
		$this->setLogLevel($level);
	}
	
	public function add($text, $data = null) {
		$this->_add(self::NOLEVEL, $text, $data);
	}
	
	public function addDebug($text, $data = null) {
		$this->_add(self::DEBUG, $text, $data);
	}
	
	public function addInfo($text, $data = null) {
		$this->_add(self::INFO, $text, $data);
	}
	
	public function addWarning($text, $data = null) {
		$this->_add(self::WARNING, $text, $data);
	}
	
	public function addError($text, $data = null) {
		$this->_add(self::ERROR, $text, $data);
	}
	
	public function setLogLevel($level) {
		if ($level < 1 || $level > 5) {
			throw new InvalidArgumentException("Valid severity levels are 1=ERROR, 2=WARNING, 3=INFO, 4=DEBUG or 5=OFF. $level given!");
		}
		$this->_logLevel = $level;
	}
	
	public function getLog() {
		return $this->_logbook;
	}
	
	public function getLastLog() {
		return end($this->_logbook);
	}
	
	public function __toString() {
		$out = "";
		foreach ($this->_logbook as $line) {
			$date = date($this->_dateFormat, $line['TIME']);
			$typeString = $this->_getTypeString($line['TYPE']);
			$debugString = "";
			if ($this->_logLevel == self::DEBUG) {
				$debugString = sprintf("%s->%s@%s", $line["FILE"], $line["FUNC"], $line["LINE"]);
			}
			$out .= sprintf("%19s | %-7s | %-40s | %s\n", $date, trim($typeString), trim($debugString), trim($line['TEXT']));
		}
		return $out;
	}
	
	public function getLogLevel() {
		return $this->_logLevel;
	}
	
	public function getLogLevelAsString() {
		return $this->_getTypeString($this->_logLevel);
	}
	
	
	private function _getTypeString($type) {
		switch ($type) {
			case self::NOLEVEL: return "NOLEVEL";
			case self::DEBUG: return "DEBUG";
			case self::INFO: return "INFO";
			case self::WARNING: return "WARNING";
			case self::ERROR: return "ERROR";
		}
	}
	
	private function _add($loglevel, $text, $data) {
		if ($this->getLogLevel() < $loglevel) {
			return;
		}
		$this->_logbook[] = array_merge(
			array (	
				'TIME' => time(),
				'TYPE' => $loglevel, 
				'TEXT' => $text, 
				'DATA' => $data
			),
			$this->_getDebugInfo()
		);
	}
	
	private function _getDebugInfo() {
		if ($this->_logLevel < self::DEBUG) {
			return array();
		}
		$deb = debug_backtrace();
		$func = next($deb);
		$func = next($deb);
		$function = next($deb);
		$class = isset($function["class"]) ? $function["class"] : null;
		$function = $function["function"];
		$file = basename($func["file"]);
		$line = $func["line"];
		
		return array(
			'FILE'  => $file,
			'LINE'  => $line,
			'FUNC'  => $function,
			'CLASS' => $class
		);
	}
	
}

?>