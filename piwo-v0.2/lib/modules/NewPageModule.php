<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';


class NewPageModule implements ModuleHandler {
	
	public function getName() {
		return "newpage";
	}

	public function getVersion() {
		return "20130915";
	}

	public function permissionGranted($userData) {
		return $userData['group'] == 'admin';
	}
	
	public function getDialog() {
		$id = pw_wiki_getid();
		$mode = pw_wiki_getmode();
		
		if (!isset($_SESSION["pw_wiki"]["login"]["user"]))
			return false;

		$idurl = $id->getID();
		$idText = pw_s2e($id->getID());
	
		$entries = StringTools::htmlIndent("<input type='hidden' name='mode' value='edit' />");
		$entries .= StringTools::htmlIndent("<input type='hidden' name='oldmode' value='$mode' />");
		$entries .= StringTools::htmlIndent("<input type='hidden' name='olddialog' value='newpage' />");
		$entries .= StringTools::htmlIndent("<label for='id'>ID:</label> <input type='text' class='textinput' name='id' id='id' value='$idText' />");
		$entries .= StringTools::htmlIndent("<br /><hr /><tt><small>Namensr&auml;ume werden mit : voneinander getrennt!<br />Bsp.: Handbuch:Seite1<br />Falls die Seite schon existiert, wird sie zum Bearbeiten ge&ouml;ffnet.</small></tt>");
		return GuiTools::dialogQuestion("Create a new page", $entries, "create", "OK", "cancel", "Cancel", "id=$idurl", "get");
		
	}
	
	public function getMenuText() {
		return "New";
	}

	public function getMenuAvailability($mode) {
		return true; 
	}


}

?>