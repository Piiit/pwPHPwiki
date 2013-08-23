<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'pwTools/parser/LexerRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRule.php';
require_once INC_PATH.'pwTools/parser/Pattern.php';

class Header extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	private $headerIndex = 0;
	private $level;
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_LINE, ' *(={1,5})', '={1,5}');
	}

	public function getAllowedModes() {
		return array(
				"#DOCUMENT", "left", "right", "notoc", "multiline", "bordererror", "borderinfo", 
				"borderwarning", "bordersuccess", "bordervalidation", "border");
	}
	
	public function onEntry() {
		$indextable = $this->getParser()->getUserInfo('indextable');

		$node = $this->getNode();
		$nodeData = $node->getData();
		$this->level = strlen($nodeData[0]);
	
		if ($node->isInside("notoc")) {
			$o = '<h'.$this->level.'>';
		} else {
			$o = '<h'.$this->level.' id="header_'.$indextable->getByIndex($this->headerIndex)->getId().'">';
			$this->headerIndex++;
		}
		
 		$htxt = trim($this->getText($node));
 		if (strlen($htxt) == 0) {
 			$o .= nop("Leere Titel sind nicht erlaubt!");
 		}
 		$o .= $htxt;
		return $o;
	}

	public function onExit() {
		return '</h'.$this->level.'>';
	}

	public function doRecursion() {
		return false;
	}

}

?>