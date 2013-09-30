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
	
		if ($id->isNS()) {
			//TODO allow to create a default namespace page...
			$this->setNotification("Namespaces can not be edited!");
			return;
		}
	
		$filename = WIKISTORAGE.$id->getPath().WIKIFILEEXT;
		$filenameText = pw_url2e($filename);
	
		if (isset($_POST["save"])) {
			$data = $_POST['wikitxt'];
			$data = pw_stripslashes($data);
			$data = pw_s2u($data);
			$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));
			$this->save($id, $data);
		} elseif (file_exists($filename)) {
			$data = file_get_contents($filename);
			$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));
		} else {
			$this->setNotification("Creating a new page.");
		}
	
		$data = pw_wiki_file2editor($data);
		
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
		$dirname = WIKISTORAGE.$id->getPath();
		$filenameText = pw_url2e($filename);
		
		try {
			FileTools::createFolderIfNotExist($dirname);
		} catch (Exception $e) {
			$this->setNotification($e->getMessage(), Module::NOTIFICATION_ERROR);
			return;
		}
	
		$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));
		if(file_put_contents($filename, $data) === false) {
			$this->setNotification("Can not save '$filenameText'", Module::NOTIFICATION_ERROR);
			return;
		}
	
		pw_wiki_create_cached_page($id);
		$this->setNotification("Changes saved.");
	}
	
	public function getJavaScript() {
		return '<script type="text/javascript" src="../pwTools/javascript/catchkeys.js"></script> <!-- Editorkeys: catch TAB, insert Spaces -->';
	}

}

?>