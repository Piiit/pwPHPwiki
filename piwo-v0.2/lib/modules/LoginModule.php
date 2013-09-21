<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/InfoBoxProvider.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';

class LoginModule extends Module implements ModuleHandler, InfoBoxProvider {  

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
				return null;
			} else {
				pw_wiki_setcfg('login', array('user' => 'guest', 'group' => 'users'));
				return GuiTools::dialogInfo("Login", "Login failed...", "id=".$id->getID()."&mode=$mode");
			}
		}
	
		if (isset($_POST["logout"])) {
			pw_wiki_setcfg('login', array('user' => 'guest', 'group' => 'users'));
			return null; 
		}
	
		if (pw_wiki_getcfg('login', 'group') == 'admin') {
			return GuiTools::dialogQuestion("Logout", "Do you want to logout?", "logout", "Yes", "cancel", "No", "id=".$id->getID()."&mode=$mode");
		}
	
		$entries = GuiTools::textInput("User", "username");
		$entries .= GuiTools::passwordInput("Password", "password");
		return GuiTools::dialogQuestion("Login", $entries, "login", "OK", "cancel", "Cancel", "id=".$id->getID()."&mode=$mode");
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

	public function getText() {
		//FIXME is this the right way? infoboxprovider?
	}

	public function isError() {
	}

}

?>
