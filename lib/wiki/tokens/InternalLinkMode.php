<?php

class InternalLinkMode extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
		return '';
	}

	public function onExit() {
		return '';
	}

	public function doRecursion() {
		return true;
	}

	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_WORD, '([a-zA-Z]*)>');
	}
	
	public function getAllowedModes() {
		return array("internallinkpos");
	}
}

?>