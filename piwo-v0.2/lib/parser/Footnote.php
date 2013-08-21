<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'pwTools/parser/LexerRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRule.php';
require_once INC_PATH.'pwTools/parser/Pattern.php';

class Footnote extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	public static $footnoteList;
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
  		self::$footnoteList[] = $this->getTextFromNode($this->getNode());
  		$footnoteNumber = sizeof(self::$footnoteList);
  		$o = '<sup><a class="footnote" id="fnt__'.$footnoteNumber.'" href="#fn__'.$footnoteNumber.'">[';
  		#echo '<acronym title="Keine Ahnung">';
  		$o .= $footnoteNumber;
  		#echo '</acronym>';
  		$o .= ']';
  		return $o;
	}

	public function onExit() {
		return '</a></sup>';
	}

	public function doRecursion() {
		return false;
	}

	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_SECTION, '\(\(', '\)\)');
	}
	
	public function getAllowedModes() {
		return array("#DOCUMENT", "tablecell", "listitem", "multiline", "tablecell", "tableheader", "wptableheader", "wptablecell",
				"bordererror", "borderinfo", "borderwarning", "bordersuccess", "bordervalidation", "border",
				"bold", "underline", "italic", "monospace", "small", "big", "strike", "sub", "sup", "hi", "lo", "em",
				"align", "justify", "alignintable", "indent", "left", "right", "defitem", "defterm");
	}
}

?>