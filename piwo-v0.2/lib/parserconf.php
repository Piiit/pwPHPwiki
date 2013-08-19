<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/pw_lexer.php';
require_once INC_PATH.'piwo-v0.2/lib/common.php';
require_once INC_PATH.'pwTools/string/encoding.php';
require_once INC_PATH.'pwTools/data/IndexTable.php';


function scomment() {
  return '<!--';
}
function ecomment() {
  return '-->';
}
function scomment2() {
  return '<!--';
}
function ecomment2() {
  return '-->';
}
function sdeflist() {
  return '<dl>';
}
function edeflist() {
  return '</dl>';
}
function sdefterm() {
  return '<dt>';
}
function edefterm() {
  return '</dt>';
}
function sdefitem() {
  return '<dd>';
}
function edefitem() {
  return '</dd>';
}
function sfootnote($node) {
  global $footnote;
  $GLOBALS['footnote']++;
  $GLOBALS['footnotes'][] = $node['ID'];
  $o = '<sup><a class="footnote" id="fnt__'.$footnote.'" href="#fn__'.$footnote.'">[';
  #echo '<acronym title="Keine Ahnung">';
  $o .= $footnote;
  #echo '</acronym>';
  $o .= ']';
  return $o;
}
function efootnote() {
  return '</a></sup>';
}

$listitems = array();

function slist($node, $lexer) {
  $GLOBALS['listitems'] = array();
  $fc = $lexer->firstChild($node);
  $listtype = $fc['CONFIG'][1] == "#" ? '<ol>' : '<ul>';
  $GLOBALS['listitems'][] = $fc['CONFIG'][1];
  #$listtype = htmlentities($listtype);
  return StringFormat::htmlIndent($listtype, StringFormat::START);
}

function slistitem($node, $lexer) {
  $o = "";

  $ps = $lexer->previousSibling($node);

  $oldlevel = 1;
  if ($ps != NULL) {
    $oldlevel = strlen($ps['CONFIG'][0]);
  }

  $thislevel = strlen($node['CONFIG'][0]);


  $thislt = $node['CONFIG'][1];
  $oldlt = $ps['CONFIG'][1];

  if ($oldlevel < $thislevel) {
    $difflevel = $thislevel - $oldlevel;

    for ($i = 0; $i < $difflevel; $i++) {
      $listtype = $node['CONFIG'][1] == "#" ? '<ol>' : '<ul>';
      $GLOBALS['listitems'][] = $node['CONFIG'][1];
      $o .= $listtype;
    }
  } elseif ($thislt != $oldlt) {

      $listtype = array_pop($GLOBALS['listitems']);
      $listtype = $listtype == "#" ? '</ol>' : '</ul>';
      $o .= $listtype;

      $listtype = $node['CONFIG'][1] == "#" ? '<ol>' : '<ul>';
      $GLOBALS['listitems'][] = $node['CONFIG'][1];
      $o .= $listtype;
    }

  $o .= "<li>";
  #$o = htmlentities($o);
  return StringFormat::htmlIndent($o, StringFormat::START);
}

function elistitem($node, $lexer) {
  $o = "</li>";
  $ns = $lexer->nextSibling($node);
  if ($ns != NULL) {

    $thislevel = strlen($node['CONFIG'][0]);
    $nextlevel = strlen($ns['CONFIG'][0]);

    if ($nextlevel < $thislevel) {
      $difflevel = $thislevel - $nextlevel;
      for ($i = 0; $i < $difflevel; $i++) {
        $listtype = array_pop($GLOBALS['listitems']);
        $listtype = $listtype == "#" ? '</ol>' : '</ul>';
        $o .= $listtype;
      }
    }


  }
  #$o = htmlentities($o);
  return StringFormat::htmlIndent($o, StringFormat::END);
}

function elist($node, $lexer) {
  $o = "";
  $lclevel = count($GLOBALS['listitems']);
  for ($i = 0; $i < $lclevel; $i++) {
    $listtype = array_pop($GLOBALS['listitems']);
    $listtype = $listtype == "#" ? '</ol>' : '</ul>';
    $o .= $listtype;
  }
  #$o = htmlentities($o);
  return StringFormat::htmlIndent($o, StringFormat::END);
}

function seof() {
  #return "_____EOF_____";
}

function stable() {
  return '<div class="tablediv"><table>';
}

