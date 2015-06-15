<?php

class Pre extends ParserRule implements ParserRuleHandler, LexerRuleHandlerAbstract {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
		return '<pre><div>';
	}

	public function onExit() {
		return '</div></pre>';
	}

	public function doRecursion() {
		return true;
	}
	
	public function getConnectTo() {
		return array("preformat");
	}

}

?>