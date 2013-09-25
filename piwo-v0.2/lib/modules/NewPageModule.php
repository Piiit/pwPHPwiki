<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';
require_once INC_PATH.'pwTools/gui/GuiTools.php';

class NewPageModule extends Module implements ModuleHandler {
	
	public function __construct() {
		parent::__construct($this->getName(), $this);
	}
	
	public function getName() {
		return "newpage";
	}

	public function getVersion() {
		return "20130915";
	}

	public function permissionGranted($userData) {
		return $userData['group'] == 'admin';
	}
	
	public function execute() {
		
		if(isset($_POST["cancel"])) {
			return;
		}
		
		if(isset($_POST["create"])) {
			header("Location: index.php?mode=edit&id=".$_POST["id"]);
		}
		
		$id = pw_wiki_getid();
		$mode = pw_wiki_getmode();
		
		$entries = "<p>Namespaces get separated by <tt>:</tt>, e.g. <tt>Manual:Page1</tt><br />If the page already exists, it will be opened for editing.</p>";
		$entries .= GuiTools::textInput("ID", "id", pw_s2e($id->getID()));
		$this->setDialog(GuiTools::dialogQuestion("Create a new page", $entries, "create", "OK", "cancel", "Cancel", "mode=$mode&id=".$id->getID()));
	}
	
	public function getMenuText() {
		return "New";
	}

	public function getMenuAvailability($mode) {
		return true; 
	}


}

?>