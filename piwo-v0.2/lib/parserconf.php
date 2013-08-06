<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/pw_lexer.php';
require_once INC_PATH.'piwo-v0.2/lib/common.php';
require_once INC_PATH.'pwTools/string/encoding.php';
require_once INC_PATH.'pwTools/data/IndexTable.php';


function snewline() {
  #return '<br />';
}

function snewline2() {
  return '<br />';
}
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

function spreformat() {
  return '';
}

function epreformat() {
  return "\n";
}


function smultiline() {
  return '';
}

function emultiline() {
  return '';
}

function snotoc() {
  return '';
}

function shrule() {
  return '<hr />';
}

function sindent($node, $lexer) {
  // Pro Zeile ein neues DIV-Element...
  #$level = strlen($node['CONFIG'][0])*10;
  #$last = $lexer->previousSibling($node);
  #if ($last['NAME'] == "indent") {
  #  $lastlevel = strlen($last['CONFIG'][0])*10;
  #  if($lastlevel != $level) {
  #    echo '<div style="margin-left: '.$level.'">';
  #    #echo 'START-INDENT: l='.$level.'|';
  #  }
  #} else {
  #  echo '<div style="margin-left: '.$level.'">';
  #  #echo 'START-INDENT: l='.$level.'|';
  #}

  $level = strlen($node['CONFIG'][0])*10;
  return '<div style="margin-left: '.$level.'px">';
}

function eindent($node, $lexer) {
  #$next = $lexer->nextSibling($node);
  #if ($next['NAME'] != "indent") {
  #  echo '</div>';
  #  #echo 'END-INDENT 1<br />';
  #} else {
  #  $nextlevel = $next['CONFIG'][0];
  #  $nextlevel = strlen($nextlevel)*10;
  #  $level = strlen($node['CONFIG'][0])*10;
  #  if($nextlevel != $level) {
  #    echo '</div>';
  #    #echo "END-INDENT 2: $level; $nextlevel<br />";
  #  }
  #}

  return '</div>';
}

function salign($node, $lexer) {
  $type = $node['CONFIG'][0];
  if ($type == '>') {
    return '<div align="right">';
  } else {
    return '<div align="center">';
  }
}

function ealign() {
  return '</div>';
}

function sjustify($node, $lexer) {
  return '<div align="justify">';
}

