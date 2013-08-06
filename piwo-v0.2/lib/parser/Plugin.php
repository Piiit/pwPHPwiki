<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'pwTools/parser/LexerRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRule.php';
require_once INC_PATH.'pwTools/parser/Pattern.php';


class Plugin extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	public function getName() {
		return strtolower(__CLASS__);
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
				"alignintable", "indent", "left", "right", "bordererror", "borderinfo", "borderwarning", 
				"bordersuccess", "bordervalidation", "border"
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
		return call_user_func($funcname, $this->getParser(), $node);
	}

	public function onExit() {
	}

	public function doRecursion() {
		return false;
	}

}

?>