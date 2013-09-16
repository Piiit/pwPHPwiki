<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/JavaScriptProvider.php';


class EditModule implements ModuleHandler, JavaScriptProvider {
	
	public function getName() {
		return "edit";
	}

	public function getVersion() {
		return "20130915";
	}

	public function permissionGranted($userData) {
		return $userData['group'] == 'admin';
	}
	
	public function getDialog() {
		$id = pw_wiki_getid();
	
		$data = "";
		$ret = "";
	
		if (!isset($_SESSION["pw_wiki"]["login"]["user"]))
			return false;
	
		if ($id->isNS())
			return;
	
		$filename = pw_wiki_getcfg('storage').$id->getPath().pw_wiki_getcfg('fileext');
		$filenameText = pw_url2e($filename);
	
		if (isset($_POST["save"])) {
			$data = $_POST['wikitxt'];
			$data = pw_stripslashes($data);
			$data = pw_s2u($data);
			$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));
			$ret = self::save($id, $data);
		} elseif (file_exists($filename)) {
			$data = file_get_contents($filename);
			$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));
		} else {
			$ret = "<tt>Creating a new page.</tt>";
		}
	
		$data = pw_wiki_file2editor($data);
		
		$out = StringTools::htmlIndent("<a href='?id=".$id->getID()."'>&laquo; Back</a> | <a href='?mode=showpages&id=".$id->getFullNS()."'>Show Pages</a>");
		$out .= StringTools::htmlIndent("<h1>Source code Editor</h1>ID = <tt>".$id->getID()."</tt>");
		$out .= StringTools::htmlIndent("<form id='texteditor' name='texteditor' method='post' accept-charset='utf-8'>", StringTools::START);
		$out .= StringTools::htmlIndent("<div id='editor_win' style='width: 100%; border: 0;'>", StringTools::START);
		$out .= StringTools::htmlIndent("<button value='save' name='save' id='save'>Save</button>");
		$out .= StringTools::htmlIndent("<span style='float: right'>$ret</span>");
		$out .= StringTools::htmlIndent("<label style='display: block; border: 0; padding: 0; margin: 0'>", StringTools::START);
		$out .= StringTools::htmlIndent("<textarea cols='80' rows='25' name='wikitxt' id='wikitxt' wrap=off onkeydown='return catchTab(this,event)'>$data</textarea>");
		$out .= StringTools::htmlIndent("</label>", StringTools::END);
		$out .= StringTools::htmlIndent("</div>", StringTools::END);
		$out .= StringTools::htmlIndent("</form>", StringTools::END);
	
		return $out;
		
	}
	
	public function getMenuText() {
		return "Edit";
	}

	public function getMenuAvailability($mode) {
		return true; //For all modes available
	}
	
	private static function save (WikiID $id, $data) {
	
		$filename = pw_wiki_getcfg('storage').$id->getPath().pw_wiki_getcfg('fileext');
		$dirname = pw_wiki_getcfg('storage').$id->getPath();
		$filenameText = pw_url2e($filename);
		
		try {
			FileTools::createFolderIfNotExist($dirname);
		} catch (Exception $e) {
			return "<tt class='error'>".$e->getMessage()."</tt>";
		}
	
		$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));
		if(file_put_contents($filename, $data) === false) {
			return "<tt class='error'>'ERROR: Can not save '$filenameText'</tt>";
		}
	
		pw_wiki_create_cached_page($id);
	
		return "<tt>Changes saved.</tt>";
	}
	
	public function getJavaScript() {
		return '<script type="text/javascript" src="../pwTools/javascript/catchkeys.js"></script> <!-- Editorkeys: catch TAB, insert Spaces -->';
	}

}

?>