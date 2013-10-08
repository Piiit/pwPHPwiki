<?php//@TODO: frontend und backend trennen... UserInterface in separate Datei ablegen!if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/common.php';require_once INC_PATH.'pwTools/string/encoding.php';require_once INC_PATH.'pwTools/debug/TestingTools.php';require_once INC_PATH.'pwTools/file/FileTools.php';
require_once INC_PATH.'pwTools/gui/GuiTools.php';

function pw_wiki_rename (WikiID $id) {
	global $MODE;

	if (!isset($_SESSION["pw_wiki"]["login"]["user"])) {		return GuiTools::dialogInfo("Verschieben", "Sie sind nicht berechtigt eine Seite zu verschieben...", "id=$id&mode=$MODE");
	}

	$fullfilename = WIKISTORAGE.$id->getPath().WIKIFILEEXT;	$filename = $id->getPage().WIKIFILEEXT;
	$fntext = pw_url2e($id->getID());

	if (isset($_POST['rename'])) {

		$target = $_POST['target'];		$targetid = pw_wiki_s2id($target);
		$targetfn = $target;

		if ($id->isNS()) {			$targetid = utf8_rtrim($targetid, ':');			$targetid = pw_wiki_pg($targetid).':';			$targetid = pw_wiki_ns($id.":..:".$targetid);			$targetfn = pw_wiki_path($targetid, ST_SHORT);		} else {			$targetfn = pw_wiki_path($targetid, FNAME);			$targetfn = pw_wiki_path($id, ST_SHORT).$targetfn;		}
		$targettext = pw_s2e($target);

		$typetxt = $id->isNS() ? "Der Namensraum" : "Die Seite";

		if (file_exists($targetfn)) {			return GuiTools::dialogInfo("Umbenennen", $typetxt." '$fntext' existiert bereits.", "id=$id&mode=$MODE");
		}

		if (!rename($fullfilename, $targetfn)) {			#out(substr(decoct(fileperms($fullfilename)), 1));			return GuiTools::dialogInfo("Umbenennen", "Fehler beim Umbenennen <br />$fntext<br />nach<br />$targettext.", "id=$id&mode=$MODE&dialog=");
		}

		$newid = pw_wiki_path2id($targetfn);		return GuiTools::dialogInfo("Umbenennen", $typetxt." wurde nach <tt>$targettext</tt> umbenannt.", "id=$newid&mode=$MODE");
	}

	$entries	= StringTools::htmlIndent("<input type='hidden' name='mode' value='editpage' />");	#$entries .= StringTools::htmlIndent("<input type='hidden' name='id' value='$id' />");	$entries .= StringTools::htmlIndent("<input type='hidden' name='oldmode' value='$MODE' />");	$entries .= StringTools::htmlIndent("<input type='hidden' name='olddialog' value='rename' />");	$typetxt = $id->isNS() ? "Den Namensraum" : "Die Seite";	$entries .= StringTools::htmlIndent($typetxt." <tt>$fntext</tt> umbenennen...<br />");	$entries .= StringTools::htmlIndent("<input type='text' class='textinput' autocomplete='off' name='target' id='target' value='' />");
	return pw_ui_getDialogQuestion("Umbenennen", $entries, "rename", "Umbenennen", "id=$id&mode=$MODE");

}

