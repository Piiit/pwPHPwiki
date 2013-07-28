<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'pwTools/parser/ParserTokenHandler.php';

class ParserTokenHeader implements ParserTokenHandler {
	
	private static $headerId;
	private static $level;

	public function onEntry(Node $node) {

		$nodeData = $node->getData();
		self::$level = strlen($nodeData[0]);
	
		if ($node->isInside("notoc")) {
			$o = '<h'.self::$level.'>';
		} else {
			$o = '<h'.self::$level.' id="header_'.$GLOBALS['indextable']['CONT'][self::$headerId]['ID'].'">';
			self::$headerId++;
		}
	
// 		$htxt = $node->getText($node);
// 		var_dump($node);
// 		if (!$htxt) {
// 			$o .= nop("Leere Titel sind nicht erlaubt!");
// 		}
// 		$o .= $htxt;
		return $o;
	}

	public function onExit(Node $node) {
		return '</h'.self::$level.'>';
	}

	public function doRecursion() {
		return true;
	}

}

?>