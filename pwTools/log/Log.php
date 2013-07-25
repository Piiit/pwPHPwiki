<?php

//TODO style direct to output? no garbage html 

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'pwTools/string/StringFormat.php';

class Log {
	
	private static function printLine($type, $length, $out, $main = NULL, $info = NULL) {
		isset($length) ? $length = "<span class='deblength'>$length</span>" : $length = "";
		isset($out) and $type != "array" ? $out = "<span class='debout'>$out</span>" : $out = "";
		return "<pre class='debpre !important'><b>[$main]</b> <span class='debtype'>$type</span>".$length." ".$out." <span style='display:inline; margin-left: 20px; '>$info</span></pre>";
	}
	
	private static function printItem($item, $name=NULL, $info=NULL) {
		$name = htmlentities($name);
		if (is_array($item))
			echo "<li>".self::printLine(gettype($item), count($item), "", $name, $info)."</li>\n";
		elseif (is_bool($item))
			echo "<li>".self::printLine("boolean", 1, $item ? $item = "true" : $item = "false", $name, $info)."</li>\n";
		elseif (is_null($item))
			echo "<li>".self::printLine("NULL", "", "", $name, $info)."</li>\n";
		elseif (is_string($item))
			echo "<li>".self::printLine("string", strlen($item), preg_replace("#\n#", "<code class='debspecial'> N </code>", htmlentities($item)), $name, $info)."</li>\n";
		else
			echo "<li>".self::printLine(gettype($item), count($item), $item, $name, $info)."</li>\n";
	}
	
	public static function shutdown() {
		unset($_SESSION['pw_debug']);
	}
	
	public static function init() {
	
		if (!isset($_SESSION["pw_debug"]) or !$_SESSION["pw_debug"]) {
    		$_SESSION["pw_debug"] = true;
		}
	
	    StringFormat::htmlIndentPrint ();
	    StringFormat::htmlIndentPrint ("<!-- PW_DEBUG_INIT --------------------------------------------------->");
  		StringFormat::htmlIndentPrint ("<script language='JavaScript' src='lib/js/pw_showhide.js'></script>");
		StringFormat::htmlIndentPrint ("<style>", StringFormat::START);
  		StringFormat::htmlIndentPrint (".deblength {border: 1px solid black; background-color: black; color: white; padding-left: 3px; padding-right: 3px}");
	    StringFormat::htmlIndentPrint (".debtype {background-color: white; border: 1px solid black; color: black; padding-left: 3px; padding-right: 3px}");
	    StringFormat::htmlIndentPrint (".debpre {font-size: 12px; color: black; background-color: lightgray; margin: 0; padding-top: 6px}");
	    StringFormat::htmlIndentPrint (".debout {background-color: white; color: black; border: 1px solid black; padding-left: 2px; padding-right: 2px}");
	    StringFormat::htmlIndentPrint (".debspecial {background-color: gray; color: white; margin-left: 2px; margin-right: 2px}");
	    StringFormat::htmlIndentPrint (".debdiv {margin-left: 20px; margin-top: -20px; border: 1px solid black; background-color: lightgray; margin-bottom: 1px;}");
	    StringFormat::htmlIndentPrint (".debbutton {height: 20px; wi/dth: 10px; width: 20px}");
	    StringFormat::htmlIndentPrint (".debdiv ul {list-style-type: none}");
	    StringFormat::htmlIndentPrint (".debdiv ul#first {padding-left: 0px; margin-left: 0; margin-top: 3px; padding-bottom: 3px; margin-bottom:0}");
	    StringFormat::htmlIndentPrint (".debdiv ul li pre {background-color: lightgray; !important}");
	    StringFormat::htmlIndentPrint ("</style>", StringFormat::END);
 	 	StringFormat::htmlIndentPrint ("<!-- PW_DEBUG_INIT --------------------------------------------------->");
		StringFormat::htmlIndentPrint ();
	}
	
	
	public static function inform($output) {
		echo '<pre style="font-size: 12px; background-color: black; color: lightgreen; border: 1px solid lightgreen;">';
		echo(self::getDebugInfo()."\n");
		var_dump($output);
		echo '</pre>';
	}
	
	public static function inform2($output) {
		echo '<pre style="font-size: 12px; background-color: black; color: red; border: 1px solid red;">';
		echo(self::getDebugInfo()."\n");
		var_dump($output);
		echo '</pre>';
	}
	
	public static function debug ($arr, $main="", $call = 0) {
	  	if (!isset($_SESSION["pw_debug"]) or !$_SESSION["pw_debug"]) {
	  		return;
	  	}
	
	  	if ($call == 0) {
		    $info = "SUM=".count($arr, COUNT_RECURSIVE)."; ".self::getDebugInfo($main);
		    echo "<ul id='first'>\n";
		    self::printItem($arr, $main, $info);
		}
	
	  	if (!is_array($arr)) {
	    	echo "</ul>\n</div>";
	    	return;
	  	}
	
	  	echo "<ul>\n";
	
	  	foreach ($arr as $name => $item) {
	    	self::printItem($item, $name);
	    	if (is_array($item)) {
	      		self::debug($item, "", ++$call);
	      		$call--;
	    	}
	  	}
	
	  	echo "</ul>\n";
	  	if ($call == 0) {
	    	echo "</ul>\n</div>";
	  	}
	}
	
	private static function getDebugInfo() {
		$deb = debug_backtrace();
		$func = next($deb);
		$function = next($deb);
		$class = isset($function["class"]) ? $function["class"]."::" : null;
		$function = $function["function"];
		$file = basename($func["file"]);
		$line = $func["line"];
	
		return "FILE=$file; FUNC=$class$function; LINE=$line";
	}
}

?>