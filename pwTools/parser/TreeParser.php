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
// 			TestingTools::inform("ADDING: ".$node->getData());
			$this->_array[] = pw_s2e($node->getData());
		} else {
			$parserToken = $this->getParserToken($node->getName());
			$parserToken->setNode($node);
			$parserToken->setParser($this);
			$ret = $parserToken->onEntry();
			if ($ret !== null) {
// 				TestingTools::inform("ADDING: ".$ret);
				$this->_array[] = $ret;
			}
		}
	}

	public function callAfter(Node $node) {
		if ($node->getName() == "#TEXT") {
			return;
		}
		$parserToken = $this->getParserToken($node->getName());
		$parserToken->setNode($node);
		$parserToken->setParser($this);
		$ret = $parserToken->onExit();
		if ($ret !== null) {
			$this->_array[] = $ret;
		}
	}

	public function getResult() {
		return $this->_array;
	}
	
	public function resetResult() {
		$this->_array = array();
	}
	
	public function setResult($resultArray) {
		$this->_array = $resultArray;
	}
	
	public function doRecursion(Node $node) {
		if ($node->getName() == "#TEXT") {
			return;
		}
		$parserToken = $this->getParserToken($node->getName());
		$parserToken->setNode($node);
		$parserToken->setParser($this);
// 		TestingTools::inform($parserToken->doRecursion());
		return $parserToken->doRecursion();
	}
}

?>