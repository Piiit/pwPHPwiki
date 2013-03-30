<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', '../');
}

require_once INC_PATH.'pwTools/encoding/encoding.php';
require_once INC_PATH.'pwTools/tree/Node.php';

class Token extends Node {
	
	const EOF = '#EOF';
	const DOC = '#DOCUMENT';
	const TXT = '#TEXT';
	
	private $_name = "";
	private $_completeMatch = "";
	private $_beforeMatch = "";
	private $_tokenMatch = "";
	private $_config = null;
	private $_typeExit = false;

	public function __construct($name, $beforeMatch = "", $completeMatch = "", $conf = null) {
		$this->_name = $name;
		$this->_typeExit = (substr($name, 0, 8) == "__exit__");
		if ($this->isExit()) {
			$this->_name = substr($name, 8);
		}
		$this->_beforeMatch = $beforeMatch;
		$this->_completeMatch = $completeMatch;
		$this->_tokenMatch = substr($completeMatch, strlen($beforeMatch));
		out($this->_tokenMatch);
	}
	
	public function __toString() {
		return "[Token: $this->_name: ".pw_s2e_whiteSpace($this->_tokenMatch).", LENGTH={$this->getTextLength()}, EXIT=$this->_typeExit]";
	}
	
	public function isExit() {
		return $this->_typeExit;
	}
	
	public function isEntry() {
		return !$this->isExit();
	}
	
	
	public function getTextLength() {
		return strlen($this->_completeMatch);
	}
	
	public function getTokenString() {
		return $this->_tokenMatch;
	}
	
	#public function getTextPosition() {
	#	return $this->_tokenEndCharIndex;
	#}
	
	/**
	 * @return the $_name
	 */
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * @return the $_textFull
	 */
	public function getTextFull() {
		return $this->_completeMatch;
	}

	/**
	 * @return the $_textString
	 */
	public function getTextString() {
		return $this->_beforeMatch;
	}

	/**
	 * @return the $_config
	 */
	public function getConfig() {
		return $this->_config;
	}

	
}

?>