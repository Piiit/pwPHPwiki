<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'pwTools/parser/ParserTokenHandler.php';
require_once INC_PATH.'pwTools/parser/ParserToken.php';


class Plugin extends ParserToken implements ParserTokenHandler {
	
	public function getName() {
		return 'plugin';
	}
	
	public function onEntry() {
		$node = $this->getNode();
		$nodeData = $node->getData();
		$pluginname = strtolower($nodeData[0]);
		$funcname = "plugin_".$pluginname;
		if (!function_exists($funcname)) {
			return nop("PLUGIN '$pluginname' nicht verf&uuml;gbar.",false);
		}
		return call_user_func($funcname, $node);
	}

	public function onExit() {
	}

	public function doRecursion() {
		return true;
	}

}

?>