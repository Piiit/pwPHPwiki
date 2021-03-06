<?php

class DeletePageModule extends Module implements ModuleHandler, PermissionProvider, MenuItemProvider {

	public function __construct() {
		parent::__construct($this->getName(), $this);
	}

	public function getName() {
		return "deletepage";
	}

	public function getVersion() {
		return "20130929";
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
		
		if (isset($_POST["delete"])) {
			$filename = WikiConfig::WIKISTORAGE.$id->getPath().WikiConfig::WIKIFILEEXT; 
	
			try {
				FileTools::removeFile($filename);
				//TODO show only a notification and redirect to the NS page if existent, else go NS levels up until startpage...
				$this->setDialog(GuiTools::dialogInfo("Delete", "The page '".$id->getIDAsHtmlEntities()."' has been deleted.", "id=".$id->getFullNSAsUrl()));
			} catch (Exception $e) {
				$this->setNotification("Unable to delete the page '".$id->getIDAsHtmlEntities()."'.<br />".$e->getMessage(), Module::NOTIFICATION_ERROR);
			}
			return;
		}

// 		$out = StringTools::htmlIndent("<a href='?id=".$id->getIDAsUrl()."'>&laquo; Back</a><hr />");
		
		if($id->getPage() == "") {
			$filename = WikiConfig::WIKISTORAGE.$id->getPath().WikiConfig::WIKINSDEFAULTPAGE.WikiConfig::WIKIFILEEXT;
			if(file_exists($filename)) {
				$this->setDialog(GuiTools::dialogQuestion("Delete", "Do you want to delete the default page of the namespace '".$id->getFullNSAsHtmlEntities()."'?", "delete", "Yes", "cancel", "No", "id=".$id->getFullNS().WikiConfig::WIKINSDEFAULTPAGE."&mode=$mode"));
			} 
			$this->setNotification("Can not delete default page of this namespace. It is not present!".$filename);
		} else {
			$this->setDialog(GuiTools::dialogQuestion("Delete", "Do you want to delete the page '".$id->getIDAsHtmlEntities()."'?", "delete", "Yes", "cancel", "No", "id=".$id->getID()."&mode=$mode"));
		}
	}
	
	public function getMenuText() {
		return "Delete";
	}

	public function getMenuAvailability() {
		return true;
	}


}

?>
