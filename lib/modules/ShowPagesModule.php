<?php

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
		$id = WikiTools::getCurrentID();
		$path = WIKISTORAGE.$id->getFullNSPath();
		
		$files = array();
		$dirs = array();
		$directoryContent = glob("$path/*");
	
		foreach ($directoryContent as $fileOrDir) {
			$fileOrDir = pw_s2u($fileOrDir);
			if ($fileOrDir != utf8_strtolower($fileOrDir)) {
				rename(pw_u2t($fileOrDir), pw_u2t(utf8_strtolower($fileOrDir)));
				// TODO: falls neue Datei bereits existiert ??? Fehler melden... Benutzereingabe fordern!
			}
			$fileOrDir = utf8_strtolower($fileOrDir);

			if(!is_readable($fileOrDir)) {
				throw new Exception("File '".$fileOrDir."' is not readable. Can not execute module '".$this->getName()."'.");
			}
				
			if (is_dir($fileOrDir)) {
				$dirs[] = array(
							'NAME' => FileTools::basename(pw_s2e($fileOrDir)), 
							'TYPE' => "DIR",
							'SPECIALCHAR' => (pw_s2e($fileOrDir) != $fileOrDir)
							);
			} else {
				$files[] = array(
						'NAME' => FileTools::basename(pw_s2e($fileOrDir), ".txt"),
						'TYPE' => "TEXT",
						'SIZE' => filesize($fileOrDir),
						'MODIFIED' => filemtime($fileOrDir),
						'SPECIALCHAR' => (pw_s2e($fileOrDir) != $fileOrDir)
				);
			}
		}
	
		sort($dirs);
		sort($files);
		
		$out = "<div class='admin'><a href='?id=".$id->getIDAsUrl()."'>&laquo; Back</a><hr />";
		$out .= "<h1>Pages</h1>";
		$out .= "You are here: ".pw_wiki_trace($id)."";
		$out .= "<table class='overview' style='width: 100%'><tr><th style='width: 40%'>Name</th><th style='width: 10%'>Size</th><th style='width: 17%'>Modified</th><th>Options</th></tr>";

		foreach (array_merge($dirs, $files) as $fileOrDir) {

			/*
			 * Do not show default namespace pages and template folders.
			 */
			if(($fileOrDir['NAME'] == WIKINSDEFAULTPAGE && $fileOrDir['TYPE'] == "TEXT") ||
			   ($fileOrDir['NAME'] == trim(WIKITEMPLATESNS, ":")) && $fileOrDir['TYPE'] == "DIR") {
				continue;
			}
			
			/*
			 * Build the file and directory table, first dirs, then files.
			 */
			$out .= "<tr style='height: 40px'>";
			$encDisplayName = $fileOrDir['SPECIALCHAR'] ? $fileOrDir['NAME'] : pw_s2e($fileOrDir['NAME']);
			$encID = $fileOrDir['SPECIALCHAR'] ? pw_u2t($fileOrDir['NAME']) : $fileOrDir['NAME'];
			$encFullIDAsURL = pw_s2url($id->getFullNS().$encID);
			if ($fileOrDir['TYPE'] == "TEXT") {
				$out .= "<td><small>[TXT]</small> <a href='?id=".$encFullIDAsURL."'>";
				$out .= $encDisplayName;
				$out .= "</a></td>";
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
					$out .= "<a href='?id=".$encFullIDAsURL."&mode=edit'>Edit</a> | ";
					$out .= "<a href='?id=".$encFullIDAsURL."&mode=deletepage'>Delete</a> | ";
					$out .= "<a href='?id=".$encFullIDAsURL."&mode=rename'>Rename</a> | ";
					$out .= "<a href='?id=".$encFullIDAsURL."&mode=move'>Move</a>";
				} else {
					$out .= "<a href='?id=".$encFullIDAsURL."&mode=showsource'>Show Source</a>";
				}
			} else {
				if ($fileOrDir['NAME'] != '..') {
					if (pw_wiki_getcfg('login', 'group') == 'admin') {
						$out .= "<a href='?id=".$encFullIDAsURL.":"."&mode=deletenamespace'>Delete</a> | ";
						$out .= "<a href='?id=".$encFullIDAsURL.":"."&mode=rename'>Rename</a> | ";
						$out .= "<a href='?id=".$encFullIDAsURL.":"."&mode=move'>Move</a>";
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