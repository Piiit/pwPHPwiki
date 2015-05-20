<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';
require_once INC_PATH.'pwTools/gui/GuiTools.php';

class LoginModule extends Module implements ModuleHandler, MenuItemProvider {  
	
	public function __construct() {
		parent::__construct($this->getName(), $this);
	}

	public function getName() {
		return "login";
	}

	public function getVersion() {
		return "20130905";
	}

	public function execute() {

		if(isset($_POST["cancel"])) {
			return;
		}
		
		$mode = pw_wiki_getmode();
		$id = pw_wiki_getid();
	
		if (isset($_POST["login"])) {
			$login = $_POST["username"];
			$pass = $_POST["password"];

			if (WIKIADMINUSER == $login && WIKIADMINPASSWORD == $pass) {
				pw_wiki_setcfg('login', array('user' => $login, 'group' => 'admin'));
				$this->setNotification("Login successful!");
				return;
			} else { 
				pw_wiki_setcfg('login', array('user' => 'guest', 'group' => 'users'));
				$this->setNotification("Login failed!", Module::NOTIFICATION_ERROR);
			}
		}
	
		if (isset($_POST["logout"])) {
			pw_wiki_setcfg('login', array('user' => 'guest', 'group' => 'users'));
			$this->setNotification("Logout successful");
			return; 
		}
	
		if (pw_wiki_getcfg('login', 'group') == 'admin') {
			$this->setDialog(GuiTools::dialogQuestion("Logout", "Do you want to logout?", "logout", "Yes", "cancel", "No", "id=".$id->getIDAsUrl()."&mode=$mode"));
			return;
		}
	
		$out = StringTools::htmlIndent("<a href='?id=".$id->getIDAsUrl()."'>&laquo; Back</a><hr />");
		$entries = GuiTools::textInput("User", "username");
		$entries .= GuiTools::passwordInput("Password", "password");
		$this->setDialog($out.GuiTools::dialogQuestion("Login", $entries, "login", "OK", "cancel", "Cancel", "id=".$id->getIDAsUrl()."&mode=$mode"));
	}

	//TODO how to return a non-link string?
	public function getMenuText() {
		if (pw_wiki_getcfg('login', 'group') == 'admin') {
			$u = pw_wiki_getcfg('login', 'user');
			return "Logout ($u)"; 
		}
		return "Login";
	}

	public function getMenuAvailability() {
		return true; 
	}

}

?>
