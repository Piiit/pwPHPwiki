<?php

// TODO move common pw_dirname to FileTools...
// TODO check "isvalid"
// TODO internal links possible (ex. ns1:ns2:page#chapter)
// TODO Function to go up in hierarchy (no need for a workaround like "$newid = new WikiID($id->getID()."..")")

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
	private $anchor;
	
	public function __construct($id) {
		$this->id = self::s2id($id);
		if (!self::isvalid($this->id)) {
			throw new Exception("Invalid ID '$this->id'!");
		}
		
		preg_match("/(.*)#(.*)/", $this->id, $lpt);
		$this->id = isset($lpt[1]) ? $lpt[1] : $this->id;
		$this->anchor = null;
		if (isset($lpt[2]) && strlen($lpt[2]) > 0) {
			$this->anchor = $lpt[2];
		}
		
		$this->fullns = ":".self::cleanNamespaceString($this->id);
		$this->pg = self::cleanPageString($this->id);
		$this->nsArray = preg_split("#:#", $this->fullns, null, PREG_SPLIT_NO_EMPTY);
		$this->ns = end($this->nsArray);
		$this->id = $this->fullns.$this->pg;
		$this->path = pw_u2t(str_replace(":", "/", $this->id));
		$this->fullnspath = pw_u2t(str_replace(":", "/", $this->fullns));
	}
	
	public static function fromPath($path) {
	
		$path = utf8_strtolower($path);
	
		$path = str_replace("//", "/", $path);
		$pathFragments = explode('/', $path);
		if ($pathFragments[0] == WIKISTORAGE) {
			$pathFragments = array_slice($pathFragments, 1, sizeof($pathFragments));
		}
	
		$path = implode('/', $pathFragments);
		$path = str_replace("/", ":", $path);
		$path = ltrim($path, ":");
		$path = StringTools::rightTrim($path, WIKIFILEEXT);
		$path = pw_s2url($path);
		return new WikiID($path);
	}
	
	
	public function getID() {
		return $this->id;
	}
	
	public function getIDAsHtmlEntities() {
		return pw_s2e($this->id);
	}
	
	public function getIDAsUrl() {
		return pw_s2url($this->id);
	}

	public function getNS() {
		return $this->ns;
	}
	
	public function getNSAsHtmlEntities() {
		return pw_s2e($this->ns);
	}
	
	public function getNSAsUrl() {
		return pw_s2url($this->ns);
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
	
	public function getFullNSAsHtmlEntities() {
		return pw_s2e($this->fullns);
	}
	
	public function getFullNSAsUrl() {
		return pw_s2url($this->fullns);
	}
	
	public function getFullNSAsArray() {
		return $this->nsArray; 
	}

	public function getPage() {
		return $this->pg;
	}
	
	public function getPageAsHtmlEntities() {
		return pw_s2e($this->pg);
	}
	
	public function getAnchor() {
		return $this->anchor;
	}
	
	public function getAnchorAsString() {
		return pw_url2e($this->anchor);
	}
	
	public function hasAnchor() {
		return $this->anchor !== null;
	}
	
	public function isNS() {
		return utf8_substr($this->id, -1) == ':';
	}
	
	public function isRootNS() {
		return (utf8_strlen($this->ns) == 0);
	}
	
	private static function s2id($id) {
		$id = pw_s2u($id);
		$id = pw_stripslashes($id);
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