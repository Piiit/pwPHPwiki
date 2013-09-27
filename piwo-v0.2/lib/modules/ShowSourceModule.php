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
		$out = StringTools::htmlIndent("<a href='?id=".$id->getID()."'>&laquo; Back</a> | <a href='?mode=showpages&id=".$id->getID()."'>Show Pages</a>");
		$out .= StringTools::htmlIndent("<h1>Source code</h1>ID = <tt>".$id->getID()."</tt>");
	
		if (file_exists($filename)) {
			$data = file_get_contents($filename);
			$data = pw_wiki_file2editor($data);
		
			$out = StringTools::htmlIndent("<a href='?id=".$id->getID()."'>&laquo; Back</a><hr />");
			$out .= StringTools::htmlIndent("<h1>Show Source Code</h1>ID = <tt>".$id->getID()."</tt>");
			$out .= StringTools::htmlIndent("<div id='texteditor'>", StringTools::START);
			$out .= StringTools::htmlIndent("<textarea rows='25' name='wikitxt' id='wikitxt' wrap=off'>$data</textarea>");
			$out .= StringTools::htmlIndent("</div>", StringTools::END);

			$this->setDialog($out);
		} else {
			$this->setNotification("File not found!", Module::NOTIFICATION_ERROR);
			$this->setDialog(GuiTools::dialogInfo("File does not exist!", "There is no page with ID '".$id->getID()."'!", "id=".pw_wiki_getcfg("startpage")));
		}
		
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