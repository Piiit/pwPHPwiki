<?php

class Debug {
	
	public static function out($output) {
		echo '<pre style="font-size: 12px; background-color: black; color: lightgreen; border: 1px solid lightgreen;">';
		echo(self::getDebugInfo()."\n");
		var_dump($output);
		echo '</pre>';
	}
	
	public static function out2($output) {
		echo '<pre style="font-size: 12px; background-color: black; color: red; border: 1px solid red;">';
		echo(self::getDebugInfo()."\n");
		var_dump($output);
		echo '</pre>';
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