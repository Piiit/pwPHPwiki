<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/JavaScriptProvider.php';

class EditModule extends Module implements ModuleHandler, JavaScriptProvider, PermissionProvider, MenuItemProvider {
	
	public function __construct() { 
		parent::__construct($this->getName(), $this);
	}
	
	public function getName() {
		return "edit";
	}

	public function getVersion() {
		return "20130915";
	}

	public function permissionGranted() {
		$loginGroup = pw_wiki_getcfg("login", "group");
		return $loginGroup == "admin";
	}
	
	public function execute() {
		
		$id = pw_wiki_getid();
		$data = "";
		$filename = WIKISTORAGE.$id->getPath().WIKIFILEEXT;
		
		if ($id->isNS()) {
			$nsDefaultId = new WikiID($id->getFullNS().WIKINSDEFAULTPAGE);
			
			header("Location: ?id=".$nsDefaultId->getIDAsUrl()."&mode=edit");
			
// 			$nsDefaultFilename = WIKISTORAGE.$nsDefaultId->getPath().WIKIFILEEXT;
// 			if(file_exists($nsDefaultFilename)) {
// 				$this->setNotification("Loading default namespace page! ".$nsDefaultFilename);
// 				$filename = $nsDefaultFilename;
// 				$id = $nsDefaultId;
// 			} else {
// 				if(file_put_contents($nsDefaultFilename, "") === false) {
// 					$this->setNotification("Unable to create file '$nsDefaultFilename'!", Module::NOTIFICATION_ERROR);
// 				}
// 			}
		}
	
	
		if (isset($_POST["save"])) {
			$data = $_POST['wikitxt'];
			$data = pw_stripslashes($data);
			$data = pw_s2u($data);
			$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));
			$this->save($id, $data);
		} elseif (file_exists($filename)) {
			TestingTools::debug("exists");
			$data = file_get_contents($filename);
			$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));
		} else {
			$this->setNotification("Creating a new page.");
		}
	
		TestingTools::debug("DATA = ".$data);
		$data = pw_wiki_file2editor($data);
		TestingTools::debug("DATA = ".$data);
		
		$out = StringTools::htmlIndent("<a href='?id=".$id->getIDAsUrl()."'>&laquo; Back</a><hr />");
		$out .= StringTools::htmlIndent("<h1>Source code Editor</h1>ID = <tt>".$id->getIDAsHtmlEntities()."</tt>");
		$out .= StringTools::htmlIndent("<form id='texteditor' name='texteditor' method='post' accept-charset='utf-8'>", StringTools::START);
		$out .= StringTools::htmlIndent("<div><button value='save' name='save' id='save'>Save</button>"); 
		$out .= StringTools::htmlIndent("<textarea rows='25' name='wikitxt' id='wikitxt' wrap=off onkeydown='return catchTab(this,event)'>$data</textarea>");
		$out .= StringTools::htmlIndent("</form>", StringTools::END);
	
		$this->setDialog($out);
		
	}
	
	public function getMenuText() {
		return "Edit";
	}

	public function getMenuAvailability() {
		return true; 
	}
	
	private function save (WikiID $id, $data) {
	
		$filename = WIKISTORAGE.$id->getPath().WIKIFILEEXT;
		$dirname = WIKISTORAGE.$id->getFullNSPath();
		
		try {
			FileTools::createFolderIfNotExist($dirname);
			$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));
			if(file_put_contents($filename, $data) === false) {
				throw new Exception("Can not save '".$id->getIDAsHtmlEntities()."'. <br />Details: ".error_get_last()["message"]);
			}
			if(pw_wiki_getcfg("useCache")) {
				pw_wiki_create_cached_page($id);
				$this->setNotification("Changes saved. Cache updated!");
			} else {
				$this->setNotification("Changes saved.");
			}
		} catch (Exception $e) {
			$this->setNotification($e->getMessage(), Module::NOTIFICATION_ERROR);
			TestingTools::inform($e->getTraceAsString());
		}
	
	}
	
	public function getJavaScript() {
		return '<script type="text/javascript" src="../pwTools/javascript/catchkeys.js"></script> <!-- Editorkeys: catch TAB, insert Spaces -->';
	}

}

?>