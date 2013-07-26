<?php

//TODO getDebugInfo should return a debug info object not an array or string

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'pwTools/string/StringFormat.php';
require_once INC_PATH.'pwTools/data/ArrayTools.php';

class TestingTools {
	
	const INFORM = "INFORM";
	const DEBUG = "DEBUG";
	
	private static $_debugOn = false;
	private static $_init = false;
	
	private static function printLine($type, $length, $out, $description = null, $debugInfo = null) {
		isset($length) ? $length = "<span class='deblength'>$length</span>" : $length = "";
		isset($description) && strlen($description) > 0 ? $description = "<span class='debout'>$description</span> =" : $description = "";
		isset($out) && $type != "array" ? $out = "<span class='debout'>$out</span>" : $out = "";
		return "<li><pre class='debpre !important'> $description $out ($type, $length) $debugInfo</pre></li>";
	}
	
	private static function printItem($item, $name = null, $debugInfo = null) {
		$name = htmlentities($name);
		if (is_array($item))
			echo self::printLine(gettype($item), count($item), "", $name, $debugInfo)."\n";
		elseif (is_bool($item))
			echo self::printLine("boolean", 1, $item ? $item = "true" : $item = "false", $name, $debugInfo)."\n";
		elseif (is_null($item))
			echo self::printLine("null", "", "", $name, $debugInfo)."\n";
		elseif (is_string($item))
			echo self::printLine("string", strlen($item), preg_replace("#\n#", "<code class='debspecial'> N </code>", htmlentities($item)), $name, $debugInfo)."\n";
		else
			echo self::printLine(gettype($item), count($item), $item, $name, $debugInfo)."\n";
	}
	
	private static function printAll($output, $description="", $call = 0, $type = self::INFORM) {
		self::init();
		if ($call == 0) {
			$debugInfo = $type.": SUM=".count($output, COUNT_RECURSIVE)."; ".self::getDebugInfoAsString($description);
			echo "<div class='debdiv'><ul id='first'>\n";
  			self::printItem($output, $description, $debugInfo);
		}
		
		if (!is_array($output)) {
			echo "</ul>\n</div>";
			return;
		}
		
		echo "<ul>\n";
		
		foreach ($output as $name => $item) {
			self::printItem($item, $name);
			if (is_array($item)) {
				self::printAll($item, "", ++$call);
				$call--;
			}
		}
		
		echo "</ul>\n";
		if ($call == 0) {
			echo "</ul>\n</div>";
		}
	}
	
	public static function debugOff() {
		self::$_debugOn = false;
	}
	
	public static function debugOn() {
		self::$_debugOn = true;
	}
	
	public static function init() {
		if(self::$_init == false) {
			StringFormat::htmlIndentPrint ();
			StringFormat::htmlIndentPrint ("<!-- PW_DEBUG_INIT --------------------------------------------------->");
			StringFormat::htmlIndentPrint ("<style>", StringFormat::START);
			StringFormat::htmlIndentPrint (".debpre {font-size: 12px; color: black; background-color: lightgray; margin: 0; padding-top: 0px}");
			StringFormat::htmlIndentPrint (".debout {background-color: white; color: black; border: 1px solid black; padding-left: 2px; padding-right: 2px}");
			StringFormat::htmlIndentPrint (".debspecial {background-color: gray; color: white; margin-left: 2px; margin-right: 2px}");
			StringFormat::htmlIndentPrint (".debdiv {margin: 5px; border: 1px solid black; background-color: lightgray}");
			StringFormat::htmlIndentPrint (".debdiv ul {list-style-type: none}");
			StringFormat::htmlIndentPrint (".debdiv ul#first {padding-left: 0px; margin-left: 0; margin-top: 3px; padding-bottom: 3px; margin-bottom:0}");
			StringFormat::htmlIndentPrint ("</style>", StringFormat::END);
			StringFormat::htmlIndentPrint ("<!-- PW_DEBUG_INIT --------------------------------------------------->");
			StringFormat::htmlIndentPrint ();
			self::$_init = true;
		}
	}
	
	public static function inform($output, $description = "") {
		self::printAll($output, $description);
	}
	
	public static function debug($output, $description="") {
	  	if (self::$_debugOn == false) {
	  		return;
	  	}
	  	self::printAll($output, $description, 0, self::DEBUG);
	}
	
	public static function getDebugInfoAsArray() {
		$debugInfo = debug_backtrace();
		$debug = $debugInfo[0];
		while($debug["file"] == __FILE__) {
			$debug = next($debugInfo);
		}
		$line = $debug["line"];
		$debug = next($debugInfo);
		$debug["line"] = $line;
		return $debug;
	}
	
	public static function getDebugInfoAsString() {
		$debugInfo = self::getDebugInfoAsArray();
		return "FILE=".basename($debugInfo["file"])."; FUNC=".ArrayTools::getIfExists($debugInfo, "class").ArrayTools::getIfExists($debugInfo, "type").$debugInfo["function"]."; LINE=".$debugInfo["line"];
	}
	
}

?>