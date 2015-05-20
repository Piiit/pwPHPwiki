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
	
		$nsPageLoaded = false;
		if (!file_exists($filename)) {
			if($id->isNS()) {
				$nsDefaultId = new WikiID($id->getFullNS().WIKINSDEFAULTPAGE);
				$nsDefaultFilename = WIKISTORAGE.$nsDefaultId->getPath().WIKIFILEEXT;
				if(file_exists($nsDefaultFilename)) {
					$this->setNotification("Loading default namespace page!");
					$filename = $nsDefaultFilename;
					$id = $nsDefaultId;
					$nsPageLoaded = true;
				} else {
					$this->setNotification("This namespaces has no default page!", Module::NOTIFICATION_ERROR);
					return;
				}
			} else {
				$this->setNotification("File '".$id->getIDAsHtmlEntities()."' not found!", Module::NOTIFICATION_ERROR);
				return;
			}
		}
		
		$data = file_get_contents($filename);
		$data = pw_wiki_file2editor($data);
		$out = StringTools::htmlIndent("<a href='?id=".$id->getIDAsUrl()."'>&laquo; Back</a><hr />");
		$out .= StringTools::htmlIndent("<h1>Show Source Code</h1>ID = <tt>".$id->getIDAsHtmlEntities()."</tt>");
		$out .= $nsPageLoaded ? "<p>Attention: This is the default page of the namespace '".$id->getNSAsHtmlEntities()."'!</p>" : "";
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