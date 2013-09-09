<?php

//TODO Dynamic length of levels, not limited to 5!

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'pwTools/string/StringTools.php';
require_once INC_PATH.'pwTools/data/IndexItem.php';


class IndexTable {
	
	private $_cont;
	private $_lastlevel;
	private $_levels;
	
	public function __construct() {
		$this->_cont = array();
		$this->_levels = array(1=>0, 2=>0, 3=>0, 4=>0, 5=>0);
		$this->_lastlevel = 0;
	}
	
	public function add($level, $text) {
		
		if ($this->_lastlevel < $level) {
			$this->_levels[$level+1] = 0;
			$this->_levels[$level+2] = 0;
			$this->_levels[$level+3] = 0;
			$this->_levels[$level+4] = 0;
		}

		$this->_levels[$level]++;
		
		$l = $this->_levels;
		$id = StringTools::rightTrim("$l[1].$l[2].$l[3].$l[4].$l[5]", ".0");
		$item = new IndexItem($id, $level, $text);
		
		$this->_lastlevel = $level;
		
		$this->_cont[] = $item;
	}
	
	public function __toString() {
		$out = "";
		foreach($this->_cont as $item) {
			$out .= $item."\n";
		}
		return $out;
	}
	
	public function getAsArray() {
		return $this->_cont;
	}
	
	public function getByIndex($index) {
        if($index >= sizeof($this->_cont)) {
        	throw new Exception("Index out of bounds!");
        }
        return $this->_cont[$index];
    }
	
	public function getByIdOrText($idOrText) {
		$idOrText = self::normalizeText($idOrText);
		foreach ($this->_cont as $item) {
			if (self::normalizeText($item->getText()) == $idOrText || $item->getId() == $idOrText) {
				return $item;
			}
		}
	
		throw new Exception("Id or text '$idOrText' not found in this index table.");
	}

	public function getById($id) {
		foreach ($this->_cont as $item) {
			if ($item->getId() == $id) {
				return $item;
			}
		}
	
		throw new Exception("Id '$id' not found in this index table.");
	}
	
	public function getByText($text) {
		$text = self::normalizeText($text);
		foreach ($this->_cont as $item) {
			if (self::normalizeText($item->getText()) == $text) {
				return $item;
			}
		}
	
		throw new Exception("Text '$text' not found in this index table.");
	}
	
	private static function normalizeText($text) {
		return pw_s2e(utf8_strtolower(utf8_trim(pw_s2u($text))));
	}
	
}

?>