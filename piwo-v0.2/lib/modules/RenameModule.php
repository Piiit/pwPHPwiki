<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';
require_once INC_PATH.'pwTools/gui/GuiTools.php';
require_once INC_PATH.'pwTools/data/ArrayTools.php';

class RenameModule extends Module implements ModuleHandler, PermissionProvider, MenuItemProvider {

	public function __construct() {
		parent::__construct($this->getName(), $this);
	}
	
	public function getName() {
		return "rename";
	}
	
	public function getVersion() {
		return "20131010";
	}
	
	public function permissionGranted() {
		$loginGroup = pw_wiki_getcfg("login", "group");
		return $loginGroup == "admin";
	}
	
	public function execute() {
	
		if (isset($_POST["cancel"])) {
			return;
		}
	
		$mode = pw_wiki_getmode();
		$id = pw_wiki_getid();
	
		if (isset($_POST["rename"])) {
			$filename = WIKISTORAGE.$id->getPath().WIKIFILEEXT;
	
			try {
				FileTools::renameFile($filename, $newFilename);
				//TODO show only a notification and redirect to the NS page if existent, else go NS levels up until startpage...
				$this->setDialog(GuiTools::dialogInfo("Delete", "The page '".$id->getIDAsHtmlEntities()."' has been deleted.", "id=".$id->getFullNSAsUrl()));
			} catch (Exception $e) {
				$this->setNotification("Unable to delete the page '".$id->getIDAsHtmlEntities()."'.<br />".$e->getMessage(), Module::NOTIFICATION_ERROR);
			}
			return;
		}
	
		$out = StringTools::htmlIndent("<a href='?id=".$id->getIDAsUrl()."'>&laquo; Back</a><hr />");
		$this->setDialog(GuiTools::dialogQuestion("Delete", "Do you want to delete the page '".$id->getPageAsHtmlEntities()."'?", "delete", "Yes", "cancel", "No", "id=".$id->getID()."&mode=$mode"));
	}
	
	public function getMenuText() {
		return "Delete";
	}
	
	public function getMenuAvailability() {
		return true;
	}
	
}

?>