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
		$filepath = WIKISTORAGE.$id->getPath().WIKIFILEEXT;
//  		TestingTools::inform("executingXXX: ".$id->getIDAsUrl());
//  		TestingTools::inform(file_exists($filepath));


		if($id->getPage() == WIKINSDEFAULTPAGE) {
// 			TestingTools::inform($id);
			header("Location: ?id=".$id->getFullNSAsUrl());
		}
		
		
		if($id->isNS()) {
			$filepathNS = WIKISTORAGE.$id->getPath().WIKINSDEFAULTPAGE.WIKIFILEEXT;
			if(file_exists($filepathNS)) {
				$wikitext = pw_wiki_get_parsed_file(new WikiID($id->getFullNS().WIKINSDEFAULTPAGE));
			} else {
				$wikitext = pw_wiki_get_parsed_file(new WikiID(WIKITEMPLATESNS."namespace"));
			}
		} elseif (file_exists($filepath)) {
			$wikitext = pw_wiki_get_parsed_file($id);
		} else {
			$wikitext = pw_wiki_get_parsed_file(new WikiID(WIKITEMPLATESNS."notfound"));
		}
			
		//TODO make template filenames and paths configurable...
		$body = file_get_contents(CFG_PATH."skeleton/wiki.tmpl");
			
// 		TestingTools::inform($wikitext);
		$body = str_replace("{{wikitext}}", $wikitext, $body);
		$this->setDialog($body);
	}

}

?>