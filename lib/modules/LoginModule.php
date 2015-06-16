<?php

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
		
		$mode = WikiTools::getCurrentMode();
		$id = WikiTools::getCurrentID();
	
		if (isset($_POST["login"])) {
			$login = $_POST["username"];
			$pass = $_POST["password"];

			if (WikiConfig::WIKIADMINUSER == $login && WikiConfig::WIKIADMINPASSWORD == $pass) {
				WikiTools::setSessionInfo(
					'login', 
					array(
						'user' => $login, 
						'group' => 'admin'
					)
				);
				$this->setNotification("Login successful!");
				return;
			} else { 
				WikiTools::setSessionInfo(
					'login', 
					array(
						'user' => 'guest', 
						'group' => 'users'
					)
				);
				$this->setNotification("Login failed!", Module::NOTIFICATION_ERROR);
			}
		}
	
		if (isset($_POST["logout"])) {
			WikiTools::setSessionInfo('login', array('user' => 'guest', 'group' => 'users'));
			$this->setNotification("Logout successful");
			return; 
		}
	
		if (WikiTools::getSessionInfo('login', 'group') == 'admin') {
			$this->setDialog(
				GuiTools::dialogQuestion(
					"Logout", "Do you want to logout?", 
					"logout", "Yes", 
					"cancel", "No", 
					"id=".$id->getIDAsUrl()."&mode=$mode"
				)
			);
			return;
		}
	
		$this->setDialog(
			StringTools::htmlIndent(
				"<a href='?id=".$id->getIDAsUrl()."'>&laquo; Back</a>".
				"<hr />"
			) .
			GuiTools::dialogQuestion(
				"Login", 
				GuiTools::textInput("User", "username") .
				GuiTools::passwordInput("Password", "password"),
				"login", "OK", 
				"cancel", "Cancel", 
				"id=".$id->getIDAsUrl()."&mode=$mode"
			)
		);
	}

	//TODO how to return a non-link string?
	public function getMenuText() {
		if (WikiTools::getSessionInfo('login', 'group') == 'admin') {
			$u = WikiTools::getSessionInfo('login', 'user');
			return "Logout ($u)"; 
		} 
		return "Login";
	}

	public function getMenuAvailability() {
		return true; 
	}

}

?>
