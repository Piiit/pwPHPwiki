<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';
require_once INC_PATH.'pwTools/gui/GuiTools.php';
require_once INC_PATH.'pwTools/data/ArrayTools.php';
require_once INC_PATH.'piwo-v0.2/lib/WikiID.php';


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

	public function execute() {
		$id = pw_wiki_getid();
		$filepath = WIKISTORAGE.$id->getPath().WIKIFILEEXT;
// 		TestingTools::inform($id);
// 		TestingTools::inform(file_exists($filepath));

		if($id->getPage() == WIKINSDEFAULTPAGE) {
			header("Location: ?id=".$id->getFullNSAsUrl());
		}
		
		if($id->isNS()) {
			$filepathNS = WIKISTORAGE.$id->getPath().WIKINSDEFAULTPAGE.WIKIFILEEXT;
			if(file_exists($filepathNS)) {
				$wikitext = pw_wiki_showcontent(new WikiID($id->getFullNS().WIKINSDEFAULTPAGE));
			} else {
				$wikitext = pw_wiki_showcontent(new WikiID(":tpl:namespace"));
			}
		} elseif (file_exists($filepath)) {
			$wikitext = pw_wiki_showcontent($id);
		} else {
			$wikitext = pw_wiki_showcontent(new WikiID(":tpl:notfound"));
		}
		$body = file_get_contents(CFG_PATH."skeleton/wiki.html");
		$body = str_replace("{{wikitext}}", $wikitext, $body);
		$this->setDialog($body);
	}

}

?>