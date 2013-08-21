<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'pwTools/parser/LexerRuleHandlerAbstract.php';
require_once INC_PATH.'pwTools/parser/ParserRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRule.php';
require_once INC_PATH.'pwTools/parser/Pattern.php';
require_once INC_PATH.'piwo-v0.2/lib/parser/ListItem.php';


class Lists extends ParserRule implements ParserRuleHandler, LexerRuleHandlerAbstract {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
		ListItem::$listitems = array();
		$node = $this->getNode();
  		$fc = $node->getFirstChild();
  		$fcData = $fc->getData();
  		$listtype = $fcData[1] == "#" ? '<ol>' : '<ul>';
  		ListItem::$listitems[] = $fcData[1];
  		return StringFormat::htmlIndent($listtype, StringFormat::START);
	}

	public function onExit() {
		$o = "";
  		$lclevel = count(ListItem::$listitems);
  		for ($i = 0; $i < $lclevel; $i++) {
    		$listtype = array_pop(ListItem::$listitems);
    		$listtype = $listtype == "#" ? '</ol>' : '</ul>';
    		$o .= $listtype;
  		}
  		return StringFormat::htmlIndent($o, StringFormat::END);
	}

	public function doRecursion() {
		return true;
	}
	
	public function getConnectTo() {
		return array("listitem");
	}

}

?>