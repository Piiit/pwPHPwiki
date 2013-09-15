<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';


class ConfigModule implements ModuleHandler {
	
	public function getName() {
		return "config";
	}

	public function getVersion() {
		return "20130905";
	}

	//TODO Change this granularity and make a permissions Class to handle this
	public function permissionGranted($userData) {
		return $userData['group'] == 'admin';
	}
	
	public function getDialog() {
		
		$mode = pw_wiki_getmode();
		$id = pw_wiki_getid();
		
		if (isset($_POST["config"])) {
			if (isset($_POST['debug']) && $_POST['debug']) {
				pw_wiki_setcfg('debug', true);
			} else {
				pw_wiki_setcfg('debug', false);
				TestingTools::debugOff();
			}
			
			if (isset($_POST['useCache']) && $_POST['useCache']) {
				pw_wiki_setcfg('useCache', true);
			} else {
				pw_wiki_setcfg('useCache', false);
			}
			return pw_ui_getDialogInfo($this->getMenuText(), "Changes saved!", "id=".$id->getID());
		}
		
		$debug_ch = "";
		if (pw_wiki_getcfg('debug')) {
			$debug_ch = " checked='checked' ";
		}
		
		$cache_ch = "";
		if (pw_wiki_getcfg('useCache')) {
			$cache_ch = " checked='checked' ";
		}
		
		$entries = StringTools::htmlIndent("<label for='debug'>Debug-Modus:</label> <input type='checkbox' name='debug' id='debug'$debug_ch />");
		$entries .= StringTools::htmlIndent("<br />");
		$entries .= StringTools::htmlIndent("<label for='useCache'>Use cache:</label> <input type='checkbox' name='useCache' id='useCache'$cache_ch />");
		return pw_ui_getDialogQuestion($this->getMenuText(), $entries, "config", "OK", "id=".$id->getID());
	}
	
	public function getMenuText() {
		return "Configuration";
	}

	public function getMenuAvailability($mode) {
		return true; //For all modes available
	}


}

?>