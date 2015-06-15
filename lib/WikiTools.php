<?php
class WikiTools {
	
	static function getCurrentID() {
		$id = ":".WIKINSDEFAULTPAGE;
		if(isset($_GET['id']) && $_GET['id'] != "") {
			$id = $_GET['id'];
		}
		return new WikiID($id);
	}
	
	static function getCurrentMode() {
		return isset($_GET['mode']) ? $_GET['mode'] : null;
	}
	

	static function getHtmlTitle($sep = " &laquo; ") {
		$id = WikiTools::getCurrentID();
		$title = "";
		foreach ($id->getFullNSAsArray() as $namespace) {
			$title = pw_s2e(utf8_ucfirst($namespace)).$sep.$title;
		}
	
		if(!$id->isNS() && $id->getPage() != WIKINSDEFAULTPAGE) {
			$title = pw_s2e(utf8_ucfirst($id->getPage())).$sep.$title;
		}
	
		$title .= pw_s2e(utf8_ucfirst(pw_wiki_getcfg('wikititle')));
		return $title;
	}
}

?>