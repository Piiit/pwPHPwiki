<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'pwTools/validator/pw_isvalid.php';

/**
 * Class: FilePath - Creates an Object containing a cleaned filepath and provides some
 * 		  additional information about the filepath, such as absolute, dir or file, validity, ...
 * 		  Some static functions to perform actions on the given filepath-string
 * @author pitiz29a@gmail.com
 *
 */
class FilePath {
	
	private $_filename;
	private $_cleanedPath;
	private $_isDir = false;
	private $_isAbsolute = false;
	
	public static function isAbs($filename) {
		return (substr($filename, 0, 1) == '/');
	}
	
	public static function isDir($filename) {
		return (substr($filename, -1) == '/' || substr($filename, -3) == '/..');
	}
	
	// TODO clean function which keeps the filename at the end...
	public static function realPath($filename) {
		// Absolute paths start with '/'
		$filename = str_replace('\\', '/', $filename);
		$remember_abs = (substr($filename, 0, 1) == '/') ? '/' : '';
		$isdir = (substr($filename, -1) == '/' || substr($filename, -3) == '/..') ? true : false;
		$xpf = explode('/', $filename);
		$bn = array_pop($xpf);
		$filename = array();
		foreach ($xpf as $i => $f) {
			if ($f != ".." and $f != "." and $f != "") {
				$filename[] = $f;
			}
			if (isset($xpf[$i+1]) and $xpf[$i+1] == "..") {
				array_pop($filename);
			}
		}
		if ($bn == "..") {
			array_pop($filename);
		}
		$filename = implode($filename, "/");
		if (strlen($filename) > 0) {
			$filename .= '/';
		}
		if ($isdir) {
			if ($bn == "..") $bn = "";
			$filename = $filename.$bn;
		}
		$out = str_replace('//', '/', $remember_abs.rtrim($filename, '/').'/');
		if ($out == '/' and !$isdir) {
			$out = "";
		}
		return $out;
	}
	
	public static function isValid($filename) {
		return pw_isvalid_filename($filename);
	}
	
	
	function __construct($filename) {
		if (!self::isValid($filename)) {
			throw new InvalidArgumentException("$filename is not a valid filename!");
		}
		$this->_cleanedPath = self::realPath($filename);
		$this->_filename = basename($filename);
		$this->_isAbsolute = self::isAbs($filename);
		$this->_isDir = self::isDir($filename);
	}
	
	public function get() {
		return $this->_cleanedPath.$this->_filename;
	}
	
	/**
	 * @return the $_filename
	 */
	public function getFilename() {
		return $this->_filename;
	}

	/**
	 * @return the $_cleanedPath
	 */
	public function getPath() {
		return $this->_cleanedPath;
	}

	/**
	 * @return the $_isDir
	 */
	public function isDirectory() {
		return $this->_isDir;
	}

	/**
	 * @return the $_isAbsolute
	 */
	public function isAbsolute() {
		return $this->_isAbsolute;
	}
}

?>