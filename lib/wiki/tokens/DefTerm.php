<?php

class DefTerm extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
		return '<dt>';
	}

	public function onExit() {
		return '</dt>';
	}

	public function doRecursion() {
		return true;
	}

	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_SECTION, '\n( *)\; ', '\n');
	}
	
	public function getAllowedModes() {
		return array("#DOCUMENT", "tablecell", "listitem", "multiline",
				"bordererror", "borderinfo", "borderwarning", "bordersuccess", "bordervalidation", "border",
				"align", "justify", "alignintable", "indent", "left", "right");
	}
}

?>