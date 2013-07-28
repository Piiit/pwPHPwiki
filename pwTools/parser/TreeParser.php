<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
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

class TreeParser implements TreeWalkerConfig { 
	
	private $_registeredHandler = array();
	private $_array = array();
		
	public function registerParserToken($name, ParserTokenHandler $tokenHandler) {
		if(array_key_exists($name, $this->_registeredHandler)) {
			throw new Exception("Parser Token '$name' already registered!");
		}
		$this->_registeredHandler[$name] = $tokenHandler;
	}
	
	private function getParserToken($name) {
		if(!array_key_exists($name, $this->_registeredHandler)) {
			throw new Exception("Parser Token '$name' does not exist!");
		}
		return $this->_registeredHandler[$name];
	}
	
	public function callBefore(Node $node) {
		if ($node->getName() == "#TEXT") {
			$this->_array[] = pw_s2e($node->getData());
		} else {
			$ret = $this->getParserToken($node->getName())->onEntry($node);
			if ($ret !== null) {
				$this->_array[] = $ret;
			}
		}
	}

	public function callAfter(Node $node) {
		if ($node->getName() == "#TEXT") {
			return;
		}
		$ret = $this->getParserToken($node->getName())->onExit($node);
		if ($ret !== null) {
			$this->_array[] = $ret;
		}
	}

	public function getResult() {
		return $this->_array;
	}
	
}

?>