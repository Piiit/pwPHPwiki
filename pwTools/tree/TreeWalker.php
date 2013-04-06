<?php

require_once INC_PATH.'pwTools/tree/Node.php';
require_once INC_PATH.'pwTools/tree/TreeWalkerConfig.php';

class TreeWalker {
	private $_rootNode = null;
	private $_treeWalkerConfig = null;

	public function __construct($rootNode, $treeWalkerConfig) {
		if ($rootNode instanceof Node) {
			$this->_rootNode = $rootNode;
		} else {
			throw new InvalidArgumentException("RootNode must be of type Node!");
		}
		if ($treeWalkerConfig instanceof TreeWalkerConfig) { 
			$this->_treeWalkerConfig = $treeWalkerConfig;
		} else {
			throw new InvalidArgumentException("Config must be of type TreeWalkerConfig!");
		}
	}
	
	
	public function getResult() {
		$this->_treeWalker($this->_rootNode, null);
		return $this->_treeWalkerConfig->getResult();
	}
	
	
	private function _treeWalker($node) {
		if ($node->hasChildren()) {
			for ($node = $node->getFirstChild(); $node != null; $node = $node->getNextSibling()) {
				if (!$node instanceof Node) {
					throw new Exception("TreeWalker-Nodes must be an instance of Node!");
				}
				$this->_treeWalkerConfig->callBefore($node);
				if ($node->hasChildren()) {
					$this->_treeWalker($node);
				}
				$this->_treeWalkerConfig->callAfter($node);
			}
		}
	}
	
}