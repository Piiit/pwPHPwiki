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

function snotoc() {
  return '';
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

function surl2($node, $lexer) {
  $url = $node['CONFIG'][0];
  return '<a href="http://'.$url.'">'.$url.'</a>';
}



?>