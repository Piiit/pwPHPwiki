<?php//@TODO: frontend und backend trennen... UserInterface in separate Datei ablegen!if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/common.php';require_once INC_PATH.'pwTools/string/encoding.php';require_once INC_PATH.'piwo-v0.2/lib/pw_debug.php';function pw_wiki_showsource ($id) {  $filename = pw_wiki_path($id, ST_FULL);  $out = pw_n("<h1>Quelltext von <tt>$id</tt></h1>");  $out .= pw_n("<i>Sie sind nicht berechtigt diesen Quelltext zu bearbeiten.</i><br />");  if (file_exists($filename) and !isset($_POST['save'])) {    $data = file_get_contents($filename);    $data = pw_wiki_LE_unix($data);    $data = pw_wiki_file2editor($data);    $out .= pw_n("<br /><a href='?mode=cleared&id=$id'>&laquo; Zur&uuml;ck zur Seite</a>");    $out .= pw_n("<a style='float: right' href='?mode=showpages&id=$id'>Zum Seiten&uuml;berblick &raquo;</a><hr />");    $out .= pw_n("<textarea cols='80' rows='25' id='wikitxt' readonly='readonly' wrap='off'>$data</textarea>");    $out .= pw_n("<hr /><a href='?mode=cleared&id=$id'>&laquo; Zur&uuml;ck zur Seite</a>");    $out .= pw_n("<a style='float: right' href='?mode=showpages&id=$id'>Zum Seiten&uuml;berblick &raquo;</a>");    return $out;  }  // Leere Rückgabe, damit ein "notfound"-Fehler ausgelöst wird...  return;}
function pw_wiki_editpage ($id) {
  #out(pw_wiki_isvalidid(pw_wiki_pg($id)));
  $data = "";  $ret = "";  if (!isset($_SESSION["pw_wiki"]["login"]["user"]))    return false;
  if (pw_wiki_isns($id))
    return;
  $filename = pw_wiki_path($id, ST_FULL);  #out2(utf8_check($filename));  if (file_exists($filename) and !isset($_POST['save'])) {    $data = file_get_contents($filename);    $data = pw_wiki_LE_unix($data);    $filename = pw_s2e($filename);    $ret = "<tt>Datei '$filename' wurde geladen</tt>";  }  if (isset($_POST["save"])) {    $data = $_POST['wikitxt'];    $data = pw_stripslashes($data);    $data = pw_s2u($data);    $data = pw_wiki_LE_unix($data);    // @TODO: What is config?    $config = null;    $ret = pw_wiki_savepage ($id, $data, $config);  }  $data = pw_wiki_file2editor($data);  $id = pw_wiki_path2id($filename);  $OLDMODE = isset($_REQUEST['oldmode']) ? $_REQUEST['oldmode'] : "cleared";  $out = pw_n();  $out .= pw_n("<!-- EDITOR START -->", START);  $out .= pw_n("<form id='texteditor' name='texteditor' method='post' accept-charset='utf-8'>", START);  $out .= pw_n("<div id='editor_win' style='width: 100%; border: 0;'>", START);  $out .= pw_n("<button  value='save' name='save' id='save'>Speichern</button><a id='exiteditor' class='textinput' href='?id=$id&mode=$OLDMODE'>Abbrechen</a>");  $out .= pw_n("<span style='float: right'>$ret</span>");  $out .= pw_n("<label style='display: block; border: 0; padding: 0; margin: 0'>", START);  $out .= pw_n("<textarea cols='80' rows='25' name='wikitxt' id='wikitxt' wrap=off onkeydown='return catchTab(this,event)'>$data</textarea>");  $out .= pw_n("</label>", END);  $out .= pw_n("</div>", END);  $out .= pw_n("</form>", END);  $out .= pw_n("<!-- EDITOR END -->", END);  $out .= pw_n();  return $out;}
function pw_wiki_newpage ($id) {  if (!isset($_SESSION["pw_wiki"]["login"]["user"]))
    return false;

  global $MODE;

  $idurl = $id;
  $id = pw_s2e(utf8_rtrim(pw_url2u($id), ':').':');

  $entries  = pw_n("<input type='hidden' name='mode' value='editpage' />");  $entries .= pw_n("<input type='hidden' name='oldmode' value='$MODE' />");  $entries .= pw_n("<input type='hidden' name='olddialog' value='newpage' />");  $entries .= pw_n("<label for='id'>ID:</label> <input type='text' class='textinput' name='id' id='id' value='$id' />");  $entries .= pw_n("<br /><hr /><tt><small>Namensr&auml;ume werden mit : voneinander getrennt!<br />Bsp.: Handbuch:Seite1<br />Falls die Seite schon existiert, wird sie zum Bearbeiten ge&ouml;ffnet.</small></tt>");
  return pw_ui_getDialogQuestion("Neue Seite erstellen", $entries, "create", "OK", "id=$idurl&mode=$MODE", "get");

}

function pw_wiki_config ($id) {  if (!isset($_SESSION["pw_wiki"]["login"]["user"]))
    return false;

  global $MODE;

  if (isset($_POST["config"])) {    if ($_POST['debug']) {      $_SESSION['pw_wiki']['debug'] = true;    } else {      $_SESSION['pw_wiki']['debug'] = false;      #unset($_SESSION['pw_debug']);      pw_debug_init(false);    }    return;
  }
  $debug_ch = "";
  if (pw_wiki_getcfg('debug')) {    $debug_ch = " checked='checked' ";
  }

  $entries = pw_n("<input type='hidden' name='oldmode' value='$MODE' />");  $entries .= pw_n("<label for='debug'>Debug-Modus:</label> <input type='checkbox' name='debug' id='debug'$debug_ch />");
  return pw_ui_getDialogQuestion("Einstellungen", $entries, "config", "OK", "id=$id&mode=$MODE");

}

function pw_wiki_savepage ($id, $data) {

  // Kontrolliere die Berechtigungen  if (!isset($_SESSION["pw_wiki"]["login"]["user"]))
    return false;

  $filename = pw_wiki_path($id, ST_FULL);
  $dirname = pw_wiki_path($id, ST_SHORT);

  // Kontrolliere, ob der Ordner existiert und lege ihn ggf. an  $dirnames = explode("/", $dirname);  $dn = "";  foreach ($dirnames as $dirname) {    $dn .= $dirname."/";    if (!file_exists($dn)) {      if (!mkdir($dn)) {        $ret = "<tt class='error'>Der Ordner '$dn' konnte nicht angelegt werden.</tt>";        return $ret;      }    }
  }

  $data = pw_wiki_LE_unix($data);
  $ret = @file_put_contents($filename, $data);

  $filename = pw_s2e($filename);  if ($ret !== false) {    $ret = "<tt>Datei '$filename' wurde gespeichert.</tt>";  } else {    $ret = "<tt class='error'>Datei '$filename' konnte nicht gespeichert werden.</tt>";
  }    pw_wiki_create_cached_page($id);

  return $ret;
}

function pw_wiki_rename ($id) {
  global $MODE;

  if (!isset($_SESSION["pw_wiki"]["login"]["user"])) {    return pw_ui_getDialogInfo("Verschieben", "Sie sind nicht berechtigt eine Seite zu verschieben...", "id=$id&mode=$MODE");
  }

  $fullfilename = pw_wiki_path($id, ST_FULL);  $filename = pw_wiki_path($id, FNAME);
  $fntext = pw_url2e($id);

  $isns = pw_wiki_isns($id);

  if (isset($_POST['rename'])) {

    $target = $_POST['target'];    $targetid = pw_wiki_s2id($target);
    $targetfn = $target;

    if ($isns) {      $targetid = utf8_rtrim($targetid, ':');      $targetid = pw_wiki_pg($targetid).':';      $targetid = pw_wiki_ns($id.":..:".$targetid);      $targetfn = pw_wiki_path($targetid, ST_SHORT);    } else {      $targetfn = pw_wiki_path($targetid, FNAME);      $targetfn = pw_wiki_path($id, ST_SHORT).$targetfn;    }
    $targettext = pw_s2e($target);

    $typetxt = $isns ? "Der Namensraum" : "Die Seite";

    if (file_exists($targetfn)) {      return pw_ui_getDialogInfo("Umbenennen", $typetxt." '$fntext' existiert bereits.", "id=$id&mode=$MODE");
    }

    if (!rename($fullfilename, $targetfn)) {      #out(substr(decoct(fileperms($fullfilename)), 1));      return pw_ui_getDialogInfo("Umbenennen", "Fehler beim Umbenennen <br />$fntext<br />nach<br />$targettext.", "id=$id&mode=$MODE&dialog=");
    }

    $newid = pw_wiki_path2id($targetfn);    return pw_ui_getDialogInfo("Umbenennen", $typetxt." wurde nach <tt>$targettext</tt> umbenannt.", "id=$newid&mode=$MODE");
  }

  $entries  = pw_n("<input type='hidden' name='mode' value='editpage' />");  #$entries .= pw_n("<input type='hidden' name='id' value='$id' />");  $entries .= pw_n("<input type='hidden' name='oldmode' value='$MODE' />");  $entries .= pw_n("<input type='hidden' name='olddialog' value='rename' />");  $typetxt = $isns ? "Den Namensraum" : "Die Seite";  $entries .= pw_n($typetxt." <tt>$fntext</tt> umbenennen...<br />");  $entries .= pw_n("<input type='text' class='textinput' autocomplete='off' name='target' id='target' value='' />");
  return pw_ui_getDialogQuestion("Umbenennen", $entries, "rename", "Umbenennen", "id=$id&mode=$MODE");

}

function pw_wiki_movepage ($id) {
  global $MODE;

  if (!isset($_SESSION["pw_wiki"]["login"]["user"])) {    return pw_ui_getDialogInfo("Verschieben", "Sie sind nicht berechtigt eine Seite zu verschieben...", "id=$id&mode=$MODE");
  }

  $id = pw_wiki_ns($id).pw_wiki_pg($id);
  $isns = pw_wiki_isns($id);  $fullfilename = pw_wiki_path($id, ST_FULL);  $filename = pw_wiki_path($id, FNAME);

  $fntext = pw_url2e($id);

  if (isset($_POST["move"]) || isset($_POST["overwrite"])) {

    $target = $_POST['target'];    $targetid = pw_wiki_s2id($target);    $targetfn = pw_wiki_path($targetid, ST_NOEXT);
    $targettext = pw_s2e($target);

    if (!file_exists($fullfilename)) {      return pw_ui_getDialogInfo("Verschieben", "Die Datei '$fntext' existiert nicht.", "id=$id&mode=$MODE");
    }

    if (!is_dir($targetfn)) {      if (!isset($_POST['createfolder'])) {        return pw_ui_getDialogInfo("Verschieben", "Das Zielverzeichnis '$targettext' existiert nicht.", "id=$id&mode=$MODE");
      }

      if(!mkdir($targetfn, 0777, true)) {        return pw_ui_getDialogInfo("Verschieben", "Das Erstellen des Zielverzeichnisses '$targettext' schlug fehl.", "id=$id&mode=$MODE");
      }

    }

    if (!isset($_POST["overwrite"])) {      $targetfn = $targetfn."/".$filename;      if (file_exists($targetfn) && !$isns) {        $entries = pw_n("<input type='hidden' name='target' value='$target' />");        return pw_ui_getDialogQuestion("Verschieben", $entries."Die Zieldatei '$targetid:$filename' existiert bereits.<br />Soll sie überschrieben werden?", "overwrite", "Ja", "id=$id&mode=$MODE");      }    } else {      $targetfn = pw_wiki_path($_POST['target'], ST_NOEXT)."/".$filename;      if ($fullfilename != $targetfn) {        if (!unlink($targetfn)) {          return pw_ui_getDialogInfo("Verschieben", "Fehler beim Verschieben der Datei<br />$fntext<br />nach<br />$targettext.<br />Die existierende Zieldatei konnte nicht gelöscht werden.", "id=$id&mode=$MODE");        }
      }

      #return pw_ui_getDialogInfo("Verschieben", "OVERWRITE: $id; $t", "id=$id&mode=$MODE");
    }

    if ($fullfilename == $targetfn) {      return pw_ui_getDialogInfo("Verschieben", "Fehler beim Verschieben...<br />Die Quell- und Zieldateien sind identisch.", "id=$id&mode=$MODE");
    }

    $t = "";
    if ($isns) {
      $t = pw_wiki_path($id, DNAME);
    }

    if (!rename($fullfilename, $targetfn.$t)) {      return pw_ui_getDialogInfo("Verschieben", "Fehler beim Verschieben der Datei<br />$fntext<br />nach<br />$targettext.", "id=$id&mode=$MODE&dialog=");
    }

    $newid = pw_s2url(pw_wiki_path2id($targetfn));

    return pw_ui_getDialogInfo("Verschieben", "Die Datei wurde nach <tt>$targettext</tt> verschoben.", "id=$newid&mode=$MODE");

  }

  $entries  = pw_n("<input type='hidden' name='mode' value='editpage' />");  $entries .= pw_n("<input type='hidden' name='oldmode' value='$MODE' />");  $entries .= pw_n("<input type='hidden' name='olddialog' value='movepage' />");  if ($isns) {
    $entries .= pw_n("Den Namensraum <tt>$fntext</tt> verschieben nach...<br />");
  } else {
    $entries .= pw_n("Die Seite <tt>$fntext</tt> verschieben nach...<br />");  }
  $entries .= pw_n("<!--label for='id'>Ziel:</label--> <input type='text' class='textinput' autocomplete='off' name='target' id='target' value='' />");  $entries .= pw_n("<br /><input type='checkbox' id='createfolder' name='createfolder' checked='checked' /><label for='createfolder'>Verzeichnisse anlegen</label>");  #$entries .= "<div id='autoc' name='autoc' style='position: relative; display: block; top: -20px; width: 500px; border: 3px solid gray'></div>";  #$entries .= "<script type=\"text/javascript\">document.observe('dom:loaded', function() {new Ajax.Autocompleter('target', 'autoc', 'bin/getfilelist.php')});</script>";  return pw_ui_getDialogQuestion("Verschieben", $entries, "move", "Verschieben", "id=$id&mode=$MODE");}
function pw_wiki_delpage ($id) {  //@TODO: clean the code...  // --- rrmdir is not needed because pw_wiki_delnamespaces does the job!  global $MODE;  if (!isset($_SESSION["pw_wiki"]["login"]["user"]) or pw_wiki_ns($id) == "tpl:") {    return pw_ui_getDialogInfo("L&ouml;schen", "Sie sind nicht berechtigt diese Seite oder diesen Namensraum zu l&ouml;schen...", "id=$id&mode=$MODE");  }  $filename = pw_wiki_path($id, ST_FULL);  out($filename);  $fntext = pw_url2e($id);  if (isset($_POST["del"])) {    //@TODO: file_exists -> direxists??? redundant?    if (!file_exists($filename)) {      return pw_ui_getDialogInfo("L&ouml;schen", "Die Seite '$fntext' existiert nicht.", "id=$id&mode=$MODE");    }    if (is_file($filename)) {      if (!unlink($filename)) {        return pw_ui_getDialogInfo("L&ouml;schen", "Die Seite '$fntext' konnte nicht gel&ouml;scht werden.", "id=$id&mode=$MODE");      }      $dir = pw_wiki_path($id, ST_SHORT);      $oldid = $id;
      $id = pw_wiki_delnamespaces($dir);      $outdelns = "";
      if ($id == "") {
        $id = $oldid;
      } else {
        $outdelns = "Der Namensraum '$id' ist leer. Er wird entfernt.<hr />";      }
      //@TODO: getlastvalid namespace id      if (pw_wiki_isns($id)) {        //out($id);
        $id = substr($id,0,strlen($id)-1);      }      $newid = pw_wiki_ns($id."..");      return pw_ui_getDialogInfo("L&ouml;schen", $outdelns."Die Seite '$fntext' wurde gel&ouml;scht.", "id=".$newid."&mode=$MODE");    }    if (!is_dir($filename)) {      return pw_ui_getDialogInfo("L&ouml;schen", "Der Namensraum '$fntext' existiert nicht.", "id=$id&mode=$MODE");    }    if (!rrmdir($filename)) {      return pw_ui_getDialogInfo("L&ouml;schen", "Der Namensraum '$fntext' konnte nicht gel&ouml;scht werden.", "id=$id&mode=$MODE");    }    //@TODO: put this in a common function...    if (pw_wiki_isns($id)) {      $id = substr($id,0,strlen($id)-1);    }    $newid = pw_wiki_ns($id."..");    $dir = pw_wiki_path($newid, ST_SHORT);    $oldid = $id;
    $id = pw_wiki_delnamespaces($dir);
    $outdelns = "";
    if ($id == "") {
      $id = $oldid;
    } else {
      $outdelns = "Der Namensraum '$id' ist leer. Er wird entfernt.<hr />";
    }


    if (pw_wiki_isns($id)) {      $id = substr($id,0,strlen($id)-1);    }    $newid = pw_wiki_ns($id."..");    return pw_ui_getDialogInfo("L&ouml;schen", $outdelns."Der Namensraum '$fntext' wurde gel&ouml;scht.", "id=".$newid."&mode=$MODE");  }  $type = "Die Seite";  if (pw_wiki_isns($id)) {    $type = "Den Namensraum";  }  return pw_ui_getDialogQuestion("L&ouml;schen", "$type '$fntext' l&ouml;schen?", "del", "L&ouml;schen", "id=$id&mode=$MODE");}
