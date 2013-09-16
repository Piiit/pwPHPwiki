<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/modules/ModuleHandler.php';
require_once INC_PATH.'piwo-v0.2/lib/modules/Module.php';

class ShowPagesModule implements ModuleHandler {
	
	public function getName() {
		return "showpages";
	}

	public function getVersion() {
		return "20130915";
	}

	public function permissionGranted($userData) {
		return true;
	}
	
	public function getDialog() {
		$id = pw_wiki_getid();
		$path = pw_wiki_getcfg('storage').$id->getFullNSPath();
		
		$strout = "";
		$files = array();
		$dirs = array();
		if(!$id->isRootNS()) {
			$dirs[] = array('NAME' => '..', 'TYPE' => 'DIR');
		}
		$data = glob ("$path/*");
	
		// Leeres Verz. gefunden... Löschen!
		if (!$data) {
			if ($path != pw_wiki_getcfg('storage') && rmdir($path)) {
				$strout .= "<tt>INFO: $path ist leer und wird entfernt!</tt>";
			}
		} else {
			foreach ($data as $k => $i) {
				$i = pw_s2u($i);
				if ($i != utf8_strtolower($i)) {
					rename(pw_u2t($i), pw_u2t(utf8_strtolower($i)));
					// TODO: falls neue Datei bereits existiert ??? Fehler melden... Benutzereingabe fordern!
				}
				$i = utf8_strtolower($i);
				$i = pw_u2t($i);
	
				if (is_dir($i)) {
					$dirs[] = array('NAME' => pw_basename($i), 'TYPE' => "DIR");
				} else {
					$files[] = array('NAME' => pw_basename($i, ".txt"), 'TYPE' => "TEXT", 'SIZE' => filesize($i));
				}
	
			}
		}
	
		if ($dirs) sort($dirs);
		if ($files) sort($files);
	
		$out = array_merge($dirs, $files);
		
	//	 TestingTools::inform($out);
		
		$strout .= "<h1>Pages</h1>";
		$strout .= "You are here: ".pw_wiki_trace($id->getFullNS())."";
		$strout .= "<table id='overview'><tr><th style='width:15px'>#</th><th style='width: 380px'>ID (Preview)</th><th style='width: 70px'>Size</th><th style='width: 60px'>Type</th><th>Options</th></tr>";
		$nr = 0;
		foreach ($out as $k => $i) {
	
			$strout .= "<tr style='background: black'>";
			$strout .= "<td style='text-align: right'>".($nr++)."</td>";
			if ($i['TYPE'] == "TEXT") {
				$strout .= "<td>";
				$strout .= pw_url2e($i['NAME']);
				$strout .= "<a style='float: right' href='?id=".pw_s2url($id->getFullNS().$i['NAME'])."'>&laquo; show</a>";
				$strout .= "</td>";
			} else {
				 $strout .= "<td><a href='?id=".pw_s2url(WikiID::cleanNamespaceString($id->getFullNS().$i['NAME'].':'))."&mode=showpages'>".pw_url2e($i['NAME'])."</a></td>";
			}
			$strout .= "<td style='text-align: right'>";
			if ($i['TYPE'] == "TEXT") {
				$strout .= "<small><tt>".pw_formatbytes($i['SIZE'], 2, false)."</tt></small>";
			} else {
				$strout .= "<small><tt>-</tt></small>";
			}
			$strout .= "<td>".$i['TYPE']."</td>";
			$strout .= "<td>";
	
			if ($i['TYPE'] == "TEXT") {
				if (isset($_SESSION["pw_wiki"]["login"]["user"])) {
					$strout .= "<a href='?id=".pw_s2url($id->getFullNS().$i['NAME'])."&mode=edit&oldmode=showpages'>Edit</a> | ";
					$strout .= "<a href='?id=".pw_s2url($id->getFullNS().$i['NAME'])."&mode=showpages&dialog=delpage'>Delete</a> | ";
					$strout .= "<a href='?id=".pw_s2url($id->getFullNS().$i['NAME'])."&mode=showpages&dialog=rename'>Rename</a> | ";
					$strout .= "<a href='?id=".pw_s2url($id->getFullNS().$i['NAME'])."&mode=showpages&dialog=movepage'>Move</a>";
					#$strout .= "[<a href='?id=".$ns.$i['NAME']."&mode=showpages&dialog=info'>Info</a>]";
				} else {
					$strout .= "<a href='?id=".pw_s2url($id->getFullNS().$i['NAME'])."&mode=showsource&oldmode=showpages'>Show Source</a>";
				}
			} else {
				if ($i['NAME'] != '..') {
					if (isset($_SESSION["pw_wiki"]["login"]["user"])) {
						$strout .= "<a href='?id=".pw_s2url($id->getFullNS().$i['NAME'].":")."&mode=showpages&dialog=delpage'>Delete</a> | ";
						$strout .= "<a href='?id=".pw_s2url($id->getFullNS().$i['NAME'].":")."&mode=showpages&dialog=rename'>Rename</a> | ";
						$strout .= "<a href='?id=".pw_s2url($id->getFullNS().$i['NAME'].":")."&mode=showpages&dialog=movepage'>Move</a>";
					}
				}
			}
			$strout .= "</td>";
			$strout .= "</tr>";
		}
	
		$strout .= "</table>";
		return $strout;
			
	}
	
	public function getMenuText() {
		return "Show Pages";
	}

	public function getMenuAvailability($mode) {
		return true; //For all modes available
	}
	
}

?>