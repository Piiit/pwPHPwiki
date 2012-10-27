<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'FilePath.php';


/**
 * Class: File - file actions with exception throwing
 * @author pitiz29a
 * TODO what if we change the working directory and some files are still open,
 *      do they will be touched again in the new dir? All instances must be closed
 *      when changing the working dir!
 * 
 */
class File {
	
	private $_filename;
	private $_directory;
	private $_realpath;
	private $_filePath;
	private static $_workingDirectory = './';
	private static $_oldWorkingDirectory = './';
	
	public static function setWorkingDirectory($dir) {
		if (!is_dir($dir) || !chdir($dir)) {
			throw new InvalidArgumentException("Can't set working directory. Does '$dir' exist with proper permissions?");
		}
		self::$_oldWorkingDirectory = self::$_workingDirectory;
		self::$_workingDirectory = $dir;
	}
	
	public static function getWorkingDirectory() {
		return self::$_workingDirectory;
	}
	
	public static function restoreOldWorkingDirectory() {
		self::setWorkingDirectory(self::$_oldWorkingDirectory);
	}
	
	public function __construct($filename) {
		if (!file_exists($filename)) {
			if (!touch($filename)) {
				throw new InvalidArgumentException("Can't create a new file '$filename'!");
			}
		}
		$this->_filePath = new FilePath($filename);
		var_dump($this->_filePath);
		#$this->_directory = self::realPath(self::$_workingDirectory.dirname($filename));
		#$this->_filename = basename($filename);
		#$this->_realpath = self::realPath(self::$_workingDirectory.'/'.$this->_directory.'/').$this->_filename;
	}
	
	public function rename($newName) {
		$this->_exceptionIfInvalidFileName($newName);
		if ($this->_filename == $newName) {
			return;
		}
		$this->_exceptionIfFileExists($this->_directory.$newName);
		if (!rename($this->_directory.$this->_filename, $this->_directory.$newName)) {
			throw new Exception("Renaming '$this->_filename' to '$newName' failed!" . 
					" The file '$this->_filename' is not avaiable in working directory '" .
					$this->getWorkingDirectory()."'!");
		}
		$this->_filename = $newName;
		$this->_directory = self::realPath(dirname($newName));
		$this->_realpath = self::realPath(self::$_workingDirectory.'/'.$this->_directory.'/').$this->_filename;
	}
	
	public function delete() {
		if (!unlink($this->_directory.$this->_filename)) {
			throw new Exception("Can't delete file '$this->_filename'! Does it exist with proper permissions?");
		}
	}
	
	public function move($dest) {
		//TODO check for valid dir, no filename allowed!
		$this->_exceptionIfInvalidFileName($dest);
		$this->_exceptionIfFileExists($dest);
		if (!rename($this->_directory.$this->_filename, $dest.$this->_filename)) {
			throw new Exception("Can't move file '$this->_filename'! Does it exist with proper permissions?");
		}
	}
	
	public function copy($dest) {
		//TODO check for valid dir, no filename allowed!
		$this->_exceptionIfInvalidFileName($dest);
		$this->_exceptionIfFileExists($dest);
		if (!copy($this->_directory.$this->_filename, $dest)) {
			throw new Exception("Can't copy file '$this->_filename'! Does it exist with proper permissions?");
		}
	}
	
	public function getPermissions() {
		return fileperms($this->_directory.$this->_filename);
	}
	
	public function getSize() {
		return filesize($this->_directory.$this->_filename);
	}
	
	public function getDirectory() {
		return $this->_directory;
	}
	
	public function getFileName() {
		return $this->_filename;
	}
	
	public function getContents() {
		return file_get_contents($this->getRealPath());
	}
	
	public function __toString() {
		return $this->getDirectory().$this->_filename;
	}
	
	public function getRealPath() {
		if (isset($this->_realpath)) {
			return $this->_realpath;
		}
		return self::realPath(self::$_workingDirectory.'/'.$this->_directory.'/').$this->_filename;
	}
	
	private function _exceptionIfFileExists($fn) {
		if (file_exists($fn)) {
			throw new Exception("File '$fn' already exists in working directory '" .
					self::$_workingDirectory."'!");
		}
	}
	
}

?>