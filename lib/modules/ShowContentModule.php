<?php

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
		$id = WikiTools::getCurrentID();
		$filepath = WikiConfig::WIKISTORAGE.$id->getPath().WikiConfig::WIKIFILEEXT;

		if($id->getPage() == WikiConfig::WIKINSDEFAULTPAGE) {
			header("Location: ?id=".$id->getFullNSAsUrl());
		}
		
		if($id->isNS()) {
			$filepathNS = WikiConfig::WIKISTORAGE.$id->getPath().WikiConfig::WIKINSDEFAULTPAGE.WikiConfig::WIKIFILEEXT;
			if(file_exists($filepathNS)) {
				$wikitext = WikiTools::getParsedFile(new WikiID($id->getFullNS().WikiConfig::WIKINSDEFAULTPAGE));
			} else {
				$wikitext = WikiTools::getParsedFile(new WikiID(WikiConfig::WIKITEMPLATESNS."namespace"));
			}
		} elseif (file_exists($filepath)) {
			$wikitext = WikiTools::getParsedFile($id);
		} else {
			$wikitext = WikiTools::getParsedFile(new WikiID(WikiConfig::WIKITEMPLATESNS."notfound"));
		}
			
		//TODO make template filenames and paths configurable...
		$body = file_get_contents(WikiConfig::CONFIGPATH."/skeleton/wiki.tmpl");
			
// 		TestingTools::inform($wikitext);
		$body = str_replace("{{wikitext}}", $wikitext, $body);
		$this->setDialog($body);
	}

}

?>