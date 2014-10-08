<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'pwTools/parser/LexerRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRule.php';
require_once INC_PATH.'pwTools/parser/Pattern.php';
require_once INC_PATH.'piwo-v0.2/lib/WikiPluginHandler.php';


class Plugin extends ParserRule implements ParserRuleHandler, LexerRuleHandler, WikiPluginHandler {
	
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
				"multiline", "header", "internallinkpos", "internallinktext", "externallink", "externallinkpos", 
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
			return nop("PLUGIN '$pluginname' not found.",false);
		}
		return call_user_func($funcname, $this->getParser(), $node);
	}

	public function onExit() {
	}

	public function doRecursion() {
		return false;
	}

	public function runBefore(Parser $parser) {
		var_dump($parser);
		$this->getParser()->setUserInfo(
				'indextable', 
				WikiTocTools::createIndexTable($this->getParser(), $this->getLexer()->getRootNode())
				);
	}

	public function runOnTokenFound() {
	}

	public function runAfter() {
	}

	public function getPluginName() {
		return "toc";
	}

}

?>