<?php

require_once INC_PATH.'pwTools/tree/TreeWalker.php';
require_once INC_PATH.'pwTools/tree/TreeWalkerConfig.php';

class TreeCounter implements TreeWalkerConfig {
	
	private $_count = 1;  // 1, count given node first.
	
	public function callBefore($node) {
		$this->_count++;
	}
	
	public function callAfter($node) {}
	
	public function getResult() {
		return $this->_count;
	}

}