function pw_wiki_delnamespaces($dir) {
  if (!isset($_SESSION["pw_wiki"]["login"]["user"]))    return false;  if ($dir == pw_wiki_getcfg('storage')) {    return;  }  #out ($dir);  $dir = str_replace("//", "/", $dir);  $dir = str_replace("\\\\", "\\", $dir);  #out2($dir);
  $dirnames = explode("/", $dir);  #out($dirnames);  $dirar = array();  $dn = "";  foreach ($dirnames as $dirname) {    if ($dirname != "") {      $dn .= $dirname."/";      $dirar[] = $dn;    }  }  $dirar = array_reverse($dirar);  #out($dirar);  #die();  $dirtxt = "";  foreach ($dirar as $dirname) {    if (@rmdir($dirname)) {      $dirtxt = pw_wiki_path2id($dirname);      $dirtxt = pw_s2e($dirtxt);      #$dirtxt = pw_wiki_entities(pw_wiki_urldecode($dirtxt));      #$dirtxt = "Der Namensraum '$dirtxt' ist leer. Er wird entfernt.<hr />";    } else {      break;    }  }  return $dirtxt;}function pw_wiki_update_cache() {	$storage = pw_wiki_getcfg('storage');	if (!is_dir($storage)) {
		throw new Exception("Folder '$storage' does not exist!");
	}		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($storage));
	foreach($files as $filename) {		if(substr($filename, -4) == ".txt") {			$filename = str_replace("\\", "/", $filename);			try {				pw_wiki_create_cached_page(pw_wiki_path2id($filename));			} catch (Exception $e) {				echo "Exception: Skipping file '$filename'<br /> ";				echo $e;			}		}
	}}function pw_wiki_create_cached_page($id) {	$filename = pw_wiki_path($id, ST_FULL);	$headerFilename = pw_wiki_path("tpl:header", ST_FULL);	$footerFilename = pw_wiki_path("tpl:footer", ST_FULL);	$cachedFilename = "home/".pw_wiki_path($id, NOEXT).".html";
		if (!is_file($filename)) {		throw new Exception("File '$filename' does not exist!");
	}	if (!is_file($headerFilename)) {
		throw new Exception("File '$headerFilename' does not exist!");
	}	if (!is_file($footerFilename)) {
		throw new Exception("File '$footerFilename' does not exist!");
	}		// If the cached file is still up-to-date do nothing!	if(is_file($cachedFilename)) {		$cachedMTime = filemtime($cachedFilename);		if($cachedMTime >= filemtime($filename) && $cachedMTime >= filemtime($headerFilename) && $cachedMTime >= filemtime($footerFilename)) {			return;		}	}		$data = file_get_contents($filename);	if ($data === false) {
		throw new Exception("Unable to read data file '$filename'!");
	}
	$headerData = file_get_contents($headerFilename);
	if ($headerData === false) {
		throw new Exception("Unable to read template file '$headerFilename'!");
	}
	$footerData = file_get_contents($footerFilename);
	if ($footerFilename === false) {
		throw new Exception("Unable to read template file '$footerFilename'!");
	}		FileTools::createFolderIfNotExist(dirname($cachedFilename));	$data = pw_wiki_LE_unix($data);	$headerData = pw_wiki_LE_unix($headerData);	$footerData = pw_wiki_LE_unix($footerData);
	$data = $headerData."\n".$data."\n".$footerData;
	
	if (!utf8_check($data)) {
		throw new Exception("File '$filename' is not an UTF8-encoded file!");
	}
	
	$headerLineCount = count(explode("\n", $headerData));
	$footerLineCount = count(explode("\n", $footerData));
	
	$out = lexerconf($data, $headerLineCount, $footerLineCount);		if (file_put_contents($cachedFilename, $out) === false) {		throw new Exception("Unable to write file '$cachedFilename'!");	}	}
