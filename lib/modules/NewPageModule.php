<?php

class NewPageModule extends Module implements ModuleHandler, PermissionProvider, MenuItemProvider {
	
	public function __construct() {
		parent::__construct($this->getName(), $this);
	}
	
	public function getName() {
		return "newpage";
	}

	public function getVersion() {
		return "20130915";
	}

	public function permissionGranted() {
		$loginGroup = pw_wiki_getcfg("login", "group");
		return $loginGroup == "admin";
	}
	
	public function execute() {
		
		if(isset($_POST["cancel"])) {
			return;
		}
		
		if(isset($_POST["create"])) {
			header("Location: index.php?mode=edit&id=".pw_s2url($_POST["id"]));
		}
		
		$id = WikiTools::getCurrentID();
		$mode = WikiTools::getCurrentMode();
		
		$out = StringTools::htmlIndent("<a href='?id=".$id->getIDAsUrl()."'>&laquo; Back</a><hr />");
		$entries = "<p>Namespaces get separated by <tt>:</tt>, e.g. <tt>Manual:Page1</tt><br />If the page already exists, it will be opened for editing.</p>";
		$entries .= GuiTools::textInput("ID", "id", $id->getIDAsHtmlEntities());
		$this->setDialog($out.GuiTools::dialogQuestion("Create a new page", $entries, "create", "OK", "cancel", "Cancel", "mode=$mode&id=".$id->getIDAsUrl()));
	}
	
	public function getMenuText() {
		return "New";
	}

	public function getMenuAvailability() {
		return true; 
	}


}

?>