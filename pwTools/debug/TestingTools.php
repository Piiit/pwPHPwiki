<?php

//TODO getDebugInfo should return a debug info object not an array or string
//TODO replace var_dump(?) or replace all other output mechanisms with var_dump(???)

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'pwTools/string/StringTools.php';
require_once INC_PATH.'pwTools/data/ArrayTools.php';

class TestingTools {
	
	const INFORM = "INFORM";
	const DEBUG = "DEBUG";
	const PRINTNEWLINE = 0;
	const REPLACENEWLINE = 1;
	
	private static $_debugOn = false;
	private static $_init = false;
	
	private static function printLine($type, $length, $out, $description = null, $debugInfo = null) {
		isset($length) ? $length = "<span class='deblength'>$length</span>" : $length = "";
		isset($description) && strlen($description) > 0 ? $description = "$description = " : $description = "";
		isset($out) && $type != "array" ? $out = "<span class='debout'>$out</span>" : $out = "";
		return "<li><pre class='debpre !important'> $debugInfo ($type, $length)\n $description$out</pre></li>\n";
	}
	
	private static function printItem($item, $name = null, $debugInfo = null, $newline = self::REPLACENEWLINE) {
		$name = htmlentities($name);
		if (is_array($item)) {
			echo self::printLine(gettype($item), count($item), "", $name, $debugInfo);
		} elseif (is_bool($item)) {
			echo self::printLine("boolean", 1, $item ? $item = "true" : $item = "false", $name, $debugInfo);
		} elseif (is_null($item)) {
			echo self::printLine("null", "", "", $name, $debugInfo);
		} elseif (is_string($item)) {
			$itemClean = htmlentities($item);
			if($newline == self::REPLACENEWLINE) {
				$itemClean = preg_replace("#\n#", "<code class='debspecial'> N </code>", $itemClean);
				$itemClean = preg_replace("#\r#", "<code class='debspecial'> R </code>", $itemClean);
			}
			$itemClean = preg_replace("#\t#", "<code class='debspecial'> T </code>", $itemClean);
			echo self::printLine("string", strlen($item), $itemClean, $name, $debugInfo);
		} elseif (is_object($item)) {
			echo "<pre class='debpre !important'>".$debugInfo; //FIXME output with printLine or similar
			var_dump($item);
			echo "</pre>";
		} else {
			echo self::printLine(gettype($item), count($item), $item, $name, $debugInfo);
		}
	}
	
	private static function printAll($output, $description="", $call = 0, $type = self::INFORM, $newline = self::REPLACENEWLINE) {
		self::init();
		if ($call == 0) {
			$debugInfo = $type.": SUM=".count($output, COUNT_RECURSIVE)."; ".self::getDebugInfoAsString($description);
			echo "<div class='debdiv'><ul id='first'>\n";
  			self::printItem($output, $description, $debugInfo, $newline);
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
			StringTools::htmlIndentPrint ();
			StringTools::htmlIndentPrint ("<!-- PW_DEBUG_INIT --------------------------------------------------->");
			StringTools::htmlIndentPrint ("<style>", StringTools::START);
			StringTools::htmlIndentPrint (".debpre {font-size: 12px; color: black; background-color: lightgray; margin: 1px; padding-top: 0px}");
			StringTools::htmlIndentPrint (".debout {background-color: white; color: black; border: 1px solid black; padding-left: 2px; padding-right: 2px}");
			StringTools::htmlIndentPrint (".debspecial {background-color: gray; color: white; margin-left: 2px; margin-right: 2px}");
			StringTools::htmlIndentPrint (".debdiv {margin: 5px; border: 1px solid black; background-color: lightgray}");
			StringTools::htmlIndentPrint (".debdiv ul {list-style-type: none}");
			StringTools::htmlIndentPrint (".debdiv ul li {margin-top:3px;}");
			StringTools::htmlIndentPrint (".debdiv ul#first {padding-left: 0px; margin-left: 0; margin-top: 3px; padding-bottom: 3px; margin-bottom:0}");
			StringTools::htmlIndentPrint ("</style>", StringTools::END);
			StringTools::htmlIndentPrint ("<!-- PW_DEBUG_INIT --------------------------------------------------->");
			StringTools::htmlIndentPrint ();
			self::$_init = true;
		}
	}
	
	public static function inform($output, $description = "") {
		self::printAll($output, $description);
	}
	
	public static function informPrintNewline($output, $description = "") {
		self::printAll($output, $description, 0, self::INFORM, self::PRINTNEWLINE);
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
			$funcText = "; FUNC=".$funcText;
		}
		return "FILE=".basename($debugInfo["file"])."; LINE=".$debugInfo["line"].$funcText;
	}
	
}

?>