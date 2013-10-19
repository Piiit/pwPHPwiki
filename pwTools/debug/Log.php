<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'pwTools/debug/LogEntry.php';
require_once INC_PATH.'pwTools/debug/TestingTools.php';

class Log {
	
	const NOLEVEL = 0;
	const ERROR   = 1;  
	const WARNING = 2;  
	const INFO    = 3;  
	const DEBUG   = 4;  
	const OFF     = 5;
	
	private $_logbook = array();
	private $_dateFormat = "Y-m-d h:i:s";
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
	
	public function getLogReversed() {
		return array_reverse($this->_logbook);
	}
	
	public function getLastLog() {
		return end($this->_logbook);
	}
	
	public function toStringReversed() {
		return $this->toString(true);
	}

	public function __toString() {
		return $this->toString();
	}
	
	public function toString($reversed = false) {
		$out = "";
		$logBook = $reversed ? $this->getLogReversed() : $this->getLog();
		foreach ($logBook as $logEntry) {
			$date = date($this->_dateFormat, $logEntry->getTimestamp());
			$typeString = $this->_getLogLevelString($logEntry->getLevel());
			$debugString = "";
			$backTrace = $logEntry->getDebugBackTrace();
			if ($this->_logLevel == self::DEBUG) {
				$debugString = sprintf("%s->%s@%s", $backTrace["file"], $backTrace["function"], $backTrace["line"]);
			}
			$out .= sprintf("%19s | %-7s | %-40s | %s\n", $date, trim($typeString), trim($debugString), trim($logEntry->getDescription()));
		}
		return $out;
	}
	
	public function getLogLevel() {
		return $this->_logLevel;
	}
	
	public function getLogLevelAsString() {
		return $this->_getLogLevelString($this->_logLevel);
	}
	
	private function _getLogLevelString($type) {
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
		$this->_logbook[] = new LogEntry(time(), $loglevel, $text, $data, TestingTools::getDebugInfoAsArray());
	}
	
}

?>