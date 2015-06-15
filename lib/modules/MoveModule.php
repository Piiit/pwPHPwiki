<?php

class MoveModule extends Module implements ModuleHandler, PermissionProvider, MenuItemProvider {

	public function __construct() {
		parent::__construct($this->getName(), $this);
	}
	
	public function getName() {
		return "move";
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
	
		if (isset($_POST["move"])) {
			try {
				$newId = new WikiID(":".$_POST['newname'].":");
				if($id->isNS()) {
					FileTools::moveFolder(WIKISTORAGE.$id->getPath(), WIKISTORAGE.$newId->getPath());
					$this->setDialog(GuiTools::dialogInfo("Move", "The namespace '".$id->getIDAsHtmlEntities()."' has been moved to '".$newId->getIDAsHtmlEntities()."'", "id=".$newId->getFullNSAsUrl()));
				} else {
					FileTools::moveFile(WIKISTORAGE.$id->getPath().WIKIFILEEXT, WIKISTORAGE.$newId->getPath());
					$this->setDialog(GuiTools::dialogInfo("Move", "The page '".$id->getIDAsHtmlEntities()."' has been moved to '".$newId->getIDAsHtmlEntities()."'", "id=".$newId->getIDAsUrl()));
				}
			} catch (Exception $e) {
				$this->setNotification("Unable to move '".$id->getIDAsHtmlEntities()."'.<br />".$e->getMessage(), Module::NOTIFICATION_ERROR);
			}
			return;
		}
	
		if($id->isRootNS()) {
			$this->setNotification("You can not move the root namespace!", Module::NOTIFICATION_ERROR);
			return;
		}
		
		if($id->isNS()) {
			$entries = "Moving the namespace '".$id->getNSAsHtmlEntities()."'?<br />";
		} else {
			$entries = "Moving the file '".$id->getPageAsHtmlEntities()."'?<br />";
		}
		$entries .= GuiTools::textInput("Destination", "newname");
		$out = StringTools::htmlIndent("<a href='?id=".$id->getIDAsUrl()."'>&laquo; Back</a><hr />");
		$this->setDialog($out.GuiTools::dialogQuestion("Move", $entries, "move", "OK", "cancel", "Cancel", "id=".$id->getID()."&mode=$mode"));
	}
	
	public function getMenuText() {
		return "Move";
	}
	
	public function getMenuAvailability() {
		return true;
	}
	
}

?>