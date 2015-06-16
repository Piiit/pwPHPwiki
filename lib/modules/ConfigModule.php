<?php

class ConfigModule extends Module implements ModuleHandler, PermissionProvider, MenuItemProvider {
	
	public function __construct() {
		parent::__construct($this->getName(), $this); 
	}
	
	public function getName() {
		return "config";
	}

	public function getVersion() {
		return "20130905";
	}

	public function permissionGranted() {
		$loginGroup = WikiTools::getSessionInfo("login", "group");
		return $loginGroup == "admin";
	}
	
	public function execute() {
		
		if (isset($_POST["cancel"])) {
			return;	
		}
		
		$mode = WikiTools::getCurrentMode();
		$id = WikiTools::getCurrentID();
		
		if (isset($_POST['clearsession'])) {
			WikiTools::unsetSessionInfo();
			pw_wiki_loadconfig();
			unset($_POST['clearsession']);
			$this->setNotification("Session cleared!");
			return;
		}
		
		if (isset($_POST["config"])) {
			WikiTools::setSessionInfo('debug', ArrayTools::getIfExistsNotNull(false, $_POST, 'debug'));
			if (WikiTools::getSessionInfo('debug') == false) {
				TestingTools::debugOff();
			}
			WikiTools::setSessionInfo('useCache', ArrayTools::getIfExistsNotNull(false, $_POST, 'useCache'));
			$this->setNotification("Changes saved!");
			return;
		}
		
		$out = StringTools::htmlIndent("<a href='?id=".$id->getIDAsUrl()."'>&laquo; Back</a><hr />");
		$entries = GuiTools::checkbox("Debug-Modus", "debug", WikiTools::getSessionInfo('debug'));
		$entries .= GuiTools::checkbox("Use cache", "useCache", WikiTools::getSessionInfo('useCache'));
		$entries .= GuiTools::button("Clear Session", "clearsession"); 
		$this->setDialog($out.GuiTools::dialogQuestion($this->getMenuText(), $entries, "config", "OK", "cancel", "Cancel", "id=".$id->getIDAsUrl()."&mode=$mode"));
	}
	
	public function getMenuText() {
		return "Configuration";
	}

	public function getMenuAvailability() {
		return true;
	}


}

?>