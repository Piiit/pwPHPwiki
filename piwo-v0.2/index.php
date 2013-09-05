<?php//FIXME: BAD CODE SMELL... whole file!error_reporting(E_ALL);session_start();if (!defined('MODULE_PATH')) {
	define ('MODULE_PATH', realpath(dirname(__FILE__)).'/lib/modules/');
}if (!defined('PLUGIN_PATH')) {
	define ('PLUGIN_PATH', realpath(dirname(__FILE__)).'/plugins/');
}
if (!defined('CFG_PATH')) {	define ('CFG_PATH', realpath(dirname(__FILE__)).'/cfg/');}if (!defined('INC_PATH')) {	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');}require_once INC_PATH.'pwTools/validator/pw_isvalid.php';require_once INC_PATH.'pwTools/string/utf8.php';require_once INC_PATH.'pwTools/string/encoding.php';require_once INC_PATH.'pwTools/string/StringTools.php';require_once INC_PATH.'pwTools/file/FileTools.php';require_once INC_PATH.'pwTools/debug/TestingTools.php';	require_once INC_PATH.'piwo-v0.2/lib/common.php';require_once INC_PATH.'piwo-v0.2/lib/lexerconf.php';require_once INC_PATH.'piwo-v0.2/lib/admin.php';require_once INC_PATH.'piwo-v0.2/lib/ui.php';require_once CFG_PATH."main.php";
$parserTokenList = glob(PLUGIN_PATH."*.php");
foreach ($parserTokenList as $plugin) {
	require_once $plugin;
}$moduleList = glob(MODULE_PATH."*.php");foreach ($moduleList as $module) {
	require_once $module;
}if (pw_wiki_getcfg('debug')) {
	TestingTools::debugOn();
}
if (isset($_GET['clearsession'])) {	$login = $_SESSION["pw_wiki"]["login"];	$_SESSION["pw_wiki"] = array();	$_SESSION["pw_wiki"]["login"] = $login;	echo "SESSION CLEARED!";	return;}ini_set('auto_detect_line_endings', false);FileTools::createFolderIfNotExist(pw_wiki_getcfg("storage"));FileTools::createFolderIfNotExist(pw_wiki_getcfg("storage")."/tpl");FileTools::copyFileIfNotExist("cfg/skeleton/tpl/firststart.txt", pw_wiki_getcfg("storage")."/index.txt");FileTools::copyMultipleFilesIfNotExist("cfg/skeleton/tpl/*.txt", pw_wiki_getcfg("storage")."/tpl/");$mode = pw_wiki_getmode();	// try {// 	$dialog = isset($_GET["dialog"]) ? $_GET["dialog"] : pw_wiki_getcfg('dialog');// } catch (Exception $e) {// 	$dialog = null;// }try {	$id = pw_wiki_getid();} catch (Exception $e) {	$oldDialog = isset($_REQUEST['olddialog']) ? $_REQUEST['olddialog'] : "";
	$dialogoutput = pw_ui_getDialogInfo("Fehler: Ung&uuml;ltige ID!", "Die angegebene ID <tt>'$id'</tt> ist nicht g&uuml;ltig!", "id=&mode=&dialog=$oldDialog");
}$wikiPageFilePath = pw_wiki_getcfg('storage').$id->getPath().pw_wiki_getcfg('fileext');try {		// Page with this id does not exist	if (!file_exists($wikiPageFilePath) && $mode != "editpage" && $mode != "showpages" && $mode != "showsource") {		$_SESSION['pw_wiki']['wrongid'] = $id;		$id = new WikiID(":tpl:notfound");		$mode = "cleared";	}} catch (Exception $e) {	$id = new WikiID(":tpl:iderror");	$mode = "cleared";}$moduleConfig = new ConfigModule();$modules = new Collection();$modules->add($moduleConfig->getName(), $moduleConfig);$wikitext = pw_wiki_showcontent($id);
$layout = file_get_contents(CFG_PATH."skeleton/wiki.html");$layout = str_replace("{{wikititle}}", pw_wiki_getcfg('wikititle'), $layout);$layout = str_replace("{{titledesc}}", pw_wiki_getcfg('titledesc'), $layout);$layout = str_replace("{{startpage}}", pw_wiki_getcfg('startpage'), $layout);$layout = str_replace("{{wikitext}}", $wikitext, $layout);
$mainpage = file_get_contents(CFG_PATH."skeleton/mainpage.html");
$mainpage = str_replace("{{pagetitle}}", pw_wiki_getfulltitle(), $mainpage);
$mainpage = str_replace("{{pagedescription}}", pw_wiki_getcfg('description'), $mainpage);
$mainpage = str_replace("{{pagekeywords}}", pw_wiki_getcfg('keywords'), $mainpage);
$mainpage = str_replace("{{body}}", $layout, $mainpage);$mainpage = str_replace("{{debugstyle}}", TestingTools::getCSS(), $mainpage);
echo $mainpage;// $dialogoutput = "";
// $output = "";// switch ($mode) {// 	case "editpage":// // 		$tempid = $id;// // 		$id = $id->getNS().$id->getPage();// 		$output = pw_wiki_editpage($id);
		// // 		if ($id != "" and $id != ":") {// // 		} else {// // 			$mode = "cleared";// // 			$_SESSION['pw_wiki']['wrongid'] = $tempid;// // 			$id = "tpl:iderror";// // 		}// 		if ($output === false) {// 			$OLDMODE = isset($_REQUEST['oldmode']) ? $_REQUEST['oldmode'] : "cleared";// 			$dialogoutput = pw_ui_getDialogInfo("Bearbeiten", "Sie sind nicht berechtigt diese Seite zu bearbeiten.", "id=$id&mode=$OLDMODE");// 		}// 	break;// 	case "cleared":// 		unset($mode);// 	break;// 	case "showpages":// 		$output = pw_wiki_showpages($id);// 	break;// 	case "showsource":// 		try {// 			$output = pw_wiki_showsource($id);// 		} catch (Exception $e) {// 			$dialogoutput = pw_wiki_filenotfound($id);// 			$output = "."; // 		}// 	break;	// 	case "update":// 		$output = pw_wiki_create_cached_page($id, true);// 	break;	// 	case "updatecache":// 		pw_wiki_update_cache();// 		$output = "UPDATING CACHE... DONE!";// 	break;	// 	case "updatecacheforced":// 		pw_wiki_update_cache(true);
// 		$output = "FORCED UPDATING CACHE... DONE!";
// 	break;// }// // if (! pw_wiki_isvalidid($id)) {// // 	$oldDialog = isset($_REQUEST['olddialog']) ? $_REQUEST['olddialog'] : "";// // 	$dialogoutput = pw_ui_getDialogInfo("Fehler: Ung&uuml;ltige ID!", "Die angegebene ID <tt>'$id'</tt> ist nicht g&uuml;ltig!", "id=&mode=&dialog=$oldDialog");// // } else {// 	switch ($dialog) {// 		case "delpage":// 			$dialogoutput = pw_wiki_delpage($id, pw_wiki_getmode());// 		break;// 		case "login":// 			$dialogoutput = pw_wiki_login($id);// 		break;// 		case "newpage":// 			$dialogoutput = pw_wiki_newpage($id, $mode);// 		break;// 		case "config":// 			$dialogoutput = $moduleConfig->getDialog();// 		break;// 		case "movepage":// 			$dialogoutput = pw_wiki_movepage($id);// 		break;// 		case "rename":// 			$dialogoutput = pw_wiki_rename($id);// 		break;// 	}// // }// if ($output == "") {// 	$mode = "cleared";// // 	if (substr($id, -1) == ":") {// // 		$id = substr($id, 0, -1);// // 	}// // 	pw_wiki_setid($id);// // 	pw_wiki_setmode($mode);// 	$output = pw_wiki_showcontent($id);// }// $modal = pw_ui_printDialogWrap($dialogoutput);// showheader($id);// if ($mode == "showpages") {// 	showpages($output);// } else {// 	showcontent($output);// }// html_footer($modal);function showpages($out) {	if ($out == "")		return;	echo "<div id='admin'>";	echo $out;	echo "</div>";}function showmenu($id) {		$mode = pw_wiki_getmode();
	$idText = $id->getID();
	// 	TestingTools::inform($idText);
	
	$msep = " | ";	if (isset($_SESSION["pw_wiki"]["login"]["user"])) {
		$u = pw_wiki_getcfg('login', 'user');
	
		$mnl = "<br />";
		echo "Benutzer: $u ";
		echo $msep;
		echo pw_ui_getButton("Logout", "mode=$mode&dialog=login&id=$idText");
		echo $msep;
		#echo "<span class='edit'><a href='?mode=$MODE&dialog=login&id=$id'><span class='shortcut'>F12</span>Logout</a></span> | ";
		#echo " <span class='edit'><a href='?mode=$MODE&dialog=config&id=$id'>Einstellungen</a></span><br />";
		echo pw_ui_getButton("Einstellungen", "mode=$mode&dialog=config&id=$idText");
	
	
		if ($mode != "editpage") {
				echo $msep;
	
				#echo $mnl;
				#echo "<span class='edit'><a href='?mode=editpage&id=$id'><span class='shortcut'>F6</span>Bearbeiten</a></span> |";
				#echo "<span class='edit'><a href='?mode=$MODE&dialog=delpage&id=$id'><span class='shortcut'>F7</span>";
				echo pw_ui_getButton("Bearbeiten", "mode=editpage&id=$idText");
				echo $msep;
				echo pw_ui_getButton("L&ouml;schen", "mode=$mode&dialog=delpage&id=$idText");
				echo $msep;
				#if ($MODE == "showpages") {
				#	echo "<span class='shortcut'>Entf</span>";
				#}
				#echo "L&ouml;schen</a></span><br />";
				#echo "<span class='edit'><a href='?mode=$MODE&dialog=movepage&id=$id'><span class='shortcut'>F8</span>Verschieben</a></span> |";
				#echo "<span class='edit'><a href='?mode=$MODE&dialog=newpage&id=$id'><span class='shortcut'>F9</span>Neu</a></span>";
				echo pw_ui_getButton("Verschieben", "mode=$mode&dialog=movepage&id=$idText");
				echo $msep;
				echo pw_ui_getButton("Neu", "mode=$mode&dialog=newpage&id=$idText");
				echo $msep;
				echo pw_ui_getButton("Umbenennen", "mode=$mode&dialog=rename&id=$idText");
				echo $msep;
				if ($mode != "showpages") {
						#echo "<br /><span class='edit'><a href='?mode=showpages&id=$id'><span class='shortcut'>F10</span>Seiten&uuml;berblick</a></span> | ";
							echo pw_ui_getButton("Seiten&uuml;berblick", "mode=showpages&id=$idText");
						} else {
						#echo " | <span class='edit'><a href='?mode=cleared&id='><span class='shortcut'>Esc</span>Schlie&szlig;en</a></span> | ";
						echo pw_ui_getButton("Schlie&szlig;en", "mode=cleared&id=$idText");
						}
		}
	
		#if ($MODE == "showpages") {
		#	echo "<span class='edit'>";
		#	echo "<span class='shortcut'>&nbsp;&uArr;&nbsp;</span>";
		#	echo "<span class='shortcut'>&nbsp;&dArr;&nbsp;</span>";
		#	echo "Navigation</span>";
		#}
	
		#echo "<span class='edit'>[<a href='#title=a' onclick=\"pw_ui_modaldialog('editmaintitle'); document.modal.maintitle.focus(); return false\">Bearbeiten</a>]</span>";
	} else {
		echo pw_ui_getButton("Login", "mode=$mode&dialog=login&id=$idText");
	
		if ($mode != "showpages") {
	
			if ($mode != 'showsource' and pw_wiki_getcfg("showsource")) {
				echo $msep;
				echo pw_ui_getButton("Quelltext anzeigen", "mode=showsource&id=$idText");
			}
	
			if (pw_wiki_getcfg("showpages")) {
				echo $msep;
				echo pw_ui_getButton("Seiten&uuml;berblick", "mode=showpages&id=$idText");
			}
	
		}
	
		if ( (pw_wiki_getcfg("showsource") and $mode == 'showsource') or (pw_wiki_getcfg("showpages") and $mode == 'showpages') ) {
			echo $msep;
			echo pw_ui_getButton("Zur&uuml;ck zur Seite", "mode=cleared&id=$idText");
		}
	}}?>