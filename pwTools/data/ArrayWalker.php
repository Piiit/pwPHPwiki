<?php
if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}

require_once INC_PATH.'pwTools/data/ArrayWalkerConfig.php';

class ArrayWalker {
	private $_array = null;
	private $_arrayWalkerConfig = null;

	public function __construct($array, ArrayWalkerConfig $arrayWalkerConfig) {
		$this->_array = $array;
		$this->_arrayWalkerConfig = $arrayWalkerConfig;
	}
	
	public function getResult() {
		$this->_arrayWalker($this->_array, 0, 0);
		return $this->_arrayWalkerConfig->getResult();
	}
	
	private function _arrayWalker($item, $key, $index) {
		if (is_array($item)) {
			$index = 0;
			foreach($item as $key => $value) {
				$this->_arrayWalkerConfig->callBefore($value, $key, $index);
				if($this->_arrayWalkerConfig->doRecursion($value, $key, $index)) {
					$this->_arrayWalker($value, $key, $index);
				}
				$this->_arrayWalkerConfig->callAfter($value, $key, $index);
				$index++;
			}
		}
	}
	
}