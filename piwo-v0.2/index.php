<?phperror_reporting(E_ALL);session_start();//session_unset();if (!defined('MODULE_PATH')) {
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
//TODO create a module for clearsession, or put it to configuration...if (isset($_GET['clearsession'])) {	$login = pw_wiki_getcfg("login");	session_unset();	pw_wiki_setcfg("login", $login);	echo "SESSION CLEARED!";	return;}ini_set('auto_detect_line_endings', false);FileTools::createFolderIfNotExist(pw_wiki_getcfg("storage"));FileTools::createFolderIfNotExist(pw_wiki_getcfg("storage")."/tpl");FileTools::copyFileIfNotExist("cfg/skeleton/tpl/firststart.txt", pw_wiki_getcfg("storage")."/index.txt");FileTools::copyMultipleFilesIfNotExist("cfg/skeleton/tpl/*.txt", pw_wiki_getcfg("storage")."/tpl/");$mode = pw_wiki_getmode();// TestingTools::inform($_REQUEST);// TestingTools::inform($_SESSION);	//TODO handle modes before id checks, every mode has its own constraints...try {	$id = pw_wiki_getid();} catch (Exception $e) {}$wikiPageFilePath = pw_wiki_getcfg('storage').$id->getPath().pw_wiki_getcfg('fileext');try {		// Page with this id does not exist	if (!file_exists($wikiPageFilePath) && $mode != "edit" && $mode != "showpages" && $mode != "showsource") {		$_SESSION['pw_wiki']['wrongid'] = $id;		$id = new WikiID(":tpl:notfound");	}} catch (Exception $e) {	$id = new WikiID(":tpl:iderror");}$moduleLogin = new LoginModule();
$moduleConfig = new ConfigModule();$moduleShowSource = new ShowSourceModule();$moduleEdit = new EditModule();$moduleShowPages = new ShowPagesModule();$moduleNewPage = new NewPageModule();$modules = new Collection();$modules->add($moduleLogin->getName(), $moduleLogin);$modules->add($moduleConfig->getName(), $moduleConfig);$modules->add($moduleShowSource->getName(), $moduleShowSource);$modules->add($moduleEdit->getName(), $moduleEdit);
$modules->add($moduleShowPages->getName(), $moduleShowPages);$modules->add($moduleNewPage->getName(), $moduleNewPage);$scriptsText = "";foreach($modules->getArray() as $module) {	if($module instanceof JavaScriptProvider && $module->getName() == pw_wiki_getmode()) {		$scriptsText .= $module->getJavaScript()."<!-- INSERTED BY MODULE ".$module->getName()." -->\n";	}}//  TestingTools::inform($_GET);//  TestingTools::inform($_POST); $module = null;$notification = "";$body = "<Nothing to show>";//TODO Mode-handling: change everything to an event-handling-pattern...try {	$mode = pw_wiki_getcfg("eventmode");// 	TestingTools::inform("EVENTMODE is ".pw_wiki_getcfg("eventmode"));		pw_wiki_setcfg("event", true);} catch (Exception $e) {	$mode = pw_wiki_getmode();} if($mode != null) {	try {		try {			$module = $modules->get($mode);		} catch (Exception $e) {			throw new Exception("Mode with ID '$mode' does not exist!");		}		if (!$module->permissionGranted(pw_wiki_getcfg('login'))) {			throw new Exception("Module '".$module->getName()."': Access denied.");		}// 		TestingTools::inform($module->getName());		$module->execute();				//TODO Eventhandling: use bind and trigger, or EventConsumer, EventProvider pattern, or similar...		try {// 	 		TestingTools::inform("EVENTMODE is ".pw_wiki_getcfg("eventmode"));	 		foreach($modules->getArray() as $m) {	 			if($m->getName() == pw_wiki_getcfg("eventmode")) {	 				$m->execute();	 				$module = $m;	 				break;	 			}	 		}		} catch (Exception $e) {		}			} catch (Exception $e) {		$notification = $e->getMessage();		$notificationType = "error";	}// 	TestingTools::inform($module->getName());
	$body = $module->getDialog();
	$notification = $notification == null ? $module->getNotification() : $notification;	$notificationType = $module->getNotificationType() == Module::NOTIFICATION_INFO ? "info" : "error";
	if($notification != null) {
		$notification = "<div id='notification' class='$notificationType'>$notification</div>";
	}
		if(pw_wiki_getcfg("event") == true) {		pw_wiki_setcfg("event", false);		pw_wiki_unsetcfg("eventmode"); 	}}$menu = pw_wiki_getmenu($id, $mode, $modules);

$mainpage = file_get_contents(CFG_PATH."skeleton/mainpage.html");
$mainpage = str_replace("{{pagetitle}}", pw_wiki_getfulltitle(), $mainpage);
$mainpage = str_replace("{{pagedescription}}", pw_wiki_getcfg('description'), $mainpage);
$mainpage = str_replace("{{pagekeywords}}", pw_wiki_getcfg('keywords'), $mainpage);
$mainpage = str_replace("{{scripts}}", rtrim($scriptsText), $mainpage);
$mainpage = str_replace("{{notification}}", $notification, $mainpage);$mainpage = str_replace("{{wikititle}}", pw_wiki_getcfg('wikititle'), $mainpage);
$mainpage = str_replace("{{titledesc}}", pw_wiki_getcfg('titledesc'), $mainpage);
$mainpage = str_replace("{{startpage}}", pw_wiki_getcfg('startpage'), $mainpage);
if(!isset($_GET['mode']) || ($module != null && $module->getDialog() == null)) {	//TODO show content should also become a module! Everything is a module!!! :)	$wikitext = pw_wiki_showcontent($id);	$body = file_get_contents(CFG_PATH."skeleton/wiki.html");
	$body = str_replace("{{wikitext}}", $wikitext, $body);
}$mainpage = str_replace("{{body}}", $body, $mainpage);$mainpage = str_replace("{{debugstyle}}", TestingTools::getCSS(), $mainpage);
$mainpage = str_replace("{{mainmenu}}", $menu, $mainpage);
echo $mainpage;function pw_wiki_getmenu($id, $mode, Collection $modules) {	$loginData = pw_wiki_getcfg('login');	$o = "";	foreach ($modules->getArray() as $module) {		if($module->getMenuAvailability($mode) && $module->permissionGranted($loginData)) {			$o .= GuiTools::button($module->getMenuText(), "id=".$id->getID()."&mode=".$module->getName());			$o .= " | ";		}	}		return $o;}// 	case "update":
// 		$output = pw_wiki_create_cached_page($id, true);
// 	break;

// 	case "updatecache":
// 		pw_wiki_update_cache();
// 		$output = "UPDATING CACHE... DONE!";
// 	break;

// 	case "updatecacheforced":
// 		pw_wiki_update_cache(true);
// 		$output = "FORCED UPDATING CACHE... DONE!";
// 	break;
// }
?>