function etable() {
  return '</table></div>';
}

function stablecell($node, $lexer) {
  $o = "";

  $chid = $lexer->childPosition($node);
  $rowspan = rowspantext($node, $lexer, $chid);
  $colspan = colspantext($node, $lexer, $chid);

  $fc = $lexer->firstChild($node);
  if ($fc and $fc['NAME'] !== "tablespan") {
    $o = '<td'.$rowspan.$colspan.'>';
    $o .= $lexer->getText($node);
    $o .= '</td>';
  }
  return $o;
}

function etablecell() {
  return '</td>';
}


function rowspantext($node, $lexer, $chid) {
  $nx = $node;
  $rowspans = 1;
  while($nx) {
  	$nx = $lexer->nextSiblingSameChild($nx, $chid);
    $fc = $lexer->firstChild($nx);
    if ($fc['NAME'] == "tablespan") {
      $rowspans++;
    } else {
      break;
    }
  }
  $rowspan = $rowspans == 1 ? '' : ' rowspan="'.$rowspans.'"';
  return $rowspan;
}

function colspantext($node, $lexer, $chid) {
  $nx = $node;
  $colspans = 1;
  while($nx) {
  	$nx = $lexer->nextSibling($nx, $lexer, $chid);
    if (!$lexer->hasChildNodes($nx)) {
      $colspans++;
    } else {
      break;
    }
  }
  $colspan = $colspans == 1 ? '' : ' colspan="'.$colspans.'"';
  return $colspan;
}

function stableheader($node, $lexer) {
  $o = "";
  $chid = $lexer->childPosition($node);
  $rowspan = rowspantext($node, $lexer, $chid);
  $colspan = colspantext($node, $lexer, $chid);
  $fc = $lexer->firstChild($node);
  if ($fc and $fc['NAME'] !== "tablespan") {
    $o = '<th'.$rowspan.$colspan.'>';
    $o .= $lexer->getText($node);
    $o .= '</th>';
  }
  return $o;
}

function spre() {
  return '<pre><div>';
}

function epre() {
  return '</div></pre>';
}

function snotoc() {
  return '';
}

function shrule() {
  return '<hr />';
}

function salignintable($node, $lexer) {
  $type = $node['CONFIG'][0];
  if ($type == '>') {
    return '<div align="right">';
  } else {
    return '<div align="center">';
  }
}

function ealignintable() {
  return '</div>';
}

function unescape($txt) {
  $esc    = array ('\"',
                   '\>');
  $unesc  = array ('"',
                   '&gt;');
  return str_replace($esc, $unesc, $txt);
}


function svariable($node, $lexer) {
  $varname = utf8_strtolower($node['CONFIG'][0]);
  $value = $lexer->getText($node);

  if ($_SESSION['pw_wiki']['error']) {
    $_SESSION['pw_wiki']['error'] = false;
    return $value.nop("Die Variable kann wegen interner Fehler nicht gesetzt werden.");
  }

  $GLOBALS['variables'][$varname] = $value;
}

