<?php//FIXME: BAD CODE SMELL... whole file!error_reporting(E_ALL);session_start();if (!defined('PLUGIN_PATH')) {
	define ('PLUGIN_PATH', realpath(dirname(__FILE__)).'/plugins/');
}
if (!defined('CFG_PATH')) {	define ('CFG_PATH', realpath(dirname(__FILE__)).'/cfg/');}if (!defined('INC_PATH')) {
	define ('INC_PATH', '../');
}require_once INC_PATH.'pwTools/validator/pw_isvalid.php';require_once INC_PATH.'pwTools/string/utf8.php';require_once INC_PATH.'pwTools/string/encoding.php';require_once INC_PATH.'pwTools/string/StringFormat.php';require_once INC_PATH.'pwTools/file/FileTools.php';require_once INC_PATH.'pwTools/debug/TestingTools.php';	require_once INC_PATH.'piwo-v0.2/lib/common.php';require_once INC_PATH.'piwo-v0.2/lib/lexerconf.php';require_once INC_PATH.'piwo-v0.2/lib/parserconf.php';require_once INC_PATH.'piwo-v0.2/lib/admin.php';require_once INC_PATH.'piwo-v0.2/lib/ui.php';require_once CFG_PATH."main.php";
$plugins = glob(PLUGIN_PATH."*.php");
foreach ($plugins as $plugin) {
	if (file_exists($plugin)) {
		require_once $plugin;
	}
}if (isset($_GET['clearsession'])) {	$login = $_SESSION["pw_wiki"]["login"];	$_SESSION["pw_wiki"] = array();	$_SESSION["pw_wiki"]["login"] = $login;	echo "SESSION CLEARED!";	return;}ini_set('auto_detect_line_endings', false);FileTools::createFolderIfNotExist(pw_wiki_getcfg("storage"));FileTools::createFolderIfNotExist(pw_wiki_getcfg("storage")."/tpl");FileTools::copyFileIfNotExist("cfg/skeleton/tpl/firststart.txt", pw_wiki_getcfg("storage")."/index.txt");FileTools::copyMultipleFilesIfNotExist("cfg/skeleton/tpl/*.txt", pw_wiki_getcfg("storage")."/tpl/");try {	$MODE = isset($_GET["mode"]) ? $_GET["mode"] : pw_wiki_getcfg('mode');} catch (Exception $e) {	$MODE = null;}	try {	$DIALOG = isset($_GET["dialog"]) ? $_GET["dialog"] : pw_wiki_getcfg('dialog');} catch (Exception $e) {	$DIALOG = null;}$id = pw_wiki_getid();if ($MODE == "cleared" or $MODE == "editpage") {	$id = rtrim($id, ':');}try {	pw_wiki_setmode($MODE);	pw_wiki_setid($id);		// Page with this id does not exist	if (!file_exists(pw_wiki_path($id, ST_FULL)) && $MODE != "editpage") {		$_SESSION['pw_wiki']['wrongid'] = $id;		$id = "tpl:notfound";		$MODE = "cleared";	}} catch (Exception $e) {	$id = "tpl:iderror";	$MODE = "cleared";}html_header();	$dialogoutput = "";$output = "";switch ($MODE) {	case "editpage":		$tempid = $id;		$id = pw_wiki_ns($id).pw_wiki_pg($id);		if ($id != "" and $id != ":") {			$output = pw_wiki_editpage($id);		} else {			$MODE = "cleared";			$_SESSION['pw_wiki']['wrongid'] = $tempid;			$id = "tpl:iderror";		}		if ($output === false) {			$OLDMODE = isset($_REQUEST['oldmode']) ? $_REQUEST['oldmode'] : "cleared";			$dialogoutput = pw_ui_getDialogInfo("Bearbeiten", "Sie sind nicht berechtigt diese Seite zu bearbeiten.", "id=$id&mode=$OLDMODE");		}	break;	case "cleared":		unset($MODE);	break;	case "showpages":		$output = pw_wiki_showpages($id);	break;	case "showsource":		$output = pw_wiki_showsource($id);	break;		case "update":		$output = pw_wiki_create_cached_page($id, true);	break;		case "updatecache":		pw_wiki_update_cache();		$output = "UPDATING CACHE... DONE!";	break;		case "updatecacheforced":		pw_wiki_update_cache(true);
		$output = "FORCED UPDATING CACHE... DONE!";
	break;}if (! pw_wiki_isvalidid($id)) {	$OLDDIALOG = isset($_REQUEST['olddialog']) ? $_REQUEST['olddialog'] : "";	$dialogoutput = pw_ui_getDialogInfo("Fehler: Ung&uuml;ltige ID!", "Die angegebene ID <tt>'$id'</tt> ist nicht g&uuml;ltig!", "id=&mode=&dialog=$OLDDIALOG");} else {	switch ($DIALOG) {		case "delpage":			$dialogoutput = pw_wiki_delpage($id);		break;		case "login":			$dialogoutput = pw_wiki_login($id);		break;		case "newpage":			$dialogoutput = pw_wiki_newpage($id);		break;		case "config":			$dialogoutput = pw_wiki_config($id);		break;		case "movepage":			$dialogoutput = pw_wiki_movepage($id);		break;		case "rename":			$dialogoutput = pw_wiki_rename($id);		break;	}}if ($output == "") {	$MODE = "cleared";	if (substr($id, -1) == ":") {		$id = substr($id, 0, -1);	}	pw_wiki_setid($id);	pw_wiki_setmode($MODE);	$output = pw_wiki_showcontent($id);}$modal = pw_ui_printDialogWrap($dialogoutput);showheader($id);if ($MODE == "showpages") {	showpages($output);} else {	showcontent($output);}html_footer($modal);function showpages($out) {	if ($out == "")		return;	echo "<div id='admin'>";	echo $out;	echo "</div>";}function showcontent($out) {	if ($out == "")		return;	echo "<div id='urhere'></div><div id='content'><div id='title'></div><div id='wikitext'>";	echo $out;	echo "</div></div>";}function showheader($id) {	global $MODE;	$msep = " | ";	echo "		<div id='__fullsite'>			<div id='header'>				<div class='right'>";	if (isset($_SESSION["pw_wiki"]["login"]["user"])) {		$u = pw_wiki_getcfg('login', 'user');		$mnl = "<br />";		echo "Benutzer: $u ";		echo $msep;		echo pw_ui_getButton("Logout", "mode=$MODE&dialog=login&id=$id");		echo $msep;		#echo "<span class='edit'><a href='?mode=$MODE&dialog=login&id=$id'><span class='shortcut'>F12</span>Logout</a></span> | ";		#echo " <span class='edit'><a href='?mode=$MODE&dialog=config&id=$id'>Einstellungen</a></span><br />";		echo pw_ui_getButton("Einstellungen", "mode=$MODE&dialog=config&id=$id");		if ($MODE != "editpage") {			echo $msep;			#echo $mnl;			#echo "<span class='edit'><a href='?mode=editpage&id=$id'><span class='shortcut'>F6</span>Bearbeiten</a></span> |";			#echo "<span class='edit'><a href='?mode=$MODE&dialog=delpage&id=$id'><span class='shortcut'>F7</span>";			echo pw_ui_getButton("Bearbeiten", "mode=editpage&id=$id");			echo $msep;			echo pw_ui_getButton("L&ouml;schen", "mode=$MODE&dialog=delpage&id=$id");			echo $msep;			#if ($MODE == "showpages") {			#	echo "<span class='shortcut'>Entf</span>";			#}			#echo "L&ouml;schen</a></span><br />";			#echo "<span class='edit'><a href='?mode=$MODE&dialog=movepage&id=$id'><span class='shortcut'>F8</span>Verschieben</a></span> |";			#echo "<span class='edit'><a href='?mode=$MODE&dialog=newpage&id=$id'><span class='shortcut'>F9</span>Neu</a></span>";			echo pw_ui_getButton("Verschieben", "mode=$MODE&dialog=movepage&id=$id");			echo $msep;			echo pw_ui_getButton("Neu", "mode=$MODE&dialog=newpage&id=$id");			echo $msep;			echo pw_ui_getButton("Umbenennen", "mode=$MODE&dialog=rename&id=$id");			echo $msep;			if ($MODE != "showpages") {				#echo "<br /><span class='edit'><a href='?mode=showpages&id=$id'><span class='shortcut'>F10</span>Seiten&uuml;berblick</a></span> | ";				echo pw_ui_getButton("Seiten&uuml;berblick", "mode=showpages&id=$id");			} else {				#echo " | <span class='edit'><a href='?mode=cleared&id='><span class='shortcut'>Esc</span>Schlie&szlig;en</a></span> | ";				echo pw_ui_getButton("Schlie&szlig;en", "mode=cleared&id=$id");			}		}		#if ($MODE == "showpages") {		#	echo "<span class='edit'>";		#	echo "<span class='shortcut'>&nbsp;&uArr;&nbsp;</span>";		#	echo "<span class='shortcut'>&nbsp;&dArr;&nbsp;</span>";		#	echo "Navigation</span>";		#}		#echo "<span class='edit'>[<a href='#title=a' onclick=\"pw_ui_modaldialog('editmaintitle'); document.modal.maintitle.focus(); return false\">Bearbeiten</a>]</span>";	} else {		echo pw_ui_getButton("Login", "mode=$MODE&dialog=login&id=$id");		if ($MODE != "showpages") {			if ($MODE != 'showsource' and pw_wiki_getcfg("showsource")) {				echo $msep;				echo pw_ui_getButton("Quelltext anzeigen", "mode=showsource&id=$id");			}			if (pw_wiki_getcfg("showpages")) {				echo $msep;				echo pw_ui_getButton("Seiten&uuml;berblick", "mode=showpages&id=$id");			}		}		if ( (pw_wiki_getcfg("showsource") and $MODE == 'showsource') or (pw_wiki_getcfg("showpages") and $MODE == 'showpages') ) {			echo $msep;			echo pw_ui_getButton("Zur&uuml;ck zur Seite", "mode=cleared&id=$id");		}	}	echo "</div>";	echo "<span class='title'><a href='?mode=cleared&id=".pw_wiki_getcfg('startpage')."'>".pw_wiki_getcfg('wikititle')."|</a></span>				<span class='titledesc'>".pw_wiki_getcfg('titledesc')."</span>			</div>";	echo "		<div id='sidebar'>			<div id='menue'>			</div>			<div id='info'>			</div>		</div>		<div id='__main'>";}?>