function pw_wiki_showcontent($id) {
  //@TODO: Errorhandling!!!  $filename = pw_wiki_path($id, ST_FULL);//   out($filename);  $header = file_get_contents(pw_wiki_path("tpl:header", ST_FULL));
  $footer = file_get_contents(pw_wiki_path("tpl:footer", ST_FULL));

  #if (!file_exists($filename) or !is_file($filename)) {  #  return false;  #}    $filenameCached = "home/".pw_wiki_path($id, NOEXT).".html";//   out($filenameCached);  if(file_exists($filenameCached) && filemtime($filenameCached) >= filemtime($filename)) {//   	out("Using cached file!");  	  return file_get_contents($filenameCached);  }
  $data = file_get_contents($filename);
  
  $data = pw_wiki_LE_unix($data);
  $data = $header."\n".$data."\n".$footer;

  if (! utf8_check($data)) {    $filename2 = pw_wiki_path("tpl:noutf8", ST_FULL);    $data = file_get_contents($filename2);    if (! utf8_check($data)) {      die("FATAL ERROR: '$filename' isn't a UTF8-encoded file!!! Fallback failed...");    }
  }

  $hdlen = count(explode("\n", $header));
  $ftlen = count(explode("\n", $footer));

  $out = lexerconf($data, $hdlen, $ftlen);

  return $out;
}


