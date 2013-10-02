<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'pwTools/file/TextFileFormat.php';
require_once INC_PATH.'pwTools/debug/TestingTools.php';

class FileTools {
	
	public static function createFolderIfNotExist($folder) {
		if (strlen($folder) == 0) {
			throw new Exception("Folder string cannot be empty");
		}
		if (!file_exists($folder)) {
			if(!mkdir($folder, 0755, true)) {
				throw new Exception("Creating folder '$folder' failed!");
			}
		}
	}
	
	public static function copyFileIfNotExist($source, $dest) {
		if (strlen($source) == 0 || strlen($dest) == 0) {
			throw new Exception("File strings cannot be empty");
		}
		if (!file_exists($source)) {
			throw new Exception("File '$source' does not exist!");
		}
		if (!file_exists($dest)) {
			if(!copy($source, $dest)) {
				throw new Exception("Copying file '$source' to '$dest' failed!");
			}
		}
	}
	
	public static function copyMultipleFilesIfNotExist($sourceWithWildcards, $dest) {
		if (strlen($sourceWithWildcards) == 0 || strlen($dest) == 0) {
			throw new Exception("File strings cannot be empty");
		}
		if (!is_dir($dest)) {
			throw new Exception("Folder '$dest' does not exist!");
		}
		$files = glob($sourceWithWildcards);
		if (!$files || count($files) == 0) {
			throw new Exception("Pattern '$sourceWithWildcards' does not match any file!");
		}
		foreach ($files as $file) {
			self::copyFileIfNotExist($file, $dest.basename($file));
		}
	}
	
	public static function getUnixFilePermission($filename) {
		if (strlen($filename) == 0) {
			throw new Exception("File strings cannot be empty");
		}
		if (!is_file($filename)) {
			throw new Exception("File '$filename' does not exist!");
		}
		
		$perms = fileperms($filename);
		
		if (($perms & 0xC000) == 0xC000) {
			// Socket
			$info = 's';
		} elseif (($perms & 0xA000) == 0xA000) {
			// Symbolic Link
			$info = 'l';
		} elseif (($perms & 0x8000) == 0x8000) {
			// Regular
			$info = '-';
		} elseif (($perms & 0x6000) == 0x6000) {
			// Block special
			$info = 'b';
		} elseif (($perms & 0x4000) == 0x4000) {
			// Directory
			$info = 'd';
		} elseif (($perms & 0x2000) == 0x2000) {
			// Character special
			$info = 'c';
		} elseif (($perms & 0x1000) == 0x1000) {
			// FIFO pipe
			$info = 'p';
		} else {
			// Unknown
			$info = 'u';
		}
		
		// Owner
		$info .= (($perms & 0x0100) ? 'r' : '-');
		$info .= (($perms & 0x0080) ? 'w' : '-');
		$info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
		
		// Group
		$info .= (($perms & 0x0020) ? 'r' : '-');
		$info .= (($perms & 0x0010) ? 'w' : '-');
		$info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));
		
		// World
		$info .= (($perms & 0x0004) ? 'r' : '-');
		$info .= (($perms & 0x0002) ? 'w' : '-');
		$info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
		
		return $info;
	}
	
	private static function removeDirectoryRec($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") {
						self::removeDirectoryRec($dir."/".$object);
					} else {
						if(!is_writeable($dir."/".$object)) {
							throw new Exception("Your are not allowed to delete:<br />'$dir'!<br />Permissions are ".self::getUnixFilePermission($dir."/".$object));
						}
						if (!unlink($dir."/".$object)) {
							throw new Exception("Unable to remove directory '".$dir."/".$object."'");
						}
					}
				}
			}
			reset($objects);
			if (!rmdir($dir)) {
				throw new Exception("Unable to remove directory '".$dir."'");
			}
		}
	}
	
	public static function removeDirectory($dir) {
		if(!is_dir($dir)) {
			throw new Exception("Unable to find directory '".$dir."'");
		}
		self::removeDirectoryRec($dir);
	}
	
	public static function removeFile($filename) {
		if (strlen($filename) == 0) {
			throw new Exception("File strings cannot be empty");
		}
		if (!is_file($filename)) {
			throw new Exception("The file '$filename' does not exist!");
		}
		if(!is_writeable($filename)) {
			throw new Exception("Your are not allowed to delete:<br />'$filename'!<br />Permissions are ".self::getUnixFilePermission($filename));
		}
		if (!unlink($filename)) {
			throw new Exception("Unable to remove file '$filename'");
		}
	}
	
	public static function getTextFileFormat($text) {
		if (strpos($text,"\n") && strpos($text,"\r")===false) {
			return new TextFileFormat(TextFileFormat::UNIX);
		}
		if(strpos($text,"\r") && strpos($text, "\n")===false) {
			return new TextFileFormat(TextFileFormat::OLDMAC);
		}
		if (($nr = strpos($text,"\n\r")) || ($rn = strpos($text, "\r\n"))) {
			if(isset($nr)) {
				$text = str_replace("\n\r", "", $text);
			} else {
				$text = str_replace("\r\n", "", $text);
			}
			if(strpos($text,"\r") || strpos($text, "\n")) {
				return new TextFileFormat(TextFileFormat::MIXED);
			}
			return new TextFileFormat(TextFileFormat::WINDOWS);
		}
		return new TextFileFormat(TextFileFormat::UNDEFINED);
	}
	
	public static function setTextFileFormat($text, TextFileFormat $newFormat) {
		
		if($newFormat->getOrdinal() == TextFileFormat::UNDEFINED || $newFormat->getOrdinal() == TextFileFormat::MIXED) {
			throw new Exception("Cannot set text file to format ".TextFileFormat::toString($newFormat));
		}
		
		$format = self::getTextFileFormat($text);
		if($format == $newFormat) {
			return $text;
		}
		$text = str_replace(array("\n\r", "\r\n", "\r"), array("\n", "\n", "\n"), $text);
		switch ($newFormat->getOrdinal()) {
			case TextFileFormat::UNIX:
			case TextFileFormat::MAC:
				 return $text;
			break;
			case TextFileFormat::OLDMAC:
				return str_replace("\n", "\r", $text);
			break;
			case TextFileFormat::WINDOWS:
				return str_replace("\n", "\r\n", $text);
			break;
		}
		
	}
}

?>