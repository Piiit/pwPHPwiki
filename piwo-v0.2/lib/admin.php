<?php//@TODO: frontend und backend trennen... UserInterface in separate Datei ablegen!if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/common.php';require_once INC_PATH.'pwTools/string/encoding.php';require_once INC_PATH.'pwTools/debug/TestingTools.php';require_once INC_PATH.'pwTools/file/FileTools.php';function pw_wiki_showsource (WikiID $id) {	$filename = pw_wiki_getcfg('storage').$id->getPath().pw_wiki_getcfg('fileext');	$out = StringTools::htmlIndent("<h1>Quelltext von <tt>".$id->getID()."</tt></h1>");	$out .= StringTools::htmlIndent("<i>Sie sind nicht berechtigt diesen Quelltext zu bearbeiten.</i><br />");	if (file_exists($filename) and !isset($_POST['save'])) {		$data = file_get_contents($filename);		$data = pw_wiki_file2editor($data);		$out .= StringTools::htmlIndent("<br /><a href='?mode=cleared&id=".$id->getID()."'>&laquo; Zur&uuml;ck zur Seite</a>");		$out .= StringTools::htmlIndent("<a style='float: right' href='?mode=showpages&id=".$id->getID()."'>Zum Seiten&uuml;berblick &raquo;</a><hr />");		$out .= StringTools::htmlIndent("<textarea cols='80' rows='25' id='wikitxt' readonly='readonly' wrap='off'>$data</textarea>");		$out .= StringTools::htmlIndent("<hr /><a href='?mode=cleared&id=".$id->getID()."'>&laquo; Zur&uuml;ck zur Seite</a>");		$out .= StringTools::htmlIndent("<a style='float: right' href='?mode=showpages&id=".$id->getID()."'>Zum Seiten&uuml;berblick &raquo;</a>");		return $out;	}		throw new Exception("File not found!");}
function pw_wiki_editpage (WikiID $id) {
	#out(pw_wiki_isvalidid(pw_wiki_pg($id)));
	$data = "";	$ret = "";	if (!isset($_SESSION["pw_wiki"]["login"]["user"]))		return false;
	if ($id->isNS())
		return;
	$filename = pw_wiki_getcfg('storage').$id->getPath().pw_wiki_getcfg('fileext');	$filenameText = pw_url2e($filename);	#out2(utf8_check($filename));	if (file_exists($filename) && !isset($_POST['save'])) {		$data = file_get_contents($filename);		$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));		$ret = "<tt>Datei '$filenameText' wurde geladen</tt>";	}	if (isset($_POST["save"])) {		$data = $_POST['wikitxt'];		$data = pw_stripslashes($data);		$data = pw_s2u($data);		$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));		// @TODO: What is config?		$config = null;		$ret = pw_wiki_savepage ($id, $data, $config);	}	$data = pw_wiki_file2editor($data);	$OLDMODE = isset($_REQUEST['oldmode']) ? $_REQUEST['oldmode'] : "cleared";	$out = StringTools::htmlIndent();	$out .= StringTools::htmlIndent("<!-- EDITOR START -->", StringTools::START);	$out .= StringTools::htmlIndent("<form id='texteditor' name='texteditor' method='post' accept-charset='utf-8'>", StringTools::START);	$out .= StringTools::htmlIndent("<div id='editor_win' style='width: 100%; border: 0;'>", StringTools::START);	$out .= StringTools::htmlIndent("<button	value='save' name='save' id='save'>Speichern</button><a id='exiteditor' class='textinput' href='?id=".$id->getID()."&mode=$OLDMODE'>Abbrechen</a>");	$out .= StringTools::htmlIndent("<span style='float: right'>$ret</span>");	$out .= StringTools::htmlIndent("<label style='display: block; border: 0; padding: 0; margin: 0'>", StringTools::START);	$out .= StringTools::htmlIndent("<textarea cols='80' rows='25' name='wikitxt' id='wikitxt' wrap=off onkeydown='return catchTab(this,event)'>$data</textarea>");	$out .= StringTools::htmlIndent("</label>", StringTools::END);	$out .= StringTools::htmlIndent("</div>", StringTools::END);	$out .= StringTools::htmlIndent("</form>", StringTools::END);	$out .= StringTools::htmlIndent("<!-- EDITOR END -->", StringTools::END);	$out .= StringTools::htmlIndent();	return $out;}
function pw_wiki_newpage (WikiID $id, $mode) {	if (!isset($_SESSION["pw_wiki"]["login"]["user"]))
		return false;

	$idurl = $id->getID();
	$idText = pw_s2e($id->getID());

	$entries = StringTools::htmlIndent("<input type='hidden' name='mode' value='editpage' />");	$entries .= StringTools::htmlIndent("<input type='hidden' name='oldmode' value='$mode' />");	$entries .= StringTools::htmlIndent("<input type='hidden' name='olddialog' value='newpage' />");	$entries .= StringTools::htmlIndent("<label for='id'>ID:</label> <input type='text' class='textinput' name='id' id='id' value='$idText' />");	$entries .= StringTools::htmlIndent("<br /><hr /><tt><small>Namensr&auml;ume werden mit : voneinander getrennt!<br />Bsp.: Handbuch:Seite1<br />Falls die Seite schon existiert, wird sie zum Bearbeiten ge&ouml;ffnet.</small></tt>");
	return pw_ui_getDialogQuestion("Neue Seite erstellen", $entries, "create", "OK", "id=$idurl&mode=$mode", "get");

}

function pw_wiki_config (WikiID $id) {	if (!isset($_SESSION["pw_wiki"]["login"]["user"]))
		return false;

	global $MODE;

	if (isset($_POST["config"])) {		if (isset($_POST['debug']) && $_POST['debug']) {			$_SESSION['pw_wiki']['debug'] = true;		} else {			$_SESSION['pw_wiki']['debug'] = false;			TestingTools::debugOff();		}				if (isset($_POST['useCache']) && $_POST['useCache']) {
			$_SESSION['pw_wiki']['useCache'] = true;
		} else {
			$_SESSION['pw_wiki']['useCache'] = false;
		}		return;
	}
	$debug_ch = "";
	if (pw_wiki_getcfg('debug')) {		$debug_ch = " checked='checked' ";
	}		$cache_ch = "";
	if (pw_wiki_getcfg('useCache')) {
		$cache_ch = " checked='checked' ";
	}

	$entries = StringTools::htmlIndent("<input type='hidden' name='oldmode' value='$MODE' />");	$entries .= StringTools::htmlIndent("<label for='debug'>Debug-Modus:</label> <input type='checkbox' name='debug' id='debug'$debug_ch />");	$entries .= StringTools::htmlIndent("<br />");	$entries .= StringTools::htmlIndent("<label for='useCache'>Use cache:</label> <input type='checkbox' name='useCache' id='useCache'$cache_ch />");
	return pw_ui_getDialogQuestion("Einstellungen", $entries, "config", "OK", "id=$id&mode=$MODE");

}

function pw_wiki_savepage (WikiID $id, $data) {

	// Kontrolliere die Berechtigungen	if (!isset($_SESSION["pw_wiki"]["login"]["user"]))
		return false;

	$filename = pw_wiki_getcfg('storage').$id->getPath().pw_wiki_getcfg('fileext');	$dirname = pw_wiki_getcfg('storage').$id->getPath(); //pw_wiki_path($id, ST_SHORT);
	$filenameText = pw_url2e($filename);
	
	// Kontrolliere, ob der Ordner existiert und lege ihn ggf. an	$dirnames = explode("/", $dirname);	$dn = "";	foreach ($dirnames as $dirname) {		$dn .= $dirname."/";		if (!file_exists($dn)) {			if (!mkdir($dn)) {				$ret = "<tt class='error'>Der Ordner '$dn' konnte nicht angelegt werden.</tt>";				return $ret;			}		}
	}

	$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));
	$ret = file_put_contents($filename, $data);

	if ($ret !== false) {		$ret = "<tt>Datei '$filenameText' wurde gespeichert.</tt>";	} else {		$ret = "<tt class='error'>Datei '$filenameText' konnte nicht gespeichert werden.</tt>";
	}		pw_wiki_create_cached_page($id);

	return $ret;
}