$user = "root";
$pwd = "qwertz";

function pw_wiki_login($id) {  global $user;  global $pwd;
  global $MODE;

  if (isset($_POST["login"])) {    $login = $_POST["username"];
    $pass = $_POST["password"];

    if ($user == $login and $pass == $pwd) {      $_SESSION["pw_wiki"]['login']["user"] = $login;      return pw_ui_getDialogInfo("Login", "Benutzerlogin erfolgreich...", "id=$id&mode=$MODE");    } else {      unset($_SESSION["pw_wiki"]['login']["user"]);      return pw_ui_getDialogInfo("Login", "Benutzerlogin fehlgeschlagen...", "id=$id&mode=$MODE");    }
  }

  if (isset($_POST["logout"])) {    unset($_SESSION["pw_wiki"]['login']["user"]);    session_destroy();    return pw_ui_getDialogInfo("Logout", "Sie sind nun abgemeldet...", "id=$id&mode=$MODE");
  }

  if (isset($_SESSION["pw_wiki"]['login']["user"])) {    return pw_ui_getDialogQuestion("Logout", "Wollen Sie sich abmelden?", "logout", "Ja", "id=$id&mode=$MODE");
  }

  $entries = "<label for='username'>Benutzer: </label><input type='text' class='textinput' name='username' /><br />";  $entries .= "<label for='password'>Passwort: </label><input type='password' class='textinput' name='password' />";  return pw_ui_getDialogQuestion("Login", $entries, "login", "OK", "id=$id&mode=$MODE");
}

