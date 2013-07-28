<?php

require_once INC_PATH.'pwTools/tree/Node.php';
require_once INC_PATH.'pwTools/tree/TreeWalkerConfig.php';

function callFunction($node, $type) {
	$prefix = 'e';
	if ($type == 0) {
		$prefix = 's';
	}

	if ($node->getName() == "#TEXT") {
		return $node->getData();
	}

	
	if (function_exists($prefix.$node->getName())) {
		return call_user_func($prefix.$node->getName(), $node, null);
	}
}

class TreeArray implements TreeWalkerConfig {
	
	private $_array = array();
	private $_noRecursion = array();
	
	public function addNoRecursionNode($nodeName) {
		$this->_noRecursion[$nodeName] = 0;
	}
	
	public function callBefore($node) {
		if ($node->getName() == "#TEXT") {
			$this->_array[] = pw_s2e($node->getData());
		} else {
			$ret = callFunction($node, 0);
			if ($ret !== null) {
				$this->_array[] = $ret;
			}
		}
	}

	public function callAfter($node) {
		if ($node->getName() == "#TEXT") {
			return;
		}
		$ret = callFunction($node, 1);
		if ($ret !== null) {
			$this->_array[] = $ret;
		}
	}

	public function getResult() {
		return $this->_array;
	}
	
	public function doRecursion(Node $node = null) {
		if ($node != null && array_key_exists($node->getName(), $this->_noRecursion)) {
			return false;
		}
		return true;
	}
	
}

?>