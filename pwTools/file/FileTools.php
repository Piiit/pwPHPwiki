<?php

class FileTools {
	
	public static function createFolderIfNotExist($folder) {
		if (strlen($folder) == 0) {
			throw new Exception("Folder string cannot be empty");
		}
		if (!file_exists($folder)) {
			if(!mkdir($folder, 0777, TRUE)) {
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
			FileTools::copyFileIfNotExist($file, $dest.basename($file));
		}
	}
}

?>