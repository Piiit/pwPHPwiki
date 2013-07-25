<?php

require_once INC_PATH.'pwLexer/pw_debug.php';

function pw_wiki_version() {
	global $piwo_version;
	return $piwo_version;
}

function pw_wiki_getid() {
	$id = isset($_GET['id']) ? $_GET['id'] : pw_url2u(pw_wiki_getcfg('id'));
	if (!$id) {
		$id = pw_url2u(pw_wiki_getcfg('startpage'));
	}

	$id = pw_wiki_s2id($id);

	return $id;
}


function pw_wiki_setcfg($id, $mode) {

	$id = pw_s2u($id);

	if (! pw_wiki_isvalidid($id)) {
		$_SESSION['pw_wiki']['wrongid'] = $id;
		return false;
	}

	$id = utf8_strtolower($id);

	$fullpath = pw_wiki_path($id, ST_FULL);
	$path = pw_wiki_path($id, ST_SHORT);
	$path2 = pw_wiki_path($id, SHORT);

	$fullns = pw_wiki_ns($id);
	$pg = pw_wiki_pg($id);

	$ns = explode(':', rtrim($fullns, ':'));
	if (strlen($ns[0]) > 0) {
		$ns = array_pop($ns);
		$id = $fullns.$pg;
	} else {
		$ns = "";
		$id = $pg;
	}

	$fullns = pw_s2url($fullns);
	$ns = pw_s2url($ns);
	$id = pw_s2url($id);
	$pg = pw_s2url($pg);

	$_SESSION['pw_wiki']['mode'] = $mode;
	$_SESSION['pw_wiki']['fullpath'] = $fullpath;
	$_SESSION['pw_wiki']['path'] = $path;
	$_SESSION['pw_wiki']['path2'] = $path2;
	$_SESSION['pw_wiki']['fullns'] = $fullns;
	$_SESSION['pw_wiki']['ns'] = $ns;
	$_SESSION['pw_wiki']['id'] = $id;
	$_SESSION['pw_wiki']['pg'] = $pg;

	#return $fullid;
	return true;

}

function pw_wiki_getcfg($what = "", $subcat = "") {
	if (!isset($_SESSION) || !isset($_SESSION['pw_wiki']))
		return false;

	if (!is_array($_SESSION['pw_wiki']))
		return false;

	if ($what == "")
		return $_SESSION['pw_wiki'];

	if (!array_key_exists($what, $_SESSION['pw_wiki']))
		return false;

	if ($subcat == "")
		return $_SESSION['pw_wiki'][$what];

	if (!array_key_exists($subcat, $_SESSION['pw_wiki'][$what]))
		return false;

	return $_SESSION['pw_wiki'][$what][$subcat];
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
	StringFormat::htmlIndentormalizeLE($data);
	$data = pw_s2e($data);
	return $data;
}

function pw_wiki_path2id($path) {

	$id = $path;
	#$id = utf8_strtolower($path);

	$ida = explode('/', $id);
	if ($ida[0] == pw_wiki_getcfg('storage')) {
		$ida = array_slice($ida, 1, sizeof($ida));
	}

	$id = implode('/', $ida);


	$id = str_replace("/", ":", $id);

	$id = ltrim($id, ":");

	$id = StringTools::rightTrim($id, pw_wiki_getcfg('fileext'));
	$id = pw_s2url($id);
	return $id;
}

define ('ST_FULL', 0);    // Pfad mit Storageangabe, mit Dateinamen, mit Erweiterung
define ('ST_SHORT', 1);   // Pfad mit Storageangabe, ohne Dateinamen, ohne Erweiterung
define ('ST_NOEXT', 2);   // Pfad mit Storageangabe, mit Dateinamen, ohne Erweiterung
define ('FULL', 3);       // Pfad ohne Storageangabe, mit Dateinamen, mit Erweiterung
define ('SHORT', 4);      // Pfad ohne Storageangabe, ohne Dateinamen, ohne Erweiterung
define ('NOEXT', 5);      // Pfad ohne Storageangabe, mit Dateinamen, ohne Erweiterung
define ('FNAME', 6);      // Nur Dateiname mit Erweiterung
define ('FNAME_NOEXT', 7);// Nur Dateiname ohne Erweiterung
define ('DNAME', 8);      // Innerstes Verzeichnis

