<?php

class Bold extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
		return '<b>';
	}

	public function onExit() {
		return '</b>';
	}

	public function doRecursion() {
		return true;
	}

	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_SECTION, '\*\*', '\*\*');
	}
	
	public function getAllowedModes() {
		return array("#DOCUMENT", "defitem", "footnote", "align", "justify", "alignintable", "indent", "left", "right",
				"tablecell", "tableheader", "wptableheader", "wptablecell", "tablecell", "listitem", "multiline", 
				"bordererror", "borderinfo", "borderwarning", "bordersuccess", "bordervalidation", "border",
				"bold", "underline", "italic", "monospace", "small", "big", "strike", "sub", "sup", "hi", "lo", "em");
	}
}

?>