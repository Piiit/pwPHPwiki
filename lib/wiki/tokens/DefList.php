<?php

class DefList extends ParserRule implements ParserRuleHandler, LexerRuleHandlerAbstract {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
		return '<dl>';
	}

	public function onExit() {
		return '</dl>';
	}

	public function doRecursion() {
		return true;
	}

	public function getConnectTo() {
		return array("defterm");
	}

}

?>