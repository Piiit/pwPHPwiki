<?php

class Indent extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
		$nodeData = $this->getNode()->getData();
  		$level = strlen($nodeData[0])*10;
  		return '<div style="margin-left: '.$level.'px">';
	}

	public function onExit() {
  		return '</div>';
	}

	public function doRecursion() {
		return true;
	}

	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_SECTION, '\n:(:*) ', '\n');
	}
	
	public function getAllowedModes() {
		return array("#DOCUMENT", "tablecell", "listitem", "multiline", "bordererror", "borderinfo", "borderwarning", 
				"bordersuccess", "bordervalidation", "border");
	}
}

?>