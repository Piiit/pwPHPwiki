<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';

class ConfigModule implements Module {
	
	public function getName() {
		return strtolower(__CLASS__);
	}

	public function getVersion() {
		return "20130905";
	}

	public function activateIf() {
		return array('config', 'configSave');
	}

	public function availableFor() {
		return array('admin');
	}
	
	public function getDialog() {
		if (!isset($_SESSION["pw_wiki"]["login"]["user"]))
			return false;
		
		$mode = pw_wiki_getmode();
		$id = pw_wiki_getid();
		
		if (isset($_POST["configSave"])) {
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
			return;
		}
		
		$debug_ch = "";
		if (pw_wiki_getcfg('debug')) {
			$debug_ch = " checked='checked' ";
		}
		
		$cache_ch = "";
		if (pw_wiki_getcfg('useCache')) {
			$cache_ch = " checked='checked' ";
		}
		
		$entries = StringTools::htmlIndent("<input type='hidden' name='oldmode' value='$mode' />");
		$entries .= StringTools::htmlIndent("<label for='debug'>Debug-Modus:</label> <input type='checkbox' name='debug' id='debug'$debug_ch />");
		$entries .= StringTools::htmlIndent("<br />");
		$entries .= StringTools::htmlIndent("<label for='useCache'>Use cache:</label> <input type='checkbox' name='useCache' id='useCache'$cache_ch />");
		return pw_ui_getDialogQuestion("Einstellungen", $entries, "configSave", "OK", "id=".$id->getID()."&mode=$mode");
	}
	
	public function getMenuText() {
		return "Einstellungen";
	}

	public function getMenuAvailability($mode) {
		return true; //For all menu 
	}


}

?>