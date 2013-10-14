<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';
require_once INC_PATH.'pwTools/gui/GuiTools.php';
require_once INC_PATH.'pwTools/data/ArrayTools.php';

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
	
		$mode = pw_wiki_getmode();
		$id = pw_wiki_getid();
	
		if (isset($_POST["move"])) {
			try {
				if($id->isNS()) {
					$filename = WIKISTORAGE.$id->getPath();
					$newId = new WikiID($id->getFullNS()."..:".$_POST['newname'].":");
					FileTools::move($filename, $newId->getPath());
					$this->setDialog(GuiTools::dialogInfo("Move", "The namespace '".$id->getIDAsHtmlEntities()."' has been moved to '".$newId->getIDAsHtmlEntities()."'", "id=".$newId->getFullNSAsUrl()));
				} else {
					$filename = WIKISTORAGE.$id->getPath().WIKIFILEEXT;
					$newId = new WikiID($id->getFullNS()."..:".$_POST['newname']);
					FileTools::move($filename, $newId->getPath().WIKIFILEEXT);
					$this->setDialog(GuiTools::dialogInfo("Move", "The page '".$id->getIDAsHtmlEntities()."' has been moved to '".$newId->getIDAsHtmlEntities()."'", "id=".$newId->getIDAsUrl()));
				}
			} catch (Exception $e) {
				$this->setNotification("Unable to move the page '".$id->getIDAsHtmlEntities()."'.<br />".$e->getMessage(), Module::NOTIFICATION_ERROR);
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
		$entries .= GuiTools::textInput("New name", "newname");
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