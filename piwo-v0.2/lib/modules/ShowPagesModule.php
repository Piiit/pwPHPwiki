<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';

class ShowPagesModule extends Module implements ModuleHandler, MenuItemProvider {
	
	public function __construct() {
		parent::__construct($this->getName(), $this);
	}
	
	public function getName() {
		return "showpages";
	}

	public function getVersion() {
		return "20130915";
	}

	public function execute() {
		$id = pw_wiki_getid();
		
		//FIXME getFullNSPath gives a url and not a filesystem path! BUG!!!
		$path = WIKISTORAGE.$id->getFullNSPath();
		
		$files = array();
		$dirs = array();
		$directoryContent = glob("$path/*");
	
		//TODO Delete empty folders!  should be optional!
		if (!$directoryContent) { 
			if ($path != WIKISTORAGE) {
				try {
					FileTools::removeDirectory($path);
					$this->setNotification("'".$id->getNSAsHtmlEntities()."' is empty.<br />It has been deleted!");
				} catch (Exception $e) {
					$this->setNotification("Unable to delete '".$id->getNSAsHtmlEntities()."'!<br />".$e->getMessage(), Module::NOTIFICATION_ERROR);
				}
			}
		} else {
			foreach ($directoryContent as $fileOrDir) {
				$fileOrDir = pw_s2u($fileOrDir);
				if ($fileOrDir != utf8_strtolower($fileOrDir)) {
					rename(pw_u2t($fileOrDir), pw_u2t(utf8_strtolower($fileOrDir)));
					// TODO: falls neue Datei bereits existiert ??? Fehler melden... Benutzereingabe fordern!
				}
				$fileOrDir = utf8_strtolower($fileOrDir);
				$fileOrDir = pw_u2t($fileOrDir);
	
				if (is_dir($fileOrDir)) {
					$dirs[] = array('NAME' => FileTools::basename($fileOrDir), 'TYPE' => "DIR");
				} else {
					$files[] = array('NAME' => FileTools::basename($fileOrDir, ".txt"), 'TYPE' => "TEXT", 'SIZE' => filesize($fileOrDir), 'MODIFIED' => filemtime($fileOrDir));
				}
	
			}
		}
	
		sort($dirs);
		sort($files);
		
		$out = "<div class='admin'><a href='?id=".$id->getIDAsUrl()."'>&laquo; Back</a><hr />";
		$out .= "<h1>Pages</h1>";
		$out .= "You are here: ".pw_wiki_trace($id)."";
		$out .= "<table class='overview' style='width: 100%'><tr><th style='width: 40%'>Name</th><th style='width: 10%'>Size</th><th style='width: 17%'>Modified</th><th>Options</th></tr>";

		foreach (array_merge($dirs, $files) as $fileOrDir) {
// 			TestingTools::inform($fileOrDir);
			//Do not show default namespace pages and template folders.
			if(($fileOrDir['NAME'] == WIKINSDEFAULTPAGE && $fileOrDir['TYPE'] == "TEXT") ||
			   ($fileOrDir['NAME'] == trim(WIKITEMPLATESNS, ":")) && $fileOrDir['TYPE'] == "DIR") {
				continue;
			}
			$out .= "<tr style='height: 40px'>";
			if ($fileOrDir['TYPE'] == "TEXT") {
				$out .= "<td><small>[TXT]</small> <a href='?id=".pw_s2url($id->getFullNS().$fileOrDir['NAME'])."'>".pw_s2e($fileOrDir['NAME'])."</a></td>";
				$out .= "<td style='text-align: right'><tt>".StringTools::showReadableFilesize($fileOrDir['SIZE'], 2, false)."</tt></td>";
				$out .= "<td style='text-align: right'>".date("d.m.Y H:i", $fileOrDir['MODIFIED'])."</td>";
			} else {
				$out .= "<td><a href='?id=".pw_s2url(WikiID::cleanNamespaceString($id->getFullNS().$fileOrDir['NAME'].':'))."&mode=showpages'>".pw_s2e($fileOrDir['NAME'])."</a></td>";
				$out .= "<td>&nbsp</td>";
				$out .= "<td>&nbsp</td>";
			}

			$out .= "<td style='padding-left: 20px'>";
	
			if ($fileOrDir['TYPE'] == "TEXT") {
				if (pw_wiki_getcfg('login', 'group') == 'admin') {
					$out .= "<a href='?id=".pw_s2url($id->getFullNS().$fileOrDir['NAME'])."&mode=edit'>Edit</a> | ";
					$out .= "<a href='?id=".pw_s2url($id->getFullNS().$fileOrDir['NAME'])."&mode=deletepage'>Delete</a> | ";
					$out .= "<a href='?id=".pw_s2url($id->getFullNS().$fileOrDir['NAME'])."&mode=rename'>Rename</a> | ";
					$out .= "<a href='?id=".pw_s2url($id->getFullNS().$fileOrDir['NAME'])."&mode=move'>Move</a>";
				} else {
					$out .= "<a href='?id=".pw_s2url($id->getFullNS().$fileOrDir['NAME'])."&mode=showsource'>Show Source</a>";
				}
			} else {
				if ($fileOrDir['NAME'] != '..') {
					if (pw_wiki_getcfg('login', 'group') == 'admin') {
						$out .= "<a href='?id=".pw_s2url($id->getFullNS().$fileOrDir['NAME'].":")."&mode=deletenamespace'>Delete</a> | ";
						$out .= "<a href='?id=".pw_s2url($id->getFullNS().$fileOrDir['NAME'].":")."&mode=rename'>Rename</a> | ";
						$out .= "<a href='?id=".pw_s2url($id->getFullNS().$fileOrDir['NAME'].":")."&mode=move'>Move</a>";
					}
				}
			}
			$out .= "</td>";
			$out .= "</tr>";
		}
	
		$out .= "</table></div>";
		$this->setDialog($out);
			
	}
	
	public function getMenuText() {
		return "Show&nbsp;Pages";
	}

	public function getMenuAvailability() {
		return true; 
	}
	
}

?>