<?php

class BorderSuccess extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_SECTION, '<success>', '<\/success>');
	}
	
	public function onEntry() {
		return '<div class="section_success">';
	}

	public function onExit() {
		return '</div>';
	}

	public function doRecursion() {
		return true;
	}
	
 	public function getAllowedModes() {
 		return array("#DOCUMENT", "multiline", "left", "right", "indent", "tablecell");
	}

}

?>