function sconst($node, $lexer) {
  #out2("SCONST");
  #out2($node);
  global $variables;

  $conf = pw_s2u($node['CONFIG'][0]);


  if (preg_match("#(.*) *= *(.*)#i", $conf, $ass)) {
    $varname = utf8_strtolower(utf8_trim($ass[1]));
    $value = utf8_trim($ass[2]);
    $GLOBALS['variables'][$varname] = $value;
    return;
  }

  $conf = utf8_strtolower($conf);

  $txt = "";

  //@TODO: Ersetzen mit richtigen PHP i18n Funktionen!!!
  $months_translated = array("Januar","Februar","M&auml;rz","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember");;
  $months = array("January","February","March","April","May","June","July","August","September","October","November","December");
  $days = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
  $days_translated = array("Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag");

  $varnames = explode(":", $conf);
  $varname = array_shift($varnames);
  $subcat = array_pop($varnames);
  #out($varname);
  switch($varname) {
    case 'date': $txt = date('d.m.Y'); break;
    case 'month': $txt = date('m'); break;
    case 'monthname': $txt = str_replace($months,$months_translated,date('F')); break;
    case 'day': $txt = date('d'); break;
    case 'dayname': $txt = str_replace($days,$days_translated,date('l')); break;
    case 'year': $txt = date('Y'); break;
    case 'time': $txt = date('H:i'); break;
    case 'pi': $txt = str_replace('.',',',round(pi(), 5)); break;
    case 'e': $txt = str_replace('.',',',round(2.718281828459045235, 5)); break;
    case 'ns': $txt = pw_url2u(pw_wiki_getcfg('ns')); if ($txt === false) $txt = "[root]"; break;
    case 'id': $txt = pw_url2u(pw_wiki_getcfg('pg')); break;
    case 'wrongid': $txt = pw_url2u(pw_wiki_getcfg('wrongid')); break;
    case 'fullid': $txt = pw_url2u(pw_wiki_getcfg('id')); break;
    case 'startpage': $txt = ':'.pw_url2u(pw_wiki_getcfg('startpage')); break;
    case 'version': $txt = pw_wiki_version(); break;
    case 'lexerversion': $txt = $lexer->getVersion(); break;
    case 'path': $txt = 'http://'.$_SERVER['SERVER_NAME'].pw_dirname($_SERVER['PHP_SELF']); break;
    case 'countsubs':
      // zähle alle wikipages im aktuellen namespace
      $path = pw_wiki_getcfg('path');
      $ext = pw_wiki_getcfg('fileext');
      $txt = count(glob($path."/*".$ext));
    break;
    case 'performance':
      $txt = $lexer->getExecutionTime();
    break;
    case 'file':
      $txt = pw_wiki_fileinfo($subcat);
    break;
    default:

      if (isset($variables[$varname])) {
        $txt = $variables[$varname];
        $txt = unescape($txt);
        return $txt;
      } else {
        $_SESSION['pw_wiki']['error'] = true;
        return nop("VARIABLE '$varname' wurde nicht gesetzt.", false);
      }

    break;
  }

  return pw_s2e($txt);
}

function ssymbol($node, $lexer) {
  $symbol = $node['CONFIG'][0];

  // Veränderte Darstellung nur in class=math
  #echo '<span class="symbols">&'.$symbol.';</span>';
  return '&'.$symbol.';';
}

function sleft() {
  return '<div class="section_left">';
}

function eleft($node, $lexer) {
  $o = '</div>';
  $ns = $lexer->nextSibling($node);
  $cfg = isset($node['CONFIG'][1]) ? $node['CONFIG'][1] : null;
  $o .= $cfg;
  if ($ns['NAME'] != 'right' and $cfg != "alone") {
    $o .= '<div class="clear"></div>';
  }
  return $o;
}

function sright() {
  return '<div class="section_right">';
}

function eright($node, $lexer) {
  $o = '</div>';
  $ns = $lexer->nextSibling($node);
  $cfg = isset($node['CONFIG'][1]) ? $node['CONFIG'][1] : null;
  if ($ns['NAME'] != 'left' and $cfg != "alone") {
    $o .= '<div class="clear"></div>';
  }
  return $o;
}


function smath() {
  return '<span class="section_math">';
}

function emath() {
  return '</span>';
}

function sexternallink($node, $lexer) {

  $urlnode = $lexer->firstChild($node);
  $url = $lexer->getText($urlnode);

  //@TODO: refactor... common function... bubble-up of an error until ????
  if ($_SESSION['pw_wiki']['error']) {
    $_SESSION['pw_wiki']['error'] = false;
    return $url." ".nop("Externer Link kann wegen interner Fehler nicht aufgelöst werden.");
  }


  if (!pw_isvalid_url($url)) {
    return "[".$lexer->getText($node, " ")."]";
  }

  // Start with $urlnode and walk to the end...
  $txt = $lexer->getText2($urlnode);

  $target = 'target="_blank" ';
  $mailto = substr($url, 0, 7) == "mailto:" ? true : false;
  if ($mailto) {
    $target = "";
  }

  if ($txt == "") {
    $txt = $url;
  }

  return '<a '.$target.'href="'.$url.'">'.$txt.'</a>';
}

function spluginparam($node, $lexer) {
  if(!$lexer->hasChildNodes($node))
    return "";
}


function surl2($node, $lexer) {
  $url = $node['CONFIG'][0];
  return '<a href="http://'.$url.'">'.$url.'</a>';
}

function nop($txt) {
  $out = "<span style='color: yellow'>[WARNUNG: ";
  $out .= $txt;
  $out .= "]</span>";

  return $out;

}

?>