function pw_wiki_getfilelist($id = null) {  #var_dump($id);
  #var_dump(pw_wiki_getcfg());

  $ns = pw_wiki_ns($id);
  $path = pw_wiki_path($ns, ST_NOEXT);

  $strout = "";  $files = array();  $dirs = array();  if($ns) {    $dirs[] = array('NAME' => '..', 'TYPE' => 'DIR');
  }

  $p = "../".rtrim($path, "/")."/";
  $data = glob ($p."*");

  #var_dump($data);

  if (!$data) {    return null;
  }

  foreach ($data as $k => $i) {    $i = pw_s2u($i);    $i = utf8_strtolower($i);
    $i = pw_u2t($i);

    if (is_dir($i)) {      $dirs[] = array('NAME' => pw_basename($i), 'TYPE' => "DIR");    } else {      $files[] = array('NAME' => pw_basename($i, ".txt"), 'TYPE' => "TEXT", 'SIZE' => filesize($i));
    }

  }

  if ($dirs) sort($dirs);
  if ($files) sort($files);

  $out = array_merge($dirs, $files);

  return $out;

}

function pw_wiki_showpages($id = null) {  global $MODE;  #if (!isset($_SESSION["pw_wiki"]["login"]["user"]))
  #  return false;

  #out($id);  $ns = pw_wiki_ns($id);
  $path = pw_wiki_path($ns, ST_NOEXT);

  $strout = "";  $files = array();  $dirs = array();  if($ns) {    $dirs[] = array('NAME' => '..', 'TYPE' => 'DIR');  }
  $data = glob ("$path/*");

  #out($path);
  #out($data);

  // Leeres Verz. gefunden... Löschen!  if (!$data) {    if ($path != pw_wiki_getcfg('storage') and @rmdir($path)) {      $strout .= "<tt>INFO: $path ist leer und wird entfernt!</tt>";    }  } else {    foreach ($data as $k => $i) {
      #pw_debug($i, "ANFANG");

      $i = pw_s2u($i);      #pw_debug($i);
      #pw_debug(utf8_strtolower($i));

      if ($i != utf8_strtolower($i)) {        rename(pw_u2t($i), pw_u2t(utf8_strtolower($i)));        // @TODO: falls neue Datei bereits existiert ??? Fehler melden... Benutzereingabe fordern!      }
      $i = utf8_strtolower($i);
      $i = pw_u2t($i);

      if (is_dir($i)) {        $dirs[] = array('NAME' => pw_basename($i), 'TYPE' => "DIR");      } else {        $files[] = array('NAME' => pw_basename($i, ".txt"), 'TYPE' => "TEXT", 'SIZE' => filesize($i));
      }

    }
  }

  if ($dirs) sort($dirs);
  if ($files) sort($files);

  $out = array_merge($dirs, $files);  $strout .= "<h1>Seiten&uuml;berblick</h1>";  $strout .= "Sie sind hier: ".pw_wiki_trace($ns)."";  $strout .= "<table id='overview'><tr><th style='width:15px'>#</th><th style='width: 380px'>ID (Vorschau)</th><th style='width: 70px'>Gr&ouml;&szlig;e</th><th style='width: 60px'>Typ</th><th>Optionen</th></tr>";  $nr = 0;  $ns = pw_wiki_ns($ns);
  foreach ($out as $k => $i) {

    $strout .= "<tr style='background: black'>";    $strout .= "<td style='text-align: right'>".($nr++)."</td>";    if ($i['TYPE'] == "TEXT") {      $strout .= "<td>";      #$strout .= pw_wiki_encode(htmlentities($i['NAME']), false);      $strout .= pw_s2e($i['NAME']);      $strout .= "<a style='float: right' href='?id=".pw_s2url($ns.$i['NAME'])."&mode=cleared'>&laquo; anzeigen</a>";      $strout .= "</td>";    } else {      $strout .= "<td><a href='?id=".pw_s2url(pw_wiki_ns($ns.$i['NAME'].':'))."&mode=showpages'>".pw_s2e($i['NAME'])."</a></td>";    }    $strout .= "<td style='text-align: right'>";    if ($i['TYPE'] == "TEXT") {      $strout .= "<tt>".pw_formatbytes($i['SIZE'], 2, false)."</tt>";    } else {      $strout .= "<tt>-</tt>";    }    $strout .= "<td>".$i['TYPE']."</td>";
    $strout .= "<td>";

    if ($i['TYPE'] == "TEXT") {      if (isset($_SESSION["pw_wiki"]["login"]["user"])) {        $strout .= "<a href='?id=".pw_s2url($ns.$i['NAME'])."&mode=editpage&oldmode=showpages'>Bearbeiten</a> | ";        $strout .= "<a href='?id=".pw_s2url($ns.$i['NAME'])."&mode=showpages&dialog=delpage'>L&ouml;schen</a> | ";        $strout .= "<a href='?id=".pw_s2url($ns.$i['NAME'])."&mode=showpages&dialog=rename'>Umbenennen</a> | ";        $strout .= "<a href='?id=".pw_s2url($ns.$i['NAME'])."&mode=showpages&dialog=movepage'>Verschieben</a>";        #$strout .= "[<a href='?id=".$ns.$i['NAME']."&mode=showpages&dialog=info'>Info</a>]";      } else {        $strout .= "<a href='?id=".pw_s2url($ns.$i['NAME'])."&mode=showsource&oldmode=showpages'>Quelltext anzeigen</a>";      }    } else {      if ($i['NAME'] != '..') {        if (isset($_SESSION["pw_wiki"]["login"]["user"])) {          $strout .= "<a href='?id=".pw_s2url($ns.$i['NAME'].":")."&mode=showpages&dialog=delpage'>L&ouml;schen</a> | ";          $strout .= "<a href='?id=".pw_s2url($ns.$i['NAME'].":")."&mode=showpages&dialog=rename'>Umbenennen</a> | ";          $strout .= "<a href='?id=".pw_s2url($ns.$i['NAME'].":")."&mode=showpages&dialog=movepage'>Verschieben</a>";        }      }    }    $strout .= "</td>";    $strout .= "</tr>";
  }

  $strout .= "</table>";  return $strout;
}

function rrmdir($dir) {   if (is_dir($dir)) {     $objects = scandir($dir);     foreach ($objects as $object) {       if ($object != "." && $object != "..") {         if (filetype($dir."/".$object) == "dir") {           rrmdir($dir."/".$object);         } else {           if (!unlink($dir."/".$object)) {             return false;           }         }       }     }     reset($objects);     if (!rmdir($dir)) {       return false;     }   }   return true;
 }

?>