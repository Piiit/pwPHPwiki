<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';

class LoginModule extends Module implements ModuleHandler {  

	public function getName() {
		return "login";
	}

	public function getVersion() {
		return "20130905";
	}

	//TODO Change this granularity and make a permissions Class to handle this
	public function permissionGranted($userData) {
		return true;
	}

	public function getDialog() {
		global $user;
		global $pwd;
		$mode = pw_wiki_getmode();
		$id = pw_wiki_getid();
	
		if (isset($_POST["login"])) {
			$login = $_POST["username"];
			$pass = $_POST["password"];
	
			if ($user == $login && $pass == $pwd) {
				pw_wiki_setcfg('login', array('user' => $login, 'group' => 'admin'));
				return pw_ui_getDialogInfo("Login", "Benutzerlogin erfolgreich...", "id=".$id->getID());
			} else {
				unset($_SESSION["pw_wiki"]['login']["user"]);
				return pw_ui_getDialogInfo("Login", "Benutzerlogin fehlgeschlagen...", "id=".$id->getID()."&mode=$mode");
			}
		}
	
		if (isset($_POST["logout"])) {
			unset($_SESSION["pw_wiki"]['login']);
			session_destroy();
			return pw_ui_getDialogInfo("Logout", "Sie sind nun abgemeldet...", "id=".$id->getID());
		}
	
		if (pw_wiki_getcfg('login', 'group') == 'admin') {
			return pw_ui_getDialogQuestion("Logout", "Wollen Sie sich abmelden?", "logout", "Ja", "id=".$id->getID());
		}
	
		$entries = GuiTools::textInput("User", "username");
		$entries .= GuiTools::passwordInput("Password", "password");
		return pw_ui_getDialogQuestion("Login", $entries, "login", "OK", "id=".$id->getID());
	}

	//TODO how to return a non-link string?
	public function getMenuText() {
		if (pw_wiki_getcfg('login', 'group') == 'admin') {
			$u = pw_wiki_getcfg('login', 'user');
			return "Logout ($u)"; 
		}
		return "Login";
	}

	public function getMenuAvailability($mode) {
		return true; //For all modes available
	}

}

?>
