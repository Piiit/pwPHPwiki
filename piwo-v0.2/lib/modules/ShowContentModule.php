<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';
require_once INC_PATH.'pwTools/gui/GuiTools.php';
require_once INC_PATH.'pwTools/data/ArrayTools.php';


class ShowContentModule extends Module implements ModuleHandler {
	
	public function __construct() {
		parent::__construct($this->getName(), $this);
	}
	
	public function getName() {
		return "showcontent";
	}

	public function getVersion() {
		return "20130924";
	}

	public function permissionGranted($userData) {
		return true;
	}

	public function getMenuText() {
	}

	public function getMenuAvailability($mode) {
		return false;
	}

	public function execute() {
		$id = pw_wiki_getid();
		$wikitext = pw_wiki_showcontent($id);
		$body = file_get_contents(CFG_PATH."skeleton/wiki.html");
		$body = str_replace("{{wikitext}}", $wikitext, $body);
		$this->setDialog($body);
	}

}

?>