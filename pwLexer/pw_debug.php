<?php

# !!!achtung es wird nur etwas ausgegeben wenn pw_debug in der sessionvariable vorhanden und nicht false ist

## todo: return des ausgabe-strings, damit man alles gesammelt ausgeben kann
## todo: verschiedene debug-levels... siehe E_ALL, E_ERROR, ...
## todo: ausgabe in log-file

# auch E_NOTICE anzeigen, damit man auch kleinere programmfehler finden kann!! (per default ist dies ausgeschaltet)
require_once "common.php";

if (isset($_SESSION["pw_debug"]) and $_SESSION["pw_debug"])
	error_reporting(E_ALL&~E_NOTICE);

$pw_debug_id = 0;

function pw_debug_style_out($type, $length, $out, $main = NULL, $info = NULL) {
	global $pw_debug_id;
	isset($length) ? $length = "<span class='deblength'>$length</span>" : $length = "";
	isset($out) and $type != "array" ? $out = "<span class='debout'>$out</span>" : $out = "";
	return "<pre class='debpre !important'><b>[$main]</b> <span class='debtype'>$type</span>".$length." ".$out." <span style='display:inline; margin-left: 20px; '>$info</span></pre>";
}

function pw_debug_init($start = true) {
	if (!$start) {
		unset($_SESSION['pw_debug']);
		return;
	};

	if (!isset($_SESSION["pw_debug"]) or !$_SESSION["pw_debug"])
		$_SESSION["pw_debug"] = true;

	pw_ne ();
	pw_ne ("<!-- PW_DEBUG_INIT --------------------------------------------------->");
	pw_ne ("<script language='JavaScript' src='lib/js/pw_showhide.js'></script>");
	pw_ne ("<style>", START);
	pw_ne (".deblength {border: 1px solid black; background-color: black; color: white; padding-left: 3px; padding-right: 3px}");
	pw_ne (".debtype {background-color: white; border: 1px solid black; color: black; padding-left: 3px; padding-right: 3px}");
	pw_ne (".debpre {font-size: 12px; color: black; background-color: lightgray; margin: 0; padding-top: 6px}");
	pw_ne (".debout {background-color: white; color: black; border: 1px solid black; padding-left: 2px; padding-right: 2px}");
	pw_ne (".debspecial {background-color: gray; color: white; margin-left: 2px; margin-right: 2px}");
	pw_ne (".debdiv {margin-left: 20px; margin-top: -20px; border: 1px solid black; background-color: lightgray; margin-bottom: 1px;}");
	pw_ne (".debbutton {height: 20px; wi/dth: 10px; width: 20px}");
	pw_ne (".debdiv ul {list-style-type: none}");
	pw_ne (".debdiv ul#first {padding-left: 0px; margin-left: 0; margin-top: 3px; padding-bottom: 3px; margin-bottom:0}");
	pw_ne (".debdiv ul li pre {background-color: lightgray; !important}");
	pw_ne ("</style>", END);
	pw_ne ("<!-- PW_DEBUG_INIT --------------------------------------------------->");
	pw_ne ();
}

function pw_debug_msg($text, $type, $info="Keine Informationen", $cat=NULL) {


}

function pw_debug_itemout($item, $name=NULL, $info=NULL) {
	$name = htmlentities($name);
	if (is_array($item))
		echo "<li>".pw_debug_style_out(gettype($item), count($item), "", $name, $info)."</li>\n";
	elseif (is_bool($item))
	echo "<li>".pw_debug_style_out("boolean", 1, $item ? $item = "true" : $item = "false", $name, $info)."</li>\n";
	elseif (is_null($item))
	echo "<li>".pw_debug_style_out("NULL", "", "", $name, $info)."</li>\n";
	elseif (is_string($item))
	echo "<li>".pw_debug_style_out("string", strlen($item), preg_replace("#\n#", "<code class='debspecial'> N </code>", htmlentities($item)), $name, $info)."</li>\n";
	else
		echo "<li>".pw_debug_style_out(gettype($item), count($item), $item, $name, $info)."</li>\n";
}

function pw_debug_get_info($what = NULL, $type = 0) {
	$deb = debug_backtrace();
	$func = next($deb);
	#$func = next($deb);
	$function = next($deb);
	#  $function = next($deb);
	$class = isset($function["class"]) ? $function["class"] : null;
	$function = $function["function"];
	$file = basename($func["file"]);
	$line = $func["line"];

	if ($type == 0) {
		if ($class) $class .= "::";
		return "TEXT=$what; FILE=$file; FUNC=$class$function; LINE=$line";
	} else
		return array("TEXT" => $what, "FILE" => $file, "FUNC" => $function, "CLASS" => $class, "LINE" => $line);
}

function pw_d($arr, $main="") {
	pw_debug($arr, $main);
}

function pw_debug ($arr, $main="", $call = 0) {
	if (!isset($_SESSION["pw_debug"]) or !$_SESSION["pw_debug"]) return 0;

	global $pw_debug_id;
	#$main = (string)htmlentities($main);

	# bei erstem aufruf: typ und groesse des arrays ausgeben
	if ($call == 0) {
		$GLOBALS[pw_debug_id]++;
		$info = "SUMM=".count($arr, COUNT_RECURSIVE)."; ".pw_debug_get_info($main);
		echo "\n\n<!-- PW_DEBUG: ID=$pw_debug_id; $info --------------------------------------------------->\n";
		echo "<button title='$info' class='debbutton' onMouseDown=\"pw_showhide('bl".$pw_debug_id."', 0)\">+</button>\n<div id='bl".$pw_debug_id."' class='debdiv'>\n";
		echo "<ul id='first'>\n";
		pw_debug_itemout($arr, $main, $info);
	}

	if (!is_array($arr)) {
		echo "</ul>\n</div>";
		return true;
	}

	echo "<ul>\n";

	# array durchsteppen: vars ausgeben, wenn array im hauptarray >> rekursion starten
	foreach ($arr as $name => $item) {

		pw_debug_itemout($item, $name);

		if (is_array($item)) {
			pw_debug($item, "", ++$call);
			$call--;
		}

	}

	echo "</ul>\n";

	if ($call == 0)
		echo "</ul>\n</div>";
	return true;
}

function pw_debug_test() {
	echo "PW_DEBUG_TEST<br>";
	pw_debug_init();

	pw_debug(false, "BOOL");
	pw_debug(NULL, "NULL");

	pw_debug('string mit Neuer Zeile
			zweite \n Zeile', 'STRINGS singlequoted damit \n nicht erkannt wird');
	pw_debug(-883, "INT");
	$x = array(1,2,3,4,19=>5,-1231236.555,7,8,9);
	$v = array(false,true,"xxxxx"=>$x,$x,$x);
	$em = array();
	$arr = array ("EINS"=>"111","222","333333","4as<a href=''>df
			</a>",NULL, $v, 1234, $x, $em, "VIELEARRAYS"=>array("x","y","x"=>array("h","j", array (true,"2",-3,"4xy",5))));
	pw_debug(array(), "leeres ARRAY");
	pw_debug($arr, "PW_DEBUG_TEST");
	echo "FERTIG";
}

# pw_debug_test();
# die();
?>
