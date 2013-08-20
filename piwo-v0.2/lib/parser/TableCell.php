<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'pwTools/parser/LexerRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRule.php';
require_once INC_PATH.'pwTools/parser/Pattern.php';

class TableCell extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
		$o = "";
  		$chid = $this->getNode()->getChildIndex();
  		$rowspan = self::getRowspanText($this->getNode());
  		$colspan = self::getColspanText($this->getNode());

  		if($this->getNode()->hasChildren()) {
	  		$fc = $this->getNode()->getFirstChild();
	  		$fcData = $fc->getData();
	  		if ($fcData && $fc->getName() !== "tablespan") {
	    		$o = '<td'.$rowspan.$colspan.'>';
	    		$o .= $this->getText();
	    		$o .= '</td>';
	  		}
  		}
  		return $o;
	}

	public function onExit() {
		return '';
	}

	public function doRecursion() {
		return false;
	}

	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_SECTION, '\|', '(?=\||\^|\n)'); 
	}
	
	public function getAllowedModes() {
		return array("tablerow");
	}
	
	public static function getRowspanText(Node $node) {
		$nx = $node;
		$rowspans = 0;
		while($nx && $nx->hasChildren()) {
			if ($nx->getFirstChild()->getName() == "tablespan") {
				$rowspans++;
			}
			try {
				$nx = $nx->getNextSiblingSameChild($nx);
			} catch (Exception $e) {
				break;
			}
			
		}
		$rowspan = $rowspans == 0 ? '' : ' rowspan="'.$rowspans.'"';
		return $rowspan;
	}
	
	public static function getColspanText(Node $node) {
		$nx = $node->getNextSibling();
		$colspans = 1;
		while($nx && !$nx->hasChildren()) {
			$colspans++;
			$nx = $nx->getNextSibling();
		}
		$colspan = $colspans == 1 ? '' : ' colspan="'.$colspans.'"';
		return $colspan;
	}
}

?>