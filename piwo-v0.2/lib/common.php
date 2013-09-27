<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'pwTools/string/encoding.php';
require_once INC_PATH.'pwTools/string/StringTools.php';
require_once INC_PATH.'pwTools/debug/TestingTools.php';
require_once INC_PATH.'piwo-v0.2/lib/WikiID.php';

function pw_wiki_getid() {
	$id = isset($_GET['id']) && $_GET['id'] != "" ? $_GET['id'] : WIKISTARTPAGE;
	return new WikiID($id);
}

function pw_wiki_getmode() {
	return isset($_GET['mode']) ? $_GET['mode'] : null;
}

function pw_wiki_getcfg($what = "", $subcat = "") {
	
	if (!is_array($_SESSION['pw_wiki'])) {
		throw new Exception("Session pw_wiki is not an ARRAY!");
	}

	if ($what == "")
		return $_SESSION['pw_wiki'];

	if (!array_key_exists($what, $_SESSION['pw_wiki'])) {
		throw new Exception("Session pw_wiki has no category '$what'!");
	}

	if ($subcat == "")
		return $_SESSION['pw_wiki'][$what];

	if (!array_key_exists($subcat, $_SESSION['pw_wiki'][$what])) {
		throw new Exception("Session pw_wiki has no sub-category '$subcat' within '$what'!");
	}

	return $_SESSION['pw_wiki'][$what][$subcat];
}

function pw_wiki_setcfg($key, $value) {
	$_SESSION['pw_wiki'][$key] = $value;
}

function pw_wiki_unsetcfg($key = "") {
	if($key == "") {
		unset($_SESSION['pw_wiki']);
	} else {
		unset($_SESSION['pw_wiki'][$key]);
	}
}

function pw_wiki_loadconfig() {
	
	ini_set('auto_detect_line_endings', false);
	
	if (! isset($_SESSION['pw_wiki'])) {
		global $WIKIDEFAULTCONFIG;
		$config  = $WIKIDEFAULTCONFIG;
		if (isset($_SESSION['pw_wiki']) && is_array($_SESSION['pw_wiki'])) {
			$_SESSION['pw_wiki'] = array_merge($_SESSION['pw_wiki'], $config);
		} else {
			$_SESSION['pw_wiki'] = $config;
		}
	}
}

function pw_wiki_getmenu($id, $mode, Collection $modules) {
	$loginData = pw_wiki_getcfg('login');

	$o = "";
	foreach ($modules->getArray() as $module) {
		if($module instanceof MenuItemProvider && $module->getMenuAvailability()) {
			if(!($module instanceof PermissionProvider) || $module instanceof PermissionProvider && $module->permissionGranted()) {
				$o .= GuiTools::textButton($module->getMenuText(), "id=".$id->getID()."&mode=".$module->getName());
				$o .= " | ";
			}
		}
	}

	return $o;
}

function pw_checkfilename($name) {
	if (strpos($name, "*") or strpos($name, "\\") or strpos($name, "?")) {
		return false;
	}
	return true;
}

function pw_dirname($dn, $single = false) {
	$dn = pw_s2u($dn);

	if (!pw_checkfilename($dn)) {
		return false;
	}
	//@TODO: BUG(?) if .. at the end! || utf8_substr($dn, -3) == '/..'
	$isdir = (utf8_substr($dn, -1) == '/') ? true : false;

	// Absolute paths start with '/'
	$remember_abs = (utf8_substr($dn, 0, 1) == '/') ? '/' : '';

	$dn = utf8_strtolower($dn);
	$dn = str_replace('\\', '/', $dn);

	$xpf = explode("/", $dn);

	$bn = array_pop($xpf);

	$dn = array();
	foreach ($xpf as $i => $f) {
		if ($f != ".." and $f != "." and $f != "") {
			$dn[] = $f;
		}
		if (isset($xpf[$i+1]) and $xpf[$i+1] == "..") {
			array_pop($dn);
		}
	}

	if ($bn == "..") {
		array_pop($dn);
	}


	$dn = implode($dn, "/");
	if (utf8_strlen($dn) > 0) {
		$dn .= '/';
	}

	if ($isdir) {
		if ($bn == "..") $bn = "";
		$dn = $dn.$bn;
	}

	$out = str_replace('//', '/', $remember_abs.utf8_rtrim($dn, '/').'/');

	// Return only the inner directory...
	if ($single) {
		$dirs = explode('/', $out);
		array_pop($dirs);
		$out = array_pop($dirs);
	}

	#out($out." >>> ".$remember_abs);

	if ($out == '/' and !$isdir) {
		$out = "";
	}
	return $out;
}

function pw_basename($fn, $ext = null) {

	$fn = pw_s2u($fn);

	if (!pw_checkfilename($fn)) {
		return false;
	}

	#$isdir = (utf8_substr($dn, -1) == '/') ? true : false;

	$fn = explode("/", $fn);

	$fn = array_pop($fn);

	if ($ext) {
		$fn = StringTools::rightTrim($fn, $ext);
	}
	return $fn;
}

function pw_wiki_file2editor($data) {
	$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));
	$data = pw_s2e($data);
	return $data;
}

function pw_wiki_path2id($path) {

	$id = $path;
	#$id = utf8_strtolower($path);

	$ida = explode('/', $id);
	if ($ida[0] == WIKISTORAGE) {
		$ida = array_slice($ida, 1, sizeof($ida));
	}

	$id = implode('/', $ida);
	$id = str_replace("/", ":", $id);
	$id = ltrim($id, ":");

	$id = StringTools::rightTrim($id, WIKIFILEEXT);
	$id = pw_s2url($id);
	return $id;
}

function pw_wiki_getfulltitle($sep = "&raquo;", $showuser = true) {
	$sep = ' '.$sep.' ';

	$title = pw_url2u(pw_wiki_getcfg('wikititle'));
	$title = utf8_ucfirst($title);
	$title = pw_s2e($title);

	//$ns = pw_url2u(pw_wiki_getcfg('ns'));
	$id = pw_wiki_getid();
	$ns = $id->getNS();
	if ($ns) {
		$ns = utf8_ucfirst($ns);
		$ns = pw_s2e($ns);
		$title .= $sep.$ns;
	}

	$mode = pw_wiki_getmode();

	if ($mode == 'showpages') {
		$title .= " [Seiten&uuml;berblick]";
	} else {
		//$pg = pw_url2u(pw_wiki_getcfg('pg'));
		$pg = $id->getPage();
		$pg = utf8_ucfirst($pg);
		$pg = pw_s2e($pg);
		$title .= $sep.$pg;
		if ($mode == 'editpage') {
			$title .= " [Seite bearbeiten]";
		}
	}

/*
	$user = pw_wiki_getcfg('user');
	out($user);
	if (pw_wiki_getcfg('showuser') and ($user)) {
		$user = pw_wiki_encode($user);
		$title .= " [".$user."]";
	}
*/
	return $title;
}


function pw_wiki_fileinfo($subcat) {
	switch($subcat) {
		case 'type':
			// TODO use FileTools to get this file information...
			$o = $_SESSION['pw_wiki']['file']['format'];
		break;
	}

	return $o;
}





function pw_wiki_trace($ns, $sep = "&raquo;") {

	$ns = pw_url2t($ns);
	$sep = ' '.$sep.' ';

	$ns = preg_split("#:#", $ns, null, PREG_SPLIT_NO_EMPTY);
	$o = "<a href='?mode=showpages&id='>Home</a>";

	$p = "";
	foreach ($ns as $i) {
		$p .= pw_s2url($i).":";
		$i = pw_s2e(utf8_trim($i));
		$i = utf8_ucfirst($i);
		$o .= $sep."<a href='?mode=showpages&id=".$p."'>".$i."</a>";
	}

	return $o;

}

function pw_wiki_syntaxerr($text, $line, $errtxt, $header = 0, $footer = 0) {
	$texp = explode("\n", $text);
	array_pop($texp);
	array_shift($texp);

	$footer = count($texp)-$footer+1;

	$out = "";
	$lnr = 0;
	foreach ($texp as $ltxt) {
		$lnr++;
		if ($lnr == $line) {
			$out .= sprintf("<span style='color: red'>%6s| %s</span><span class='syntaxerror'>&lt; %s</span>\n", $lnr, htmlentities($ltxt), $errtxt);
		} else {
			if ($lnr <= $header or $lnr >= $footer) {
				$out .= sprintf("<span style='color: gray'>%6s| %s</span>\n", $lnr, htmlentities($ltxt));
			} else {
				$out .= sprintf("%6s| %s\n", $lnr, htmlentities($ltxt));
			}

		}
	}
	return $out;
}

function nop($txt) {
	$out = "<span style='color: yellow'>[WARNUNG: ";
	$out .= $txt;
	$out .= "]</span>";

	return $out;

}


?>