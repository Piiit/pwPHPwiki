<?phperror_reporting(E_ALL | E_STRICT);if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
if (!defined('MODULE_PATH')) {
	define ('MODULE_PATH', INC_PATH.'piwo-v0.2/lib/modules/');
}if (!defined('PLUGIN_PATH')) {
	define ('PLUGIN_PATH', INC_PATH.'piwo-v0.2/plugins/');
}
if (!defined('CFG_PATH')) {	define ('CFG_PATH', INC_PATH.'piwo-v0.2/cfg/');}require_once INC_PATH.'piwo-v0.2/lib/common.php';require_once INC_PATH.'piwo-v0.2/lib/lexerconf.php';require_once INC_PATH.'piwo-v0.2/lib/admin.php';require_once CFG_PATH."main.php";require_once INC_PATH.'pwTools/system/SystemTools.php';SystemTools::autoloadInit(	array(		PLUGIN_PATH, 		MODULE_PATH, 		INC_PATH."pwTools",		INC_PATH."piwo-v0.2/lib"	));
session_start();pw_wiki_loadconfig();
// TestingTools::inform($_REQUEST);
// TestingTools::inform($_SESSION);FileTools::createFolderIfNotExist(WIKISTORAGE);FileTools::createFolderIfNotExist(WIKISTORAGE."/tpl");FileTools::copyFileIfNotExist("cfg/skeleton/tpl/firststart.txt", WIKISTORAGE."/index.txt");FileTools::copyMultipleFilesIfNotExist("cfg/skeleton/tpl/*.txt", WIKISTORAGE."/tpl/");	new LoginModule();
new ConfigModule();new ShowSourceModule();new EditModule(); new ShowPagesModule();new NewPageModule();new DeletePageModule();new DeleteNamespaceModule();$defaultModule = new ShowContentModule();//  TestingTools::inform($_GET);//  TestingTools::inform($_POST); $module = null;$notification = "";$scriptsText = "";$mode = pw_wiki_getmode() == null ? $defaultModule->getName() : pw_wiki_getmode();
try {	try {		$module = Module::getModuleList()->get($mode);		if($module instanceof JavaScriptProvider) {
			$scriptsText .= $module->getJavaScript()."<!-- INSERTED BY MODULE ".$module->getName()." -->\n";		}
			} catch (Exception $e) {		throw new Exception("Mode with ID '$mode' does not exist!");	}	if ($module instanceof PermissionProvider && !$module->permissionGranted()) {		throw new Exception("Module '".$module->getName()."': Access denied.");	}	$module->execute();		} catch (Exception $e) {	$notification = $e->getMessage();	$notificationType = "error";}$notification = $notification == null ? $module->getNotification() : $notification;if($notification != null) {	if(!isset($notificationType)) {
		$notificationType = $module->getNotificationType() == Module::NOTIFICATION_INFO ? "info" : "error";	}	$notification = "<div id='notification' class='$notificationType'>$notification</div>";
}
$body = $module == null ? null : $module->getDialog();if($body == null) {	$defaultModule->execute();	$body = $defaultModule->getDialog();}$menu = pw_wiki_getmenu(pw_wiki_getid(), $mode, Module::getModuleList()); 

$mainpage = file_get_contents(CFG_PATH."skeleton/mainpage.html");
$mainpage = str_replace("{{pagetitle}}", pw_wiki_getfulltitle(), $mainpage);
$mainpage = str_replace("{{pagedescription}}", WIKIDESCRIPTION, $mainpage);
$mainpage = str_replace("{{pagekeywords}}", WIKIKEYWORDS, $mainpage);
$mainpage = str_replace("{{scripts}}", rtrim($scriptsText), $mainpage);
$mainpage = str_replace("{{notification}}", $notification, $mainpage);$mainpage = str_replace("{{wikititle}}", pw_wiki_getcfg('wikititle'), $mainpage);
$mainpage = str_replace("{{titledesc}}", pw_wiki_getcfg('titledesc'), $mainpage);
$mainpage = str_replace("{{startpage}}", WIKISTARTPAGE, $mainpage);
$mainpage = str_replace("{{body}}", $body, $mainpage);$mainpage = str_replace("{{debugstyle}}", TestingTools::getCSS(), $mainpage);
$mainpage = str_replace("{{mainmenu}}", $menu, $mainpage);
echo $mainpage;// 	case "update":
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