function pw_wiki_path($id, $type = SHORT) {
	$id = pw_url2u($id);
	$isdir = pw_wiki_isns($id);
	$id = utf8_strtolower($id);
	$pg = pw_wiki_pg($id);
	$id = str_replace(":", "/", $id);
	$storage = utf8_rtrim(pw_wiki_getcfg('storage'), '/').'/';

	if (!pw_checkfilename($id) or !pw_checkfilename($storage)) {
		return false;
	}


	$ext = "";
	if (!$isdir) {
		$ext = pw_wiki_getcfg('fileext');
	}

	switch ($type) {
		case ST_FULL:     $out = $storage.pw_dirname($id).$pg.$ext; break;
		case ST_SHORT:    $out = $storage.pw_dirname($id); break;
		case ST_NOEXT:    $out = $storage.pw_dirname($id).$pg; break;
		case FULL:        $out = pw_dirname($id).$pg.$ext; break;
		case SHORT:       $out = pw_dirname($id) == '.' ? '' : pw_dirname($id); break;
		case NOEXT:       $out = pw_dirname($id).$pg; break;
		case FNAME_NOEXT: $out = $pg; break;
		case FNAME:       $out = $pg.$ext; break;
		case DNAME:       $out = pw_dirname($id, true); break;

	}

	$out = str_replace('//', '/', $out);

	return pw_u2t($out);
}

function pw_wiki_isns($id) {
	$id = pw_s2u($id);
	return utf8_substr($id, -1) == ':' ? true : false;
}

$html_level = 0;

define("START", 1);
define("END", 0);

function pw_n($type_txt="", $startend="", $newline = true, $spaces = true) {
	global $WIKI_GLOBALS;

	if (is_numeric($type_txt)) {
		$GLOBALS['html_level'] += $type_txt;
		return "";
	}

	$startend = strtolower($startend);

	if ($startend == END) $GLOBALS['html_level']--;

	if ($spaces) {
		$spaces = "";
		for ($i = 0; $i < $GLOBALS['html_level']; $i++)
			$spaces .= "  ";
	}

	if ($newline)
		$newline = "\n";

	if ($startend == START) $GLOBALS['html_level']++;

	if (@array_key_exists($type_txt, $WIKI_GLOBALS[tags])) {
		if (is_array($WIKI_GLOBALS[tags][$type_txt]))
			return $spaces.$WIKI_GLOBALS[tags][$type_txt][$startend].$newline;
		return $spaces.$WIKI_GLOBALS[tags][$type_txt].$newline;
	}
	return $spaces.$type_txt.$newline;
}

function pw_ne($type_txt="", $startend="", $newline = true, $spaces = true) {
	echo pw_n($type_txt, $startend, $newline, $spaces);
}

