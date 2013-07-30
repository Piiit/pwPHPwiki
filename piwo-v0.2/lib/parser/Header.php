<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'pwTools/parser/ParserTokenHandler.php';
require_once INC_PATH.'pwTools/parser/ParserToken.php';

class Header extends ParserToken implements ParserTokenHandler {
	
	private static $headerId;
	private static $level;
	
	public function getName() {
		return 'header';
	}

	public function onEntry() {

		$node = $this->getNode();
		$nodeData = $node->getData();
		self::$level = strlen($nodeData[0]);
	
		if ($node->isInside("notoc")) {
			$o = '<h'.self::$level.'>';
		} else {
			//$o = '<h'.self::$level.' id="header_'.$GLOBALS['indextable']['CONT'][self::$headerId]['ID'].'">';
			$o = '<h'.self::$level.'>';
			self::$headerId++;
		}
		
 		$htxt = trim($this->getText($node));
 		if (strlen($htxt) == 0) {
 			$o .= nop("Leere Titel sind nicht erlaubt!");
 		}
 		$o .= $htxt;
		return $o;
	}

	public function onExit() {
		return '</h'.self::$level.'>';
	}

	public function doRecursion() {
		return false;
	}

}

?>