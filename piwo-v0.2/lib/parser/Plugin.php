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
	
	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_SECTION, '~~([\w]+):*([\w]+)*', '~~');
	}
	
	public function getAllowedModes() {
		return array(
				"#DOCUMENT", "tablecell", "listitem", "multiline", "bold", "underline", "italic", 
				"monospace", "small", "big", "strike", "sub", "sup", "hi", "lo", "em",
				"multiline", "header", "ilinkpos", "internallink", "externallink", "elinkpos", 
				"tablecell", "tableheader", "wptableheader", "wptablecell", "align", "justify", 
				"alignintable", "indent", "left", "right", "error", "info", "warning", "success", "validation", "border"
				);
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