<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';

class ShowSourceModule extends Module implements ModuleHandler, MenuItemProvider {
	
	public function __construct() {
		parent::__construct($this->getName(), $this);
	}
	
	public function getName() {
		return "showsource";
	}

	public function getVersion() {
		return "20130915";
	}

	public function execute() {
		$id = pw_wiki_getid();
		$filename = WIKISTORAGE.$id->getPath().WIKIFILEEXT;
	
		if (!file_exists($filename)) {
			if($id->isNS()) {
				$this->setNotification("Namespaces have no source code to show!", Module::NOTIFICATION_ERROR);
			} else {
				$this->setNotification("File not found!", Module::NOTIFICATION_ERROR);
			}
			return;
		}
		
		$data = file_get_contents($filename);
		$data = pw_wiki_file2editor($data);
		$out = StringTools::htmlIndent("<a href='?id=".$id->getIDAsUrl()."'>&laquo; Back</a><hr />");
		$out .= StringTools::htmlIndent("<h1>Show Source Code</h1>ID = <tt>".$id->getIDAsHtmlEntities()."</tt>");
		$out .= StringTools::htmlIndent("<div id='texteditor'>", StringTools::START);
		$out .= StringTools::htmlIndent("<textarea rows='25' name='wikitxt' id='wikitxt' wrap=off' readonly>$data</textarea>");
		$out .= StringTools::htmlIndent("</div>", StringTools::END);
		$this->setDialog($out);
	}
	
	public function getMenuText() {
		return "Show&nbsp;source";
	}

	public function getMenuAvailability() {
		$loginGroup = pw_wiki_getcfg("login", "group");
		return $loginGroup != "admin"; 
	}


}

?>