function pw_wiki_rename (WikiID $id) {
	global $MODE;

	if (!isset($_SESSION["pw_wiki"]["login"]["user"])) {		return pw_ui_getDialogInfo("Verschieben", "Sie sind nicht berechtigt eine Seite zu verschieben...", "id=$id&mode=$MODE");
	}

	$fullfilename = pw_wiki_getcfg('storage').$id->getPath().pw_wiki_getcfg('fileext');	$filename = $id->getPage().pw_wiki_getcfg('fileext');
	$fntext = pw_url2e($id->getID());

	if (isset($_POST['rename'])) {

		$target = $_POST['target'];		$targetid = pw_wiki_s2id($target);
		$targetfn = $target;

		if ($id->isNS()) {			$targetid = utf8_rtrim($targetid, ':');			$targetid = pw_wiki_pg($targetid).':';			$targetid = pw_wiki_ns($id.":..:".$targetid);			$targetfn = pw_wiki_path($targetid, ST_SHORT);		} else {			$targetfn = pw_wiki_path($targetid, FNAME);			$targetfn = pw_wiki_path($id, ST_SHORT).$targetfn;		}
		$targettext = pw_s2e($target);

		$typetxt = $id->isNS() ? "Der Namensraum" : "Die Seite";

		if (file_exists($targetfn)) {			return pw_ui_getDialogInfo("Umbenennen", $typetxt." '$fntext' existiert bereits.", "id=$id&mode=$MODE");
		}

		if (!rename($fullfilename, $targetfn)) {			#out(substr(decoct(fileperms($fullfilename)), 1));			return pw_ui_getDialogInfo("Umbenennen", "Fehler beim Umbenennen <br />$fntext<br />nach<br />$targettext.", "id=$id&mode=$MODE&dialog=");
		}

		$newid = pw_wiki_path2id($targetfn);		return pw_ui_getDialogInfo("Umbenennen", $typetxt." wurde nach <tt>$targettext</tt> umbenannt.", "id=$newid&mode=$MODE");
	}

	$entries	= StringTools::htmlIndent("<input type='hidden' name='mode' value='editpage' />");	#$entries .= StringTools::htmlIndent("<input type='hidden' name='id' value='$id' />");	$entries .= StringTools::htmlIndent("<input type='hidden' name='oldmode' value='$MODE' />");	$entries .= StringTools::htmlIndent("<input type='hidden' name='olddialog' value='rename' />");	$typetxt = $id->isNS() ? "Den Namensraum" : "Die Seite";	$entries .= StringTools::htmlIndent($typetxt." <tt>$fntext</tt> umbenennen...<br />");	$entries .= StringTools::htmlIndent("<input type='text' class='textinput' autocomplete='off' name='target' id='target' value='' />");
	return pw_ui_getDialogQuestion("Umbenennen", $entries, "rename", "Umbenennen", "id=$id&mode=$MODE");

}