function pw_wiki_movepage (WikiID $id) {
	global $MODE;

	if (!isset($_SESSION["pw_wiki"]["login"]["user"])) {		return GuiTools::dialogInfo("Verschieben", "Sie sind nicht berechtigt eine Seite zu verschieben...", "id=$id&mode=$MODE");
	}

// 	$id = pw_wiki_ns($id).pw_wiki_pg($id);
// 	$isns = pw_wiki_isns($id);	$fullfilename = WIKISTORAGE.$id->getPath().WIKIFILEEXT;	$filename = $id->getPage().WIKIFILEEXT;

	$fntext = pw_url2e($id);

	if (isset($_POST["move"]) || isset($_POST["overwrite"])) {

		$target = $_POST['target'];		$targetid = pw_wiki_s2id($target);		$targetfn = pw_wiki_path($targetid, ST_NOEXT);
		$targettext = pw_s2e($target);

		if (!file_exists($fullfilename)) {			return GuiTools::dialogInfo("Verschieben", "Die Datei '$fntext' existiert nicht.", "id=$id&mode=$MODE");
		}

		if (!is_dir($targetfn)) {			if (!isset($_POST['createfolder'])) {				return GuiTools::dialogInfo("Verschieben", "Das Zielverzeichnis '$targettext' existiert nicht.", "id=$id&mode=$MODE");
			}

			if(!mkdir($targetfn, 0777, true)) {				return GuiTools::dialogInfo("Verschieben", "Das Erstellen des Zielverzeichnisses '$targettext' schlug fehl.", "id=$id&mode=$MODE");
			}

		}

		if (!isset($_POST["overwrite"])) {			$targetfn = $targetfn."/".$filename;			if (file_exists($targetfn) && !$id->isNS()) {				$entries = StringTools::htmlIndent("<input type='hidden' name='target' value='$target' />");				return pw_ui_getDialogQuestion("Verschieben", $entries."Die Zieldatei '$targetid:$filename' existiert bereits.<br />Soll sie überschrieben werden?", "overwrite", "Ja", "id=$id&mode=$MODE");			}		} else {			$targetfn = pw_wiki_path($_POST['target'], ST_NOEXT)."/".$filename;			if ($fullfilename != $targetfn) {				if (!unlink($targetfn)) {					return GuiTools::dialogInfo("Verschieben", "Fehler beim Verschieben der Datei<br />$fntext<br />nach<br />$targettext.<br />Die existierende Zieldatei konnte nicht gelöscht werden.", "id=$id&mode=$MODE");				}
			}

			#return GuiTools::dialogInfo("Verschieben", "OVERWRITE: $id; $t", "id=$id&mode=$MODE");
		}

		if ($fullfilename == $targetfn) {			return GuiTools::dialogInfo("Verschieben", "Fehler beim Verschieben...<br />Die Quell- und Zieldateien sind identisch.", "id=$id&mode=$MODE");
		}

		$t = "";
		if ($id->isNS()) {
			$t = pw_wiki_path($id, DNAME);
		}

		if (!rename($fullfilename, $targetfn.$t)) {			return GuiTools::dialogInfo("Verschieben", "Fehler beim Verschieben der Datei<br />$fntext<br />nach<br />$targettext.", "id=$id&mode=$MODE&dialog=");
		}

		$newid = pw_s2url(pw_wiki_path2id($targetfn));

		return GuiTools::dialogInfo("Verschieben", "Die Datei wurde nach <tt>$targettext</tt> verschoben.", "id=$newid&mode=$MODE");

	}

	$entries = StringTools::htmlIndent("<input type='hidden' name='mode' value='editpage' />");	$entries .= StringTools::htmlIndent("<input type='hidden' name='oldmode' value='$MODE' />");	$entries .= StringTools::htmlIndent("<input type='hidden' name='olddialog' value='movepage' />");	if ($id->isNS()) {
		$entries .= StringTools::htmlIndent("Den Namensraum <tt>$fntext</tt> verschieben nach...<br />");
	} else {
		$entries .= StringTools::htmlIndent("Die Seite <tt>$fntext</tt> verschieben nach...<br />");	}
	$entries .= StringTools::htmlIndent("<!--label for='id'>Ziel:</label--> <input type='text' class='textinput' autocomplete='off' name='target' id='target' value='' />");	$entries .= StringTools::htmlIndent("<br /><input type='checkbox' id='createfolder' name='createfolder' checked='checked' /><label for='createfolder'>Verzeichnisse anlegen</label>");	#$entries .= "<div id='autoc' name='autoc' style='position: relative; display: block; top: -20px; width: 500px; border: 3px solid gray'></div>";	#$entries .= "<script type=\"text/javascript\">document.observe('dom:loaded', function() {new Ajax.Autocompleter('target', 'autoc', 'bin/getfilelist.php')});</script>";	return pw_ui_getDialogQuestion("Verschieben", $entries, "move", "Verschieben", "id=$id&mode=$MODE");}
function pw_wiki_delpage (WikiID $id, $mode) {	//@TODO: clean the code...	// --- rrmdir is not needed because pw_wiki_delnamespaces does the job!	if (!isset($_SESSION["pw_wiki"]["login"]["user"]) or $id->getFullNS() == ":tpl:") {		return GuiTools::dialogInfo("L&ouml;schen", "Sie sind nicht berechtigt diese Seite oder diesen Namensraum zu l&ouml;schen...", "id=$id&mode=$mode");	}	$filename = WIKISTORAGE.$id->getPath().WIKIFILEEXT;	TestingTools::inform($filename);	$fntext = pw_url2e($id);	if (isset($_POST["del"])) {		//@TODO: file_exists -> direxists??? redundant?		if (!file_exists($filename)) {			return GuiTools::dialogInfo("L&ouml;schen", "Die Seite '$fntext' existiert nicht.", "id=$id&mode=$mode");		}		if (is_file($filename)) {			if (!unlink($filename)) {				return GuiTools::dialogInfo("L&ouml;schen", "Die Seite '$fntext' konnte nicht gel&ouml;scht werden.", "id=$id&mode=$mode");			}			$dir = WIKISTORAGE.$id->getPath(); //pw_wiki_path($id, ST_SHORT);			$oldid = $id;
			$id = pw_wiki_delnamespaces($dir);			$outdelns = "";
			if ($id == "") {
				$id = $oldid;
			} else {
				$outdelns = "Der Namensraum '$id' ist leer. Er wird entfernt.<hr />";			}
			//@TODO: getlastvalid namespace id			if ($id->isNS()) {				//out($id);
				$id = substr($id,0,strlen($id)-1);			}			$newid = new WikiID($id->getID()."..");			return GuiTools::dialogInfo("L&ouml;schen", $outdelns."Die Seite '$fntext' wurde gel&ouml;scht.", "id=".$newid."&mode=$mode");		}		if (!is_dir($filename)) {			return GuiTools::dialogInfo("L&ouml;schen", "Der Namensraum '$fntext' existiert nicht.", "id=$id&mode=$mode");		}		try {			FileTools::removeDirectory($filename);		} catch (Exception $e) {			return GuiTools::dialogInfo("L&ouml;schen", "Der Namensraum '$fntext' konnte nicht gel&ouml;scht werden.", "id=$id&mode=$mode");		}		//@TODO: put this in a common function...// 		if ($id->isNS()) {// 			$id = substr($id,0,strlen($id)-1);// 		}		$newid = new WikiID($id->getID()."..");//pw_wiki_ns($id."..");		$dir = WIKISTORAGE.$newid->getPath();//pw_wiki_path($newid, ST_SHORT);		$oldid = $id;
		$id = pw_wiki_delnamespaces($dir);
		$outdelns = "";
		if ($id == "") {
			$id = $oldid;
		} else {
			$outdelns = "Der Namensraum '$id' ist leer. Er wird entfernt.<hr />";
		}


