<?php

// TODO move common pw_dirname to FileTools...
// TODO check "isvalid"
// TODO analysis and information collections only in constructor
// TODO internal links possible (ex. ns1:ns2:page#chapter)

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'pwTools/string/encoding.php';
require_once INC_PATH.'pwTools/string/StringTools.php';
require_once INC_PATH.'pwTools/debug/TestingTools.php';
require_once INC_PATH.'piwo-v0.2/lib/common.php';

class WikiID {
	
	private $id;
	private $ns;
	private $path;
	private $fullnspath;
	private $fullns;
	private $nsArray;
	private $pg;
	
	public function __construct($id) {
		$this->id = self::s2id($id);
		
		if (!self::isvalid($this->id)) {
			throw new Exception("Invalid ID '$this->id'!");
		}
		
// 		TestingTools::inform($this->id);
		
		$this->fullns = ":".self::cleanNamespaceString($this->id);
		$this->pg = self::cleanPageString($this->id);
		$this->nsArray = preg_split("#:#", $this->fullns, null, PREG_SPLIT_NO_EMPTY);
		$this->ns = end($this->nsArray);
		$this->id = $this->fullns.$this->pg;
		$this->path = pw_url2t(str_replace(":", "/", $this->id));
		$this->fullnspath = str_replace(":", "/", $this->fullns);
	}
	
	public function getID() {
		return $this->id;
	}
	
	public function getIDAsString() {
		return pw_url2e($this->id);
	}

	public function getNS() {
		return $this->ns;
	}

	public function getPath() {
		return $this->path;
	}
	
	public function getFullNSPath() {
		return $this->fullnspath;
	}
	
	public function getFullNS() {
		return $this->fullns;
	}
	
	public function getFullNSAsString() {
		return pw_url2e($this->fullns);
	}
	
	public function getFullNSAsArray() {
		return $this->nsArray; 
	}

	public function getPage() {
		return $this->pg;
	}
	
	public function getPageAsString() {
		return pw_url2e($this->pg);
	}
	
	public function isNS() {
		return utf8_substr($this->id, -1) == ':';
	}
	
	public function isRootNS() {
		return (utf8_strlen($this->ns) == 0);
	}
	
	private static function s2id($id) {
		$id = pw_stripslashes($id);
		$id = pw_e2url($id);
// 		TestingTools::inform($id);
		$id = pw_s2url($id);
		$id = utf8_strtolower($id);
		return $id;
	}

	private static function isvalid($fullid) {
		if (0 == preg_match('#[/?*;{}\\\]+#', $fullid)) {
			return true;
		}
		return false;
	}
	
	private static function cleanPageString($fullid) {
		$fullid = explode(":", $fullid);
		$id = array_pop($fullid);
		if ($id != ".." && $id != ".") {
			return $id;
		}
		return "";
	}
	
	public static function cleanNamespaceString($ns) {
		$ns = str_replace(":", "/", $ns);
		$ns = pw_dirname($ns);
		$ns = str_replace("/", ":", $ns);
		$ns = utf8_rtrim($ns, ':').':';
		return utf8_ltrim($ns, ':');
	}
	
}

?>