<?php

class Table extends ParserRule implements ParserRuleHandler, LexerRuleHandlerAbstract {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
		return '<div class="tablediv"><table>';
	}

	public function onExit() {
		return '</table></div>';
	}

	public function doRecursion() {
		return true;
	}
	
	public function getConnectTo() {
		return array("tablerow");
	}

}

?>