// 		if ($id->isNS()) {// 			$id = substr($id,0,strlen($id)-1);// 		} 		$newid = new WikiID($id->getID()."..");//pw_wiki_ns($id."..");		return GuiTools::dialogInfo("L&ouml;schen", $outdelns."Der Namensraum '$fntext' wurde gel&ouml;scht.", "id=".$newid->getID()."&mode=$mode");	}	$type = "Die Seite";	if ($id->isNS()) {		$type = "Den Namensraum";	}	return pw_ui_getDialogQuestion("L&ouml;schen", "$type '$fntext' l&ouml;schen?", "del", "L&ouml;schen", "id=$id&mode=$mode");}
function pw_wiki_delnamespaces($dir) {
	if (!isset($_SESSION["pw_wiki"]["login"]["user"]))		return false;	if ($dir == WIKISTORAGE) {		return;	}	#out ($dir);	$dir = str_replace("//", "/", $dir);	$dir = str_replace("\\\\", "\\", $dir);	#out2($dir);
	$dirnames = explode("/", $dir);	#out($dirnames);	$dirar = array();	$dn = "";	foreach ($dirnames as $dirname) {		if ($dirname != "") {			$dn .= $dirname."/";			$dirar[] = $dn;		}	}	$dirar = array_reverse($dirar);	#out($dirar);	#die();	$dirtxt = "";	foreach ($dirar as $dirname) {		if (rmdir($dirname)) {			$dirtxt = pw_wiki_path2id($dirname);			$dirtxt = pw_s2e($dirtxt);			#$dirtxt = pw_wiki_entities(pw_wiki_urldecode($dirtxt));			#$dirtxt = "Der Namensraum '$dirtxt' ist leer. Er wird entfernt.<hr />";		} else {			break;		}	}	return $dirtxt;}function pw_wiki_update_cache($forced = false) {	$storage = WIKISTORAGE;	if (!is_dir($storage)) {
		throw new Exception("Folder '$storage' does not exist!");
	}		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($storage));
	foreach($files as $filename) {		if(substr($filename, -4) == ".txt") {			$filename = str_replace("\\", "/", $filename);			try {				pw_wiki_create_cached_page(pw_wiki_path2id($filename), $forced);			} catch (Exception $e) {				echo "<pre>Exception: Skipping file '$filename': $e\n</pre>";			}		}
	}}function pw_wiki_create_cached_page(WikiID $id, $forced = false) {	$filename = WIKISTORAGE.$id->getPath().WIKIFILEEXT;	$headerID = new WikiID("tpl:header", WIKIFILEEXT, WIKISTORAGE);	$footerID = new WikiID("tpl:footer", WIKIFILEEXT, WIKISTORAGE);	$headerFilename = WIKISTORAGE.$headerID->getPath().WIKIFILEEXT;	$footerFilename = WIKISTORAGE.$footerID->getPath().WIKIFILEEXT;		if (!is_file($filename)) {		throw new Exception("File '$filename' does not exist!");
	}	if (!is_file($headerFilename)) {
		throw new Exception("File '$headerFilename' does not exist!"); 
	}	if (!is_file($footerFilename)) {
		throw new Exception("File '$footerFilename' does not exist!");
	}		// If the cached file is still up-to-date do nothing! Except forced overwriting!	$cachedFilename = "home/".$id->getPath().".html";	if(!$forced && is_file($cachedFilename)) {		$cachedMTime = filemtime($cachedFilename);		if($cachedMTime >= filemtime($filename) && $cachedMTime >= filemtime($headerFilename) && $cachedMTime >= filemtime($footerFilename)) {			$data = file_get_contents($cachedFilename);			if ($data === false) {				throw new Exception("Unable to read data file '$cachedFilename'!");			}			TestingTools::inform($cachedFilename);			return $data;		}	}		$data = file_get_contents($filename);	if ($data === false) {
		throw new Exception("Unable to read data file '$filename'!");
	}
	$headerData = file_get_contents($headerFilename);
	if ($headerData === false) {
		throw new Exception("Unable to read template file '$headerFilename'!");
	}
	$footerData = file_get_contents($footerFilename);
	if ($footerFilename === false) {
		throw new Exception("Unable to read template file '$footerFilename'!");
	}		$data = $headerData."\n".$data."\n".$footerData;	$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));
	
	if (!utf8_check($data)) {
		throw new Exception("File '$filename' is not an UTF8-encoded file!");
	}
	
	$out = parse($data);		FileTools::createFolderIfNotExist(dirname($cachedFilename));
	if (file_put_contents($cachedFilename, $out) === false) {		throw new Exception("Unable to write file '$cachedFilename'!");	}		return $out;}function pw_wiki_get_parsed_file(WikiID $id) {		$filename = WIKISTORAGE.$id->getPath().WIKIFILEEXT;	$headerID = new WikiID("tpl:header");	$footerID = new WikiID("tpl:footer");
	$headerFilename = WIKISTORAGE."/".$headerID->getPath().WIKIFILEEXT;;
	$footerFilename = WIKISTORAGE."/".$footerID->getPath().WIKIFILEEXT;;
	
	if (!is_file($filename)) {
		throw new Exception("File '$filename' does not exist!");
	}
	if (!is_file($headerFilename)) {
		throw new Exception("File '$headerFilename' does not exist!");
	}
	if (!is_file($footerFilename)) {
		throw new Exception("File '$footerFilename' does not exist!");
	}		$data = file_get_contents($filename);
	if ($data === false) {
		throw new Exception("Unable to read data file '$filename'!");
	}
	$headerData = file_get_contents($headerFilename);
	if ($headerData === false) {
		throw new Exception("Unable to read template file '$headerFilename'!");
	}
	$footerData = file_get_contents($footerFilename);
	if ($footerFilename === false) {
		throw new Exception("Unable to read template file '$footerFilename'!");
	}		$data = $headerData."\n".$data."\n".$footerData;	$_SESSION['pw_wiki']['file']['format'] = FileTools::getTextFileFormat($data)->getString();
	$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));		$out = parse($data);	return $out;}
