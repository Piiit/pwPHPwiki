<?php

class Newline extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
		return '<br />';
	}

	public function onExit() {
		return '';
	}

	public function doRecursion() {
		return true;
	}

	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_WORD, '\\\\\\\\');
	}
	
	public function getAllowedModes() {
		return array("#DOCUMENT", "tablecell", "listitem", "multiline", "bordererror", "borderinfo", "borderwarning", "bordersuccess", 
				"bordervalidation", "border", "bold", "underline", "italic", "monospace", "small", "big", "strike", "sub", "sup", "hi", 
				"lo", "em",	"align", "justify", "alignintable", "indent", "left", "right", "tablecell", "tableheader", "wptableheader", 
				"wptablecell", "footnote");
	}
}

?>