function ejustify($node, $lexer) {
  return '</div>';
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

function sheader($node, $lexer) {
  $o = "";
  $level = strlen($node['CONFIG'][0]);
  $level = strlen($node['CONFIG'][0]);

  if ($lexer->hasAncestor($node, "notoc")) {
    $o = '<h'.$level.'>';
  } else {
    global $idheader;
    global $indextable;
//    TestingTools::inform($indextable);
    $fc = $lexer->firstChild($node);
    $txt = trim($fc['VALUE']);
    //$o = '<h'.$level.' id="header_'.$GLOBALS['indextable']['CONT'][$idheader]['ID'].'">';
    $o = '<h'.$level.' id="header_'.$indextable->getByIndex($idheader)->getId().'">';
    $GLOBALS['idheader']++;
  }

  $htxt = $lexer->getText($node);

  if (!$htxt) {
    $o .= nop("Leere Titel sind nicht erlaubt!");
  }
  $o .= $htxt;
  $o .= '</h'.$level.'>';

  return $o;
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

function scode($node, $lexer) {
  $tn = $lexer->firstChild($node);
  $text = pw_s2e($tn['VALUE']);
  $o = '<pre><div>';
  $o .= utf8_trim($text, "\n");
  return $o;
}

function ecode() {
  $o = '</div></pre>';
  return $o;
}
function smath() {
  return '<span class="section_math">';
}

function emath() {
  return '</span>';
}

function shi($node, $lexer) {
  $colorid = trim($node['CONFIG'][0]);
  $colors = array("orange" => 0, "green" => 1, "yellow" => 2, "red" => 3, "blue" => 4);

  if (is_string($colorid) and array_key_exists($colorid, $colors)) {
    $colorid = $colors[$colorid];
  }

  if (!is_numeric($colorid) or $colorid < 0 or $colorid > 4) {
    $colorid = 0;
  }

  return '<span class="highlighted c'.$colorid.'">';
}

function surl($node, $lexer) {
  $url = $node['CONFIG'][0];
  $target = 'target="_blank" ';
  $mailto = substr($url, 0, 7) == "mailto:" ? true : false;
  if ($mailto) {
    $target = "";
  }

  return '<a '.$target.'href="'.$url.'">'.$url.'</a>';
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

function sinternallink($node, $lexer) {
  //@TODO: clean redundant code... specially for encoding-functions!
  global $indextable;
  global $moditext;

  $linkpos = $lexer->firstChild($node);
  $linkpostxt = $lexer->getText($linkpos);
  #out2($_SESSION['pw_wiki']['error']);
  #out($linkpostxt);

  //@TODO: refactor... common function... bubble-up of an error until ????
  if ($_SESSION['pw_wiki']['error']) {
    $_SESSION['pw_wiki']['error'] = false;
    return $linkpostxt.nop("Interner Link kann wegen interner Fehler nicht aufgelöst werden.");
  }

  $fullid = $linkpostxt;
  $modus = false;

  if (preg_match("#(.*)&gt;(.*)#", $linkpostxt, $xp_lpt)) {
    $modus = $xp_lpt[1];
    $fullid = $xp_lpt[2];

    $modi = explode("|", $moditext);
    if (!in_array($modus, $modi)) {
      return nop("Interner Link mit falschem Modus '$modus'. Erlaubte Modi sind: ".$moditext);
    }
  }

  if (!$fullid) {
    return nop("Interner Wikilink ohne Zielangabe. Leerer Wikilink?", false);
  }

  $text = $lexer->getText2($linkpos);

  //@TODO: refactor... common function... bubble-up of an error until ????
  if ($_SESSION['pw_wiki']['error']) {
    $_SESSION['pw_wiki']['error'] = false;
    //@TODO: refactor... Interner Link Token einfach ausgeben ohne ihn zu verarbeiten... restore! und darin enthalten die interne Fehlermeldung!!!
    return pw_e2u($text)." ".nop("Interner Link kann wegen interner Fehler nicht aufgelöst werden.");
  }

/*
  $text = "";
  for ($textpos = $lexer->nextSibling($linkpos); $textpos != null; $textpos = $lexer->nextSibling($textpos)) {
    $ret = $lexer->getText($textpos);
    if (!$ret) {
      $ret = $lexer->callFunction($textpos, ONENTRY);
      $ret .= $lexer->callFunction($textpos, ONEXIT);

    }
    $ret = utf8_encode(htmlentities(utf8_decode($ret)));
    $text .= $ret;
  }
*/

  $found = true;
  $na = "";
  $type = "INTERNAL";
  $section = null;
  if ($fullid[0] == "#") {
    $idtxt = ltrim($fullid, "#");
    $id = pw_s2u($idtxt);
    $id = utf8_strtolower($id);
    $type = "JUMP";

    switch($id) {
      case "_top": $href = "#__main"; break;
      case "_bottom": $href = "#__bottom"; break;
      case "_toc": $href = "#__toc"; break;
      case "_maintitle": $href = "#__fullsite"; break;
      default:
      	
      	try {

      		$item = $indextable->getByIdOrText($id);
            $section = $item->getId();
            if (!$text) {
                $text = $item->getText();
            }
            $text = pw_s2e($text);
            
        } catch (Exception $e) {
            $found = false;
            $href = "#";
            $text = pw_url2u($id);
            $na = ' class="pw_wiki_link_na"';
        }

      break;
    }

  } else {

    //out($fullid);
    preg_match("/(.*)#(.*)/", $fullid, $lpt);
    #out($lpt);

    $id = isset($lpt[1]) ? $lpt[1] : $fullid;
    $jump = "";
    if (isset($lpt[2]) and strlen($lpt[2]) > 0) {
      $jump = "#".utf8_strtolower(pw_s2url($lpt[2]));
    }

    $id = pw_url2t($id);

    // Absolute Pfadangabe...
    if ($id[0] == ':') {
      $id = ltrim($id, ':');
    } else {
      $ns = pw_wiki_getcfg('fullns');
      $id = $ns ? $ns.$id : $id;
    }

    $id = pw_e2u($id);
    $filename = pw_wiki_path($id, ST_FULL);
    #out($id);
    #$filename = pw_u2t($filename);
    #out(file_exists($filename), $filename);
    #out($filename);
    #out2(utf8_check($filename));
    #die();

    if (!file_exists($filename) and !$modus) {
      $na = ' class="pw_wiki_link_na"';
      $modus = "edit";
      $found = false;
    }
    
    if (!$text) {
      $text = pw_wiki_pg(pw_e2u($fullid));
      #out(pw_e2u($fullid));
    }

    $href = "?id=".pw_s2url($id).$jump;

  }

  if ($type == "JUMP" and !$text) {
    $text = pw_wiki_getcfg('anchor_text', $id);
    if (!$text) {
      $text = $item->getText();
    }
    $text = pw_s2e($text);
  }


  if ($modus == "edit" and $section) {
    $href = "?id=".pw_wiki_getcfg('id');
    $href .= "&mode=editpage&amp;section=$section";
  }

  if ($type == "JUMP" and !$modus and $section) {
    $href = "#header_".$section;
  }

  if ($type == "INTERNAL") {
    if ($modus == "edit" or !$found) {
      $href .= '&mode=editpage';
    }
    if ($modus == "showpages") {
      $href .= "&mode=showpages";
      $na = '';
    }
  }


  // AJAX-Links...
  #return '<a onclick="wikilink(\''.$fullid.'\'); return false;" href="#id='.$fullid.'"'.$na.'>'.$text.'</a>';
  #out("LPTXT=$linkpostxt; MODUS=$modus; TEXT=$text; LINK=$fullid; TYPE=$type; \nID=$id; HREF=$href; FOUND=".($found?"TRUE":"FALSE")."; SECTION=$section;");
  #return $linkpostxt.'|'.$textnode['VALUE'].' [a href="'.$href.'"'.$na.']'.$text.'[/a]';

  //@TODO alle hrefs encodieren und strtolower anwenden (achtung bei utf8-Sonderzeichen)
  #$href = pw_wiki_urlencode($href);
  #$text = pw_wiki_entities($text);
  #$text = pw_s2e($text);
  return '<a href="'.$href.'"'.$na.'>'.$text.'</a>';

}

function spluginparam($node, $lexer) {
  if(!$lexer->hasChildNodes($node))
    return "";
}


function surl2($node, $lexer) {
  $url = $node['CONFIG'][0];
  return '<a href="http://'.$url.'">'.$url.'</a>';
}

function splugin($node, $lexer) {
  $pluginname = strtolower($node['CONFIG'][0]);
  $funcname = "plugin_".$pluginname;
  if (!function_exists($funcname)) {
    return nop("PLUGIN '$pluginname' nicht verf&uuml;gbar.",false);
  }
  return call_user_func($funcname, $lexer, $node);
}


function nop($txt) {
  $out = "<span style='color: yellow'>[WARNUNG: ";
  $out .= $txt;
  $out .= "]</span>";

  return $out;

}

?>