function pw_wiki_showcontent(WikiID $id) {	if(!isset($_SESSION['pw_wiki']['useCache']) || $_SESSION['pw_wiki']['useCache'] == false) {		return pw_wiki_get_parsed_file($id);	}	return pw_wiki_create_cached_page($id);
}
function pw_wiki_getfilelist(WikiID $id) {
	$ns = $id->getNS();
	$path = $id->getFullNSPath();

	$strout = "";	$files = array();	$dirs = array();	if($ns) {		$dirs[] = array('NAME' => '..', 'TYPE' => 'DIR');
	}

	$p = "../".rtrim($path, "/")."/";
	$data = glob ($p."*");

	#var_dump($data);

	if (!$data) {		return null;
	}

	foreach ($data as $k => $i) {		$i = pw_s2u($i);		$i = utf8_strtolower($i);
		$i = pw_u2t($i);

		if (is_dir($i)) {			$dirs[] = array('NAME' => FileTools::basename($i), 'TYPE' => "DIR");		} else {			$files[] = array('NAME' => FileTools::basename($i, ".txt"), 'TYPE' => "TEXT", 'SIZE' => filesize($i));
		}

	}

	if ($dirs) sort($dirs);
	if ($files) sort($files);

	$out = array_merge($dirs, $files);

	return $out;

}


?>