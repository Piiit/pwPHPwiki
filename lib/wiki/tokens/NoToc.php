<?php

class NoToc extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
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
		return new Pattern($this->getName(), Pattern::TYPE_SECTION, '<notoc>', '<\/notoc>');
	}
	
	public function getAllowedModes() {
		return array("#DOCUMENT", "tablecell", "listitem", "multiline",
				"bordererror", "borderinfo", "borderwarning", "bordersuccess", "bordervalidation", "border",
				"tablecell", "tableheader", "wptableheader", "wptablecell", "left", "right");
	}
}

?>