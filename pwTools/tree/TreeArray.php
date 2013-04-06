<?php

require_once INC_PATH.'pwTools/tree/Node.php';
require_once INC_PATH.'pwTools/tree/TreeWalkerConfig.php';

// TODO refactor getArray with a TreeWalker adapter!!!
function getArray($node, $arr = array()) {


}
function callFunction($node, $type) {
	$prefix = 'e';
	if ($type == 0) {
		$prefix = 's';
	}

	if ($node->getName() == "#TEXT") {
		return $node->getData();
	}

	
	if (function_exists($prefix.$node->getName())) {
		#echo $prefix.$node->getName();
		return call_user_func($prefix.$node->getName(), $node, null);
	}
}

class TreeArray implements TreeWalkerConfig {
	
	private $_array = array();
	
	public function __construct() {
		//@TODO: BUG! no globals here: unbound the class from the rest of the world!!!
		global $norecursion;
		if (!is_array($norecursion)) {
			throw new InvalidArgumentException("DATATYPE for NoRecursion has to be Array. FIXME=no globals!!!!!!");
		}
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
	
}

?>