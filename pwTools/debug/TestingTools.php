<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'pwTools/string/StringTools.php';
require_once INC_PATH.'pwTools/data/ArrayTools.php';
require_once INC_PATH.'pwTools/debug/Log.php';
require_once INC_PATH.'pwTools/data/ArrayPrinter.php';

class TestingTools {
	
	const INFORM = "INFORM";
	const DEBUG = "DEBUG";
	
	private static $_debugOn = false;
	private static $_logOn = false;
	private static $_outputOn = false;
	private static $_log;
	
	private static function _createLogAndOutput($output, $level, $newline) {
		$dbg = self::getDebugInfoAsString();
		$out = "";
		if(is_array($output)) {
			$arrayPrinter = new ArrayPrinter();
			$arrayWalker = new ArrayWalker($output, $arrayPrinter);
			$out = $arrayWalker->getResult();
			if($newline == StringTools::REPLACENEWLINE) {
				$out = StringTools::replaceNewlines($out);
			}
		} else {
			$out = StringTools::getItemWithTypeAndSize($output, "", $newline);
		}
		if(self::$_logOn) {
			self::$_log->add($dbg." |".(is_array($output) ? "\n" : "").$out);
		}
		if(self::$_outputOn) {
			echo "<pre>$dbg$out</pre>";
		}
		
	}
	
	public static function debugOff() {
		self::$_debugOn = false;
	}
	
	public static function debugOn() {
		self::$_debugOn = true;
	}
	
	public static function logOff() {
		self::$_logOn = false;
	}
	
	public static function logOn() {
		self::$_logOn = true;
		self::$_log = new Log();
	}
	
	public static function outputOff() {
		self::$_outputOn = false;
	}
	
	public static function outputOn() {
		self::$_outputOn = true;
	}
	
	public static function getLog() {
		return self::$_log;
	}

	public static function log($output) {
		$tempOutputStatus = self::$_outputOn;
		self::outputOff(); 
		self::_createLogAndOutput($output, self::INFORM, StringTools::PRINTNEWLINE);
		if($tempOutputStatus) {
			self::outputOn();
		}
	}
	
	public static function inform($output) {
		self::_createLogAndOutput($output, self::INFORM, StringTools::PRINTNEWLINE);
	}
	
	public static function informReplaceNewlines($output) {
		self::_createLogAndOutput($output, self::INFORM, StringTools::REPLACENEWLINE);
	}
	
	public static function debug($output) {
	  	if (self::$_debugOn == false) {
	  		return;
	  	}
	  	self::_createLogAndOutput($output, self::DEBUG, StringTools::PRINTNEWLINE);
	}
	
	public static function debugReplaceNewlines($output) {
		if (self::$_debugOn == false) {
	  		return;
	  	}
		self::_createLogAndOutput($output, self::DEBUG, StringTools::REPLACENEWLINE);
	}
	
	//TODO getDebugInfo should return a debug info object not an array or string
	public static function getDebugInfoAsArray() {
		$debugInfo = debug_backtrace();
		$debug = $debugInfo[0];
		while($debug["file"] == __FILE__) {
			$debug = next($debugInfo);
		}
		$line = $debug["line"];
		$file = $debug["file"];
		$debug = next($debugInfo);
		$debug["line"] = $line;
		$debug["file"] = $file;
		return $debug;
	}
	
	public static function getDebugInfoAsString() {
		$debugInfo = self::getDebugInfoAsArray();
		$funcText = ArrayTools::getIfExists($debugInfo, "class").ArrayTools::getIfExists($debugInfo, "type").ArrayTools::getIfExists($debugInfo, "function");
		if(strlen($funcText) != 0) {
			$funcText = ":".$funcText;
		}
		return basename($debugInfo["file"]).$funcText." (".$debugInfo["line"].")";
	}
	
}

?>