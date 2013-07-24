<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'pwTools/string/StringTools.php';
require_once INC_PATH.'pwTools/data/IndexItem.php';

//TODO Dynamic length of levels, not limited to 5! 
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
		
		if ($this->_lastlevel > $level) {
			$this->_levels[$level+1] = 0;
			$this->_levels[$level+2] = 0;
			$this->_levels[$level+3] = 0;
			$this->_levels[$level+4] = 0;
		}

		$this->_levels[$level]++;
		
		$l = $this->_levels;
		$id = StringTools::rightTrim("$l[1].$l[2].$l[3].$l[4].$l[5]", ".0");
		$item = new IndexItem($id, $level, $text);
		
		$this->_cont[] = $item;
	}
	
	public function __toString() {
		$out = "";
		foreach($this->_cont as $item) {
			$out .= $item."\n";
		}
		return $out;
	}
	
	public function get($index) {
		if($index >= sizeof($this->_cont)) {
			throw new Exception("Index out of bounds!");
		}
		return $this->_cont[$index];
	}
	
	public function getAsArray() {
		return $this->_cont;
	}
	
}

?>