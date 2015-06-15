<?php

class DefItem extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
		return '<dd>';
	}

	public function onExit() {
		return '</dd>';
	}

	public function doRecursion() {
		return true;
	}

	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_SECTION, '(\n: |: )', '(?=\n)');
	}
	
	public function getAllowedModes() {
		return array("defterm");
	}
}

?>