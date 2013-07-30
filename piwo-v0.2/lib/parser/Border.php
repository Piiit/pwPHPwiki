<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'pwTools/parser/ParserTokenHandler.php';
require_once INC_PATH.'pwTools/parser/ParserToken.php';


class Border extends ParserToken implements ParserTokenHandler {
	
	public function getName() {
		return 'border';
	}
	
	public function onEntry() {
		return '<div class="section_border">';
	}

	public function onExit() {
		return '</div>';
	}

	public function doRecursion() {
		return true;
	}

}

?>