<?php

class Align extends ParserRule implements ParserRuleHandler, LexerRuleHandler { 
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
		$nodeData = $this->getNode()->getData();
		$type = $nodeData[0];
		if ($type == '>') {
			return '<div align="right">';
		} else {
			return '<div align="center">';
		}
	}

	public function onExit() {
		return '</div>';
	}

	public function doRecursion() {
		return true;
	}

	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_SECTION, '\n:>(>*) ', '\n');
	}
	
	public function getAllowedModes() {
		return array("#DOCUMENT", "tablecell", "listitem", "multiline", "bordererror", "borderinfo", "borderwarning", 
				"bordersuccess", "bordervalidation", "border", "align", "justify", "alignintable", "indent", "left", "right");
	}
}

?>