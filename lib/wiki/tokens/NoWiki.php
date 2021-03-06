<?php

class NoWiki extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
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
		return new Pattern($this->getName(), Pattern::TYPE_SECTION, '<nowiki>', '<\/nowiki>');
	}
	
	public function getAllowedModes() {
		return array(
				"#DOCUMENT", "tablecell", "listitem", "multiline", "left", "right", "indent", 
 				"bordererror", "borderinfo", "borderwarning", "bordersuccess", "bordervalidation", 
 				"border", "bold", "underline", "italic", "monospace", "small", "big", "strike", 
 				"sub", "sup", "hi", "lo", "em", "defterm", "defitem"
				);
	}
}

?>