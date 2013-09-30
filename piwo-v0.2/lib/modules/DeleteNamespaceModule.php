<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';
require_once INC_PATH.'pwTools/gui/GuiTools.php';
require_once INC_PATH.'pwTools/data/ArrayTools.php';

class DeleteNamespaceModule extends Module implements ModuleHandler, PermissionProvider {

	public function __construct() {
		parent::__construct($this->getName(), $this);
	}

	public function getName() {
		return "deletenamespace";
	}

	public function getVersion() {
		return "20130930";
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
		
		if(!$id->isNS()) {
			$this->setNotification("The ID '".$id->getIDAsString()."' is not a namespace!");
			return;
		}
		
		if (isset($_POST["delete"])) {
			$filename = WIKISTORAGE.$id->getPath(); 
	
			if (is_dir($filename)) {
				
				try {
					FileTools::removeDirectory($filename);
					$newid = new WikiID($id->getID()."..");
					$this->setDialog(GuiTools::dialogInfo("Delete", "The namespace '".$id->getIDAsString()."' has been deleted.", "id=".$newid->getFullNS()));
				} catch (Exception $e) {
					$this->setNotification("Unable to delete the namespace '".$id->getIDAsString()."'.", Module::NOTIFICATION_ERROR);
				}
			} else {
				$this->setNotification("The namespace '".$id->getIDAsString()."' does not exist.", Module::NOTIFICATION_ERROR);
			}
			return;
		}

		$out = StringTools::htmlIndent("<a href='?id=".$id->getID()."'>&laquo; Back</a><hr />");
		$this->setDialog(GuiTools::dialogQuestion("Delete", "Do you want to delete the namespace '".$id->getFullNSAsString()."'?", "delete", "Yes", "cancel", "No", "id=".$id->getID()."&mode=$mode"));
	}
	
}

?>
