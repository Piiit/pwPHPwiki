<?php

class HorizontalRule extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
		return '<hr />';
	}

	public function onExit() {
		return '';
	}

	public function doRecursion() {
		return true;
	}

	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_LINE, '----');
	}
	
	public function getAllowedModes() {
		return array("#DOCUMENT", "multiline", "left", "right", "bordererror", "borderinfo", 
				"borderwarning", "bordersuccess", "bordervalidation", "border");
	}
}

?>