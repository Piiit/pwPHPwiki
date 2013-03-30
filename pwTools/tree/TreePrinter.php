<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', '.');
}

require_once INC_PATH.'pwTools/tree/TreeWalker.php';
require_once INC_PATH.'pwTools/tree/TreeWalkerConfig.php';


class TreePrinter implements TreeWalkerConfig {
	
	private $_text = "";
	private $_space = "";
	 
	public function callBefore($node) {
		$this->_text .= $this->_space.$node."\n";
		if ($node->hasChildren()) {
			$this->_space .= "  ";
		} 
	}

	public function callAfter($node) {
		if ($node->getParent()->getLastChild() === $node) {
			$this->_space = substr($this->_space, 0, -2);
		}
	}

	public function getResult() {
		return $this->_text;
	}
}