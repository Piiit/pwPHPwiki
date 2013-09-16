<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';


class ShowSourceModule implements ModuleHandler {
	
	public function getName() {
		return "showsource";
	}

	public function getVersion() {
		return "20130915";
	}

	//FIXME: This is just a workaround for invisibility if editing is allowed, see TODO below!
	public function permissionGranted($userData) {
		return $userData['group'] != 'admin';
	}
	
	public function getDialog() {
		$id = pw_wiki_getid();
		
		$filename = pw_wiki_getcfg('storage').$id->getPath().pw_wiki_getcfg('fileext');
		$out = StringTools::htmlIndent("<a href='?id=".$id->getID()."'>&laquo; Back</a> | <a href='?mode=showpages&id=".$id->getID()."'>Show Pages</a>");
		$out .= StringTools::htmlIndent("<h1>Source code</h1>ID = <tt>".$id->getID()."</tt>");
	
		if (file_exists($filename) and !isset($_POST['save'])) {
			$data = file_get_contents($filename);
			$data = pw_wiki_file2editor($data);
			$out .= StringTools::htmlIndent("<textarea cols='80' rows='25' id='wikitxt' readonly='readonly' wrap='off'>$data</textarea>");
			return $out;
		}
		
		throw new Exception("File not found!");
		
	}
	
	public function getMenuText() {
		return "Show source";
	}

	//TODO should be possible to check against persmissions etc.
	public function getMenuAvailability($mode) {
		//ex. return $userData['group'] != 'admin';
		return true; 
	}


}

?>