<?php

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
		$loginGroup = WikiTools::getSessionInfo("login", "group");
		return $loginGroup == "admin";
	}

	public function execute() {

		if (isset($_POST["cancel"])) {
			return;
		}

		$mode = WikiTools::getCurrentMode();
		$id = WikiTools::getCurrentID();
		
		if(!$id->isNS()) {
			$this->setNotification("The ID '".$id->getIDAsHtmlEntities()."' is not a namespace!");
			return;
		}
		
		if (isset($_POST["delete"])) {
			$filename = WikiConfig::WIKISTORAGE.$id->getPath(); 
	
			if (is_dir($filename)) {
				
				try {
					FileTools::removeDirectory($filename);
					$newid = new WikiID($id->getID()."..");
					$this->setDialog(GuiTools::dialogInfo("Delete", "The namespace '".$id->getIDAsHtmlEntities()."' has been deleted.", "id=".$newid->getFullNSAsUrl()));
				} catch (Exception $e) {
					$this->setNotification("Unable to delete the namespace '".$id->getIDAsHtmlEntities()."'. <br />".$e->getMessage(), Module::NOTIFICATION_ERROR);
				}
			} else {
				$this->setNotification("The namespace '".$id->getIDAsHtmlEntities()."' does not exist.", Module::NOTIFICATION_ERROR);
			}
			return;
		}

// 		$out = StringTools::htmlIndent("<a href='?id=".$id->getIDAsUrl()."'>&laquo; Back</a><hr />");
		$this->setDialog(GuiTools::dialogQuestion("Delete", "Do you want to delete the namespace '".$id->getFullNSAsHtmlEntities()."'?", "delete", "Yes", "cancel", "No", "id=".$id->getIDAsUrl()."&mode=$mode"));
	}
	
}

?>
