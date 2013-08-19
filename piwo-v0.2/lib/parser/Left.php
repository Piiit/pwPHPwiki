<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'pwTools/parser/LexerRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRule.php';
require_once INC_PATH.'pwTools/parser/Pattern.php';

class Left extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
		return '<div class="section_left">';
	}

	public function onExit() {
		$o = '</div>';
  		$nsName = $this->getNode()->getNextSibling()->getName();
  		$nodeData = $this->getNode()->getData();
  		$cfg = isset($nodeData[1]) ? $nodeData[1] : null;
  		if ($nsName != 'right' && $cfg != "alone") {
    		$o .= '<div class="clear"></div>';
  		}
  		return $o;
	}

	public function doRecursion() {
		return true;
	}

	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_SECTION, '<left>', '<\/left>');
	}
	
	public function getAllowedModes() {
		return array("#DOCUMENT");
	}
}

?>