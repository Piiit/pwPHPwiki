<?php

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
	
		$mode = WikiTools::getCurrentMode();
		$id = WikiTools::getCurrentID();
	
		if (isset($_POST["rename"])) {
			try {
				if($id->isNS()) {
					$filename = WIKISTORAGE.$id->getPath();
					$newId = new WikiID($id->getFullNS()."..:".$_POST['newname'].":");
					FileTools::renameFolder($filename, $newId->getPath());
					$this->setDialog(GuiTools::dialogInfo("Rename", "The namespace '".$id->getIDAsHtmlEntities()."' has been renamed to '".$newId->getIDAsHtmlEntities()."'", "id=".$newId->getFullNSAsUrl()));
				} else {
					$filename = WIKISTORAGE.$id->getPath().WIKIFILEEXT;
					$newId = new WikiID($id->getFullNS()."..:".$_POST['newname']);
					FileTools::renameFile($filename, $newId->getPath().WIKIFILEEXT);
					$this->setDialog(GuiTools::dialogInfo("Rename", "The page '".$id->getIDAsHtmlEntities()."' has been renamed to '".$newId->getIDAsHtmlEntities()."'", "id=".$newId->getIDAsUrl()));
				}
			} catch (Exception $e) {
				$this->setNotification("Unable to rename the page '".$id->getIDAsHtmlEntities()."'.<br />".$e->getMessage(), Module::NOTIFICATION_ERROR);
			}
			return;
		}
	
		if($id->isRootNS()) {
			$this->setNotification("You can not rename the root namespace!", Module::NOTIFICATION_ERROR);
			return;
		}
		
		if($id->isNS()) {
			$entries = "Renaming the namespace '".$id->getNSAsHtmlEntities()."'?<br />";
		} else {
			$entries = "Renaming the file '".$id->getPageAsHtmlEntities()."'?<br />";
		}
		$entries .= GuiTools::textInput("New name", "newname");
		$out = StringTools::htmlIndent("<a href='?id=".$id->getIDAsUrl()."'>&laquo; Back</a><hr />");
		$this->setDialog($out.GuiTools::dialogQuestion("Rename", $entries, "rename", "OK", "cancel", "Cancel", "id=".$id->getID()."&mode=$mode"));
	}
	
	public function getMenuText() {
		return "Rename";
	}
	
	public function getMenuAvailability() {
		return true;
	}
	
}

?>