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
  		$rowspan = $this->rowspantext();
  		$colspan = $this->colspantext();

  		$fc = $this->getNode()->getFirstChild();
  		$fcData = $fc->getData();
  		if ($fcData && $fcData['NAME'] !== "tablespan") {
    		$o = '<td'.$rowspan.$colspan.'>';
    		$o .= $this->getText();
    		$o .= '</td>';
  		}
  		return $o;
	}

	public function onExit() {
		return '</td>';
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
	
	private function rowspantext() {
		$nx = $this->getNode();
		$rowspans = 1;
		while($nx) {
			$nx = $nx->getNextSiblingSameChild($nx);
			$fc = $nx->getFirstChild()->getData();
			if ($fc['NAME'] == "tablespan") {
				$rowspans++;
			} else {
				break;
			}
		}
		$rowspan = $rowspans == 1 ? '' : ' rowspan="'.$rowspans.'"';
		return $rowspan;
	}
	
	private function colspantext() {
		$nx = $this->getNode();
		$colspans = 1;
		while($nx) {
			$nx = $nx->getNextSibling();
			if (!$nx->hasChildren()) {
				$colspans++;
			} else {
				break;
			}
		}
		$colspan = $colspans == 1 ? '' : ' colspan="'.$colspans.'"';
		return $colspan;
	}
}

?>