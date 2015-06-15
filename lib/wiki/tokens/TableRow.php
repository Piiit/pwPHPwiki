<?php

class TableRow extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
		return '<tr>';
	}

	public function onExit() {
		return '</tr>';
	}

	public function doRecursion() {
		return true;
	}

	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_SECTION, '\n *(?=\||\^)', '\n');
	}
	
	public function getAllowedModes() {
		return array("#DOCUMENT", "multiline", "notoc");
	}
}

?>