function pw_wiki_movepage (WikiID $id) {
	global $MODE;

	if (!isset($_SESSION["pw_wiki"]["login"]["user"])) {		return pw_ui_getDialogInfo("Verschieben", "Sie sind nicht berechtigt eine Seite zu verschieben...", "id=$id&mode=$MODE");
	}

// 	$id = pw_wiki_ns($id).pw_wiki_pg($id);
// 	$isns = pw_wiki_isns($id);	$fullfilename = pw_wiki_getcfg('storage').$id->getPath().pw_wiki_getcfg('fileext');	$filename = $id->getPage().pw_wiki_getcfg('fileext');

	$fntext = pw_url2e($id);

	if (isset($_POST["move"]) || isset($_POST["overwrite"])) {

		$target = $_POST['target'];		$targetid = pw_wiki_s2id($target);		$targetfn = pw_wiki_path($targetid, ST_NOEXT);
		$targettext = pw_s2e($target);

		if (!file_exists($fullfilename)) {			return pw_ui_getDialogInfo("Verschieben", "Die Datei '$fntext' existiert nicht.", "id=$id&mode=$MODE");
		}

		if (!is_dir($targetfn)) {			if (!isset($_POST['createfolder'])) {				return pw_ui_getDialogInfo("Verschieben", "Das Zielverzeichnis '$targettext' existiert nicht.", "id=$id&mode=$MODE");
			}

			if(!mkdir($targetfn, 0777, true)) {				return pw_ui_getDialogInfo("Verschieben", "Das Erstellen des Zielverzeichnisses '$targettext' schlug fehl.", "id=$id&mode=$MODE");
			}

		}

		if (!isset($_POST["overwrite"])) {			$targetfn = $targetfn."/".$filename;			if (file_exists($targetfn) && !$id->isNS()) {				$entries = StringTools::htmlIndent("<input type='hidden' name='target' value='$target' />");				return pw_ui_getDialogQuestion("Verschieben", $entries."Die Zieldatei '$targetid:$filename' existiert bereits.<br />Soll sie überschrieben werden?", "overwrite", "Ja", "id=$id&mode=$MODE");			}		} else {			$targetfn = pw_wiki_path($_POST['target'], ST_NOEXT)."/".$filename;			if ($fullfilename != $targetfn) {				if (!unlink($targetfn)) {					return pw_ui_getDialogInfo("Verschieben", "Fehler beim Verschieben der Datei<br />$fntext<br />nach<br />$targettext.<br />Die existierende Zieldatei konnte nicht gelöscht werden.", "id=$id&mode=$MODE");				}
			}

			#return pw_ui_getDialogInfo("Verschieben", "OVERWRITE: $id; $t", "id=$id&mode=$MODE");
		}

		if ($fullfilename == $targetfn) {			return pw_ui_getDialogInfo("Verschieben", "Fehler beim Verschieben...<br />Die Quell- und Zieldateien sind identisch.", "id=$id&mode=$MODE");
		}

		$t = "";
		if ($id->isNS()) {
			$t = pw_wiki_path($id, DNAME);
		}

		if (!rename($fullfilename, $targetfn.$t)) {			return pw_ui_getDialogInfo("Verschieben", "Fehler beim Verschieben der Datei<br />$fntext<br />nach<br />$targettext.", "id=$id&mode=$MODE&dialog=");
		}

		$newid = pw_s2url(pw_wiki_path2id($targetfn));

		return pw_ui_getDialogInfo("Verschieben", "Die Datei wurde nach <tt>$targettext</tt> verschoben.", "id=$newid&mode=$MODE");

	}

	$entries	= StringTools::htmlIndent("<input type='hidden' name='mode' value='editpage' />");	$entries .= StringTools::htmlIndent("<input type='hidden' name='oldmode' value='$MODE' />");	$entries .= StringTools::htmlIndent("<input type='hidden' name='olddialog' value='movepage' />");	if ($id->isNS()) {
		$entries .= StringTools::htmlIndent("Den Namensraum <tt>$fntext</tt> verschieben nach...<br />");
	} else {
		$entries .= StringTools::htmlIndent("Die Seite <tt>$fntext</tt> verschieben nach...<br />");	}
	$entries .= StringTools::htmlIndent("<!--label for='id'>Ziel:</label--> <input type='text' class='textinput' autocomplete='off' name='target' id='target' value='' />");	$entries .= StringTools::htmlIndent("<br /><input type='checkbox' id='createfolder' name='createfolder' checked='checked' /><label for='createfolder'>Verzeichnisse anlegen</label>");	#$entries .= "<div id='autoc' name='autoc' style='position: relative; display: block; top: -20px; width: 500px; border: 3px solid gray'></div>";	#$entries .= "<script type=\"text/javascript\">document.observe('dom:loaded', function() {new Ajax.Autocompleter('target', 'autoc', 'bin/getfilelist.php')});</script>";	return pw_ui_getDialogQuestion("Verschieben", $entries, "move", "Verschieben", "id=$id&mode=$MODE");}
function pw_wiki_delpage (WikiID $id, $mode) {	//@TODO: clean the code...	// --- rrmdir is not needed because pw_wiki_delnamespaces does the job!	if (!isset($_SESSION["pw_wiki"]["login"]["user"]) or $id->getFullNS() == ":tpl:") {		return pw_ui_getDialogInfo("L&ouml;schen", "Sie sind nicht berechtigt diese Seite oder diesen Namensraum zu l&ouml;schen...", "id=$id&mode=$mode");	}	$filename = pw_wiki_getcfg('storage').$id->getPath().pw_wiki_getcfg('fileext');	TestingTools::inform($filename);	$fntext = pw_url2e($id);	if (isset($_POST["del"])) {		//@TODO: file_exists -> direxists??? redundant?		if (!file_exists($filename)) {			return pw_ui_getDialogInfo("L&ouml;schen", "Die Seite '$fntext' existiert nicht.", "id=$id&mode=$mode");		}		if (is_file($filename)) {			if (!unlink($filename)) {				return pw_ui_getDialogInfo("L&ouml;schen", "Die Seite '$fntext' konnte nicht gel&ouml;scht werden.", "id=$id&mode=$mode");			}			$dir = pw_wiki_getcfg('storage').$id->getPath(); //pw_wiki_path($id, ST_SHORT);			$oldid = $id;
			$id = pw_wiki_delnamespaces($dir);			$outdelns = "";
			if ($id == "") {
				$id = $oldid;
			} else {
				$outdelns = "Der Namensraum '$id' ist leer. Er wird entfernt.<hr />";			}
			//@TODO: getlastvalid namespace id			if ($id->isNS()) {				//out($id);
				$id = substr($id,0,strlen($id)-1);			}			$newid = new WikiID($id->getID()."..");			return pw_ui_getDialogInfo("L&ouml;schen", $outdelns."Die Seite '$fntext' wurde gel&ouml;scht.", "id=".$newid."&mode=$mode");		}		if (!is_dir($filename)) {			return pw_ui_getDialogInfo("L&ouml;schen", "Der Namensraum '$fntext' existiert nicht.", "id=$id&mode=$mode");		}		try {			FileTools::removeDirectory($filename);		} catch (Exception $e) {			return pw_ui_getDialogInfo("L&ouml;schen", "Der Namensraum '$fntext' konnte nicht gel&ouml;scht werden.", "id=$id&mode=$mode");		}		//@TODO: put this in a common function...// 		if ($id->isNS()) {// 			$id = substr($id,0,strlen($id)-1);// 		}		$newid = new WikiID($id->getID()."..");//pw_wiki_ns($id."..");		$dir = pw_wiki_getcfg('storage').$newid->getPath();//pw_wiki_path($newid, ST_SHORT);		$oldid = $id;
		$id = pw_wiki_delnamespaces($dir);
		$outdelns = "";
		if ($id == "") {
			$id = $oldid;
		} else {
			$outdelns = "Der Namensraum '$id' ist leer. Er wird entfernt.<hr />";
		}


// 		if ($id->isNS()) {// 			$id = substr($id,0,strlen($id)-1);// 		} 		$newid = new WikiID($id->getID()."..");//pw_wiki_ns($id."..");		return pw_ui_getDialogInfo("L&ouml;schen", $outdelns."Der Namensraum '$fntext' wurde gel&ouml;scht.", "id=".$newid->getID()."&mode=$mode");	}	$type = "Die Seite";	if ($id->isNS()) {		$type = "Den Namensraum";	}	return pw_ui_getDialogQuestion("L&ouml;schen", "$type '$fntext' l&ouml;schen?", "del", "L&ouml;schen", "id=$id&mode=$mode");}
function pw_wiki_delnamespaces($dir) {
	if (!isset($_SESSION["pw_wiki"]["login"]["user"]))		return false;	if ($dir == pw_wiki_getcfg('storage')) {		return;	}	#out ($dir);	$dir = str_replace("//", "/", $dir);	$dir = str_replace("\\\\", "\\", $dir);	#out2($dir);
	$dirnames = explode("/", $dir);	#out($dirnames);	$dirar = array();	$dn = "";	foreach ($dirnames as $dirname) {		if ($dirname != "") {			$dn .= $dirname."/";			$dirar[] = $dn;		}	}	$dirar = array_reverse($dirar);	#out($dirar);	#die();	$dirtxt = "";	foreach ($dirar as $dirname) {		if (rmdir($dirname)) {			$dirtxt = pw_wiki_path2id($dirname);			$dirtxt = pw_s2e($dirtxt);			#$dirtxt = pw_wiki_entities(pw_wiki_urldecode($dirtxt));			#$dirtxt = "Der Namensraum '$dirtxt' ist leer. Er wird entfernt.<hr />";		} else {			break;		}	}	return $dirtxt;}function pw_wiki_update_cache($forced = false) {	$storage = pw_wiki_getcfg('storage');	if (!is_dir($storage)) {
		throw new Exception("Folder '$storage' does not exist!");
	}		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($storage));
	foreach($files as $filename) {		if(substr($filename, -4) == ".txt") {			$filename = str_replace("\\", "/", $filename);			try {				pw_wiki_create_cached_page(pw_wiki_path2id($filename), $forced);			} catch (Exception $e) {				echo "<pre>Exception: Skipping file '$filename': $e\n</pre>";			}		}
	}}function pw_wiki_create_cached_page(WikiID $id, $forced = false) {	$filename = pw_wiki_getcfg('storage').$id->getPath().pw_wiki_getcfg('fileext');	$headerID = new WikiID("tpl:header", pw_wiki_getcfg('fileext'), pw_wiki_getcfg('storage'));	$footerID = new WikiID("tpl:footer", pw_wiki_getcfg('fileext'), pw_wiki_getcfg('storage'));	$headerFilename = pw_wiki_getcfg('storage').$headerID->getPath().pw_wiki_getcfg('fileext');	$footerFilename = pw_wiki_getcfg('storage').$footerID->getPath().pw_wiki_getcfg('fileext');		if (!is_file($filename)) {		throw new Exception("File '$filename' does not exist!");
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
	if (file_put_contents($cachedFilename, $out) === false) {		throw new Exception("Unable to write file '$cachedFilename'!");	}		return $out;}function pw_wiki_get_parsed_file(WikiID $id) {		$filename = pw_wiki_getcfg('storage').$id->getPath().pw_wiki_getcfg('fileext');	$headerID = new WikiID("tpl:header");	$footerID = new WikiID("tpl:footer");
	$headerFilename = pw_wiki_getcfg('storage')."/".$headerID->getPath().pw_wiki_getcfg('fileext');;
	$footerFilename = pw_wiki_getcfg('storage')."/".$footerID->getPath().pw_wiki_getcfg('fileext');;
	
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
function pw_wiki_showcontent(WikiID $id) {	if(isset($_SESSION['pw_wiki']['useCache']) && $_SESSION['pw_wiki']['useCache'] == false) {		return pw_wiki_get_parsed_file($id);	}		return pw_wiki_create_cached_page($id);
}


$user = "root";
$pwd = "qwertz";

function pw_wiki_login(WikiID $id) {	global $user;	global $pwd;
	$mode = pw_wiki_getmode();

	if (isset($_POST["login"])) {		$login = $_POST["username"];
		$pass = $_POST["password"];

		if ($user == $login && $pass == $pwd) {			$_SESSION["pw_wiki"]['login']["user"] = $login;			return pw_ui_getDialogInfo("Login", "Benutzerlogin erfolgreich...", "id=".$id->getID()."&mode=$mode");		} else {			unset($_SESSION["pw_wiki"]['login']["user"]);			return pw_ui_getDialogInfo("Login", "Benutzerlogin fehlgeschlagen...", "id=".$id->getID()."&mode=$mode");		}
	}

	if (isset($_POST["logout"])) {		unset($_SESSION["pw_wiki"]['login']["user"]);		session_destroy();		return pw_ui_getDialogInfo("Logout", "Sie sind nun abgemeldet...", "id=".$id->getID()."&mode=$mode");
	}

	if (isset($_SESSION["pw_wiki"]['login']["user"])) {		return pw_ui_getDialogQuestion("Logout", "Wollen Sie sich abmelden?", "logout", "Ja", "id=".$id->getID()."&mode=$mode");
	}

	$entries = "<label for='username'>Benutzer: </label><input type='text' class='textinput' name='username' /><br />";	$entries .= "<label for='password'>Passwort: </label><input type='password' class='textinput' name='password' />";	return pw_ui_getDialogQuestion("Login", $entries, "login", "OK", "id=".$id->getID()."&mode=$mode");
}function pw_wiki_filenotfound(WikiID $id) {	//TODO get back to the previous mode and id	return pw_ui_getDialogInfo("Nicht gefunden", "Seite mit ID '".$id->getID()."' nicht gefunden.", "id=".pw_wiki_getcfg('startpage')."&mode=cleared");}

function pw_wiki_getfilelist(WikiID $id) {	#var_dump($id);
	#var_dump(pw_wiki_getcfg());

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

		if (is_dir($i)) {			$dirs[] = array('NAME' => pw_basename($i), 'TYPE' => "DIR");		} else {			$files[] = array('NAME' => pw_basename($i, ".txt"), 'TYPE' => "TEXT", 'SIZE' => filesize($i));
		}

	}

	if ($dirs) sort($dirs);
	if ($files) sort($files);

	$out = array_merge($dirs, $files);

	return $out;

}

function pw_wiki_showpages(WikiID $id) {	$path = pw_wiki_getcfg('storage').$id->getFullNSPath();
// 	TestingTools::inform($id);
	
	$strout = "";	$files = array();	$dirs = array();	if(!$id->isRootNS()) {		$dirs[] = array('NAME' => '..', 'TYPE' => 'DIR');	}
	$data = glob ("$path/*");

	// Leeres Verz. gefunden... Löschen!	if (!$data) {		if ($path != pw_wiki_getcfg('storage') && rmdir($path)) {			$strout .= "<tt>INFO: $path ist leer und wird entfernt!</tt>";		}	} else {		foreach ($data as $k => $i) {
			$i = pw_s2u($i);			if ($i != utf8_strtolower($i)) {				rename(pw_u2t($i), pw_u2t(utf8_strtolower($i)));				// TODO: falls neue Datei bereits existiert ??? Fehler melden... Benutzereingabe fordern!			}
			$i = utf8_strtolower($i);
			$i = pw_u2t($i);

			if (is_dir($i)) {				$dirs[] = array('NAME' => pw_basename($i), 'TYPE' => "DIR");			} else {				$files[] = array('NAME' => pw_basename($i, ".txt"), 'TYPE' => "TEXT", 'SIZE' => filesize($i));
			}

		}
	}

	if ($dirs) sort($dirs);
	if ($files) sort($files);

	$out = array_merge($dirs, $files);	//	 TestingTools::inform($out);		$strout .= "<h1>Seiten&uuml;berblick</h1>";	$strout .= "Sie sind hier: ".pw_wiki_trace($id->getFullNS())."";	$strout .= "<table id='overview'><tr><th style='width:15px'>#</th><th style='width: 380px'>ID (Vorschau)</th><th style='width: 70px'>Gr&ouml;&szlig;e</th><th style='width: 60px'>Typ</th><th>Optionen</th></tr>";	$nr = 0;	foreach ($out as $k => $i) {

		$strout .= "<tr style='background: black'>";		$strout .= "<td style='text-align: right'>".($nr++)."</td>";		if ($i['TYPE'] == "TEXT") {			$strout .= "<td>";			$strout .= pw_url2e($i['NAME']);			$strout .= "<a style='float: right' href='?id=".pw_s2url($id->getFullNS().$i['NAME'])."&mode=cleared'>&laquo; anzeigen</a>";			$strout .= "</td>";		} else {			 $strout .= "<td><a href='?id=".pw_s2url(WikiID::cleanNamespaceString($id->getFullNS().$i['NAME'].':'))."&mode=showpages'>".pw_url2e($i['NAME'])."</a></td>";		}		$strout .= "<td style='text-align: right'>";		if ($i['TYPE'] == "TEXT") {			$strout .= "<tt>".pw_formatbytes($i['SIZE'], 2, false)."</tt>";		} else {			$strout .= "<tt>-</tt>";		}		$strout .= "<td>".$i['TYPE']."</td>";
		$strout .= "<td>";

		if ($i['TYPE'] == "TEXT") {			if (isset($_SESSION["pw_wiki"]["login"]["user"])) {				$strout .= "<a href='?id=".pw_s2url($id->getFullNS().$i['NAME'])."&mode=editpage&oldmode=showpages'>Bearbeiten</a> | ";				$strout .= "<a href='?id=".pw_s2url($id->getFullNS().$i['NAME'])."&mode=showpages&dialog=delpage'>L&ouml;schen</a> | ";				$strout .= "<a href='?id=".pw_s2url($id->getFullNS().$i['NAME'])."&mode=showpages&dialog=rename'>Umbenennen</a> | ";				$strout .= "<a href='?id=".pw_s2url($id->getFullNS().$i['NAME'])."&mode=showpages&dialog=movepage'>Verschieben</a>";				#$strout .= "[<a href='?id=".$ns.$i['NAME']."&mode=showpages&dialog=info'>Info</a>]";			} else {				$strout .= "<a href='?id=".pw_s2url($id->getFullNS().$i['NAME'])."&mode=showsource&oldmode=showpages'>Quelltext anzeigen</a>";			}		} else {			if ($i['NAME'] != '..') {				if (isset($_SESSION["pw_wiki"]["login"]["user"])) {					$strout .= "<a href='?id=".pw_s2url($id->getFullNS().$i['NAME'].":")."&mode=showpages&dialog=delpage'>L&ouml;schen</a> | ";					$strout .= "<a href='?id=".pw_s2url($id->getFullNS().$i['NAME'].":")."&mode=showpages&dialog=rename'>Umbenennen</a> | ";					$strout .= "<a href='?id=".pw_s2url($id->getFullNS().$i['NAME'].":")."&mode=showpages&dialog=movepage'>Verschieben</a>";				}			}		}		$strout .= "</td>";		$strout .= "</tr>";
	}

	$strout .= "</table>";	return $strout;
}



?>