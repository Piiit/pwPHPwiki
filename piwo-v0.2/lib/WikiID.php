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
	private $fullns;
	private $pg;
	
	public function __construct($id) {
		
		if (!self::isvalid($id)) {
			throw new Exception("Invalid ID '$id'!");
		}
		
		$this->id = self::s2id($id);
	
		$this->fullns = self::ns($this->id);
		$this->pg = self::pg($this->id);
	
		$this->ns = explode(':', rtrim($this->fullns, ':'));
		if (strlen($this->ns[0]) > 0) {
			$this->ns = array_pop($this->ns);
			$this->id = $this->fullns.$this->pg;
		} else {
			$this->ns = "";
			$this->id = $this->pg;
		}

		$this->path = str_replace(":", "/", $this->id);;
	}
	
	public function getID() {
		return $this->id;
	}

	public function getNS() {
		return $this->ns;
	}

	public function getPath() {
		return $this->path;
	}
	
	public function getFullNS() {
		return $this->fullns;
	}

	public function getPage() {
		return $this->pg;
	}
	
	private static function s2id($id) {
		$id = pw_s2u($id);
		$id = pw_stripslashes($id);
		$id = pw_s2url($id);
		$id = utf8_strtolower($id);
		return $id;
	}

	private static function isvalid($fullid) {
		$fullid = pw_url2u($fullid);
		if (0 == preg_match('#[/?*;{}\\\]+#', $fullid)) {
			return true;
		}
		return false;
	}
	
	private static function pg($fullid) {
		$fullid = explode(":", $fullid);
		$id = array_pop($fullid);
		if ($id != ".." && $id != ".") {
			return $id;
		}
		return "";
	}
	
	private static function ns($ns) {
		$ns = str_replace(":", "/", $ns);
		$ns = pw_dirname($ns);
		$ns = str_replace("/", ":", $ns);
		$ns = utf8_rtrim($ns, ':').':';
		return utf8_ltrim($ns, ':');
	}
	
}

?>