function pw_wiki_getfulltitle($sep = "&raquo;", $showuser = true) {
	$sep = ' '.$sep.' ';

	$title = pw_url2u(pw_wiki_getcfg('wikititle'));
	$title = utf8_ucfirst($title);
	$title = pw_s2e($title);

	$ns = pw_url2u(pw_wiki_getcfg('ns'));
	if ($ns) {
		$ns = utf8_ucfirst($ns);
		$ns = pw_s2e($ns);
		$title .= $sep.$ns;
	}

	$mode = pw_wiki_getcfg('mode');

	if ($mode == 'showpages') {
		$title .= " [Seiten&uuml;berblick]";
	} else {
		$pg = pw_url2u(pw_wiki_getcfg('pg'));
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


function html_header() {
	global $MODE;
	StringFormat::htmlIndentPrint ('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
	StringFormat::htmlIndentPrint ('<html>', START);
	StringFormat::htmlIndentPrint ('<!-- HEADER start -->');
	StringFormat::htmlIndentPrint ('<head>', START);
	StringFormat::htmlIndentPrint ('<title>'.pw_wiki_getfulltitle().'</title>');
	StringFormat::htmlIndentPrint ('<meta name="description" content="'.pw_wiki_getcfg('description').'">');
	StringFormat::htmlIndentPrint ('<meta name="keywords" content="'.pw_wiki_getcfg('keywords').'">');
	StringFormat::htmlIndentPrint ('<meta http-equiv="Content-Type" content="text/html" charset="utf-8">');
	StringFormat::htmlIndentPrint ('<meta http-equiv="Content-Script-Type" content="text/javascript">');
	StringFormat::htmlIndentPrint ('<link rel="shortcut icon" href="media/favicon.ico" type="image/ico" />');
	StringFormat::htmlIndentPrint ('<link rel="stylesheet" type="text/css" media="screen" href="default.css">');
	StringFormat::htmlIndentPrint ('<link rel="stylesheet" type="text/css" media="screen" href="admin.css">');
	#StringFormat::htmlIndentPrint ('<script type="text/javascript" language="javascript" src="lib/js/scriptaculous/lib/prototype.js"></script>');
	#StringFormat::htmlIndentPrint ('<script type="text/javascript" language="javascript" src="lib/js/scriptaculous/src/scriptaculous.js"></script>');
	#StringFormat::htmlIndentPrint ('<script type="text/javascript" language="javascript" src="lib/js/pw_url.js"></script>');
	#StringFormat::htmlIndentPrint ('<script type="text/javascript" language="javascript" src="lib/js/pw_array.js"></script>');
	StringFormat::htmlIndentPrint ('<script type="text/javascript" language="javascript" src="lib/js/catchkeys.js"></script>'); // Editorkeys: catch TAB, insert Spaces
	#StringFormat::htmlIndentPrint ('<script type="text/javascript" language="javascript" src="lib/js/shortcut.js"></script>');
	#StringFormat::htmlIndentPrint ('<script type="text/javascript" language="javascript" src="lib/js/pw_ui.js"></script>');

	echo "<script>function setfocus() {
			var f = document.getElementsByTagName('input');

			if (f.length == 0) {
			f = document.getElementById('forminfo')
			if (!f) {
			f = document.getElementById('wikitxt')
			if (!f) {
			f = document.getElementById('submit')
}
}

			f.focus();
			return;
}

			for (var i = 0; i < f.length; i++) {
			if (f[i].type != 'hidden') {
			f[i].focus();
			return;
}
}
}

			</script>";

	if (pw_wiki_getcfg('debug')) {
		pw_debug_init(true);
	}

	StringFormat::htmlIndentPrint ('</head>', END);
	StringFormat::htmlIndentPrint ('<body onload="setfocus()">', START);
	StringFormat::htmlIndentPrint ('<!-- HEADER end -->');
	StringFormat::htmlIndentPrint ('<div id="INFO"></div>');
}

function html_footer($modal) {
	echo "</div>
			<div id='__bottom'>
			</div>
			</div>";

	if ($modal) {
		pw_ui_printDialogWrap();
	}

	StringFormat::htmlIndentPrint ('<!-- FOOTER start -->');
	StringFormat::htmlIndentPrint ('</body>', END);
	StringFormat::htmlIndentPrint ('</html>', END);
	StringFormat::htmlIndentPrint ('<!-- FOOTER end -->');
}

/**
 * SPECIAL UTILITIES FOR WIKI...
 */
function pw_wiki_s2id($id) {
	$id = pw_s2u($id);
	$id = pw_stripslashes($id);
	$id = pw_s2url($id);
	$id = utf8_strtolower($id);
	return $id;
}



function pw_wiki_fileinfo($subcat) {
	switch($subcat) {
		case 'type':
			$o = $_SESSION['pw_wiki']['file']['format'];
			break;
	}

	return $o;
}

function pw_wiki_pg($fullid) {
	$fullid = explode(":", $fullid);
	$id = array_pop($fullid);

	if (pw_wiki_isvalidid($id) and $id != ".." and $id != ".") {
		return pw_s2u($id);
	}

	return false;

}

function pw_wiki_ns($ns) {
	$ns = pw_s2u($ns);

	$ns = str_replace(":", "/", $ns);
	$ns = pw_dirname($ns);
	$ns = str_replace("/", ":", $ns);

	$ns = utf8_rtrim($ns, ':').':';
	return utf8_ltrim($ns, ':');
}

function pw_wiki_fullns($ns) {
	if (!isset($ns) || strlen($ns) == 0 || $ns == ":") {
		return pw_wiki_ns(pw_wiki_getcfg('fullns'));
	}

	if ($ns[0] == ':') {
		return pw_wiki_ns($ns);
	}

	return pw_wiki_ns(pw_wiki_getcfg('fullns').$ns);
}

function pw_wiki_isvalidid($fullid) {
	$fullid = pw_url2u($fullid);
	if (0 == preg_match('#[/?*;{}\\\]+#', $fullid)) {
		return true;
	}

	return false;
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

function out($node, $txt = null) {
	echo '<pre style="font-size: 12px; background-color: white;  color: black; border: 3px solid lightgreen; padding: 5px">';
	echo(pw_debug_get_info($txt)."\n");
	var_dump($node);
	echo '</pre>';
}

function out2($node, $txt = null) {
	echo '<pre style="font-size: 12px; background-color: white; color: black; border: 3px solid red; padding: 5px">';
	echo(pw_debug_get_info($txt)."\n");
	var_dump($node);
	echo '</pre>';
}


?>