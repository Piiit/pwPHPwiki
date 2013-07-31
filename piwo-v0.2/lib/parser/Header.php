<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'pwTools/parser/LexerRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRule.php';
require_once INC_PATH.'pwTools/parser/Pattern.php';

class Header extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	private static $headerId;
	private static $level;
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_LINE, ' *(={1,5})', '={1,5}');
	}

	public function getAllowedModes() {
		return array("#DOCUMENT", "left", "right", "notoc", "multiline", "error", "info", "warning", "success", "validation", "border");
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