<?phpfunction plugin_toc($lexer, $node) {    global $indextable;    if (isset($indextable['CONT'])) {      $o  = '<div class="toc" id="__toc">';      $o .= '<ul>';      $lastlvl = 0;
      foreach($indextable['CONT'] as $idx) {        if ($lastlvl < $idx['LEVEL']) {          $diff = $idx['LEVEL']-$lastlvl;          for ($i = 0; $i < $diff; $i++)            $o .= '<ul>';        } elseif ($lastlvl > $idx['LEVEL']) {          $diff = $lastlvl-$idx['LEVEL'];          for ($i = 0; $i < $diff; $i++)            $o .= '</ul>';        }        $o .= '<li>';        $o .= '<a href="#header_'.$idx['ID'].'">'.$idx['ID'].' '.pw_s2e($idx['TEXT']).'</a>';        $o .= '</li>';        $lastlvl = $idx['LEVEL'];      }      $o .= '</ul>';      $o .= '</div>';      #out($indextable);    }    return $o;  }
function plugin_nstoc($lexer, $node) {  if (!is_object($lexer) or $lexer->checkNode($node) === false) {    throw new Exception("Plugin_NSTOC: Wrong Parametertype(s).");    return;  }  $cont = $lexer->getArray($node);  // Der erste Parameter gibt den Namensraum an...  $nstxt = array_shift($cont);  $nstxt = utf8_trim($nstxt);  $nstxt = pw_wiki_fullns($nstxt.':');    // Parameter TITLE: Soll ein Titel ausgegeben werden?  $titeltxt = "";  if (in_array("TITLE", $cont)) {  	// @TODO: BUG: Falls relative Pfadangaben (wie ..) bestehen muss der Pfad 	// zuerst aufgelöst werden, bevor man den korrekten Titel ermitteln kann.	$nslist = $nstxt;	    if ($nslist == "") {      $nslist = "[root]";	  $nstxt = ":";    }		//@TODO: remove ending : with regular expression if there are more than one!!!	if ($nslist[strlen($nslist)-1] == ':') {	  $nslist = substr($nslist, 0, -1);	}	    $titeltxt = utf8_ucwords(str_replace(":", " &raquo; ", $nslist));    $titeltxt = "Inhalt des Namensraumes <i>\"$titeltxt\"</i>: ";  }  // Parameter NOERR: Fehlermeldungen unterdrücken?  $error = true;  if (in_array("NOERR", $cont)) {    $error = false;  }  // Parameter SHOWNS: Zeige auch die untergeordneten Namensräume...  $showns = false;  if (in_array("SHOWNS", $cont)) {    $showns = true;  }  return pw_wiki_nstoc($nstxt, $titeltxt, $error, $showns);}function pw_wiki_nstoc($ns, $titel, $error, $showns) {    $ext = "";  $o = "";  $glob_flag = 0;  if (!$showns) {    $ext = pw_wiki_getcfg('fileext');  } else {    $glob_flag = GLOB_ONLYDIR;  }    $path = pw_wiki_getcfg('path');  if ($ns) {    $path = pw_wiki_path($ns, ST_NOEXT);  }     $wikis = glob($path."/*".$ext, $glob_flag);  // Titel werden nur ausgegeben, wenn Fehlermeldungen auch ausgegeben werden dürfen!  // ...sonst kann es zu alleinstehenden Titeln kommen.  if (utf8_strlen($titel) > 0 and $error) {    $o .= $titel;  }  if($error and empty($wikis))    return $o."<br />".nop("Es sind keine Texte im Namensraum '".pw_s2e($ns)."' vorhanden.", false);  $o .= "<ul>";  foreach($wikis as $i) {    $page = pw_basename($i, ".txt");    $page = utf8_ucfirst($page);    $page = pw_s2e($page);    $id = pw_wiki_path2id($i);    $o .= "<li><a href='?id=$id'>".$page."</a></li>";  }  $o .= "</ul>";  return $o;}
function createindextable($lexer, $node = NULL, $indextable = NULL) {	// 	out2($GLOBALS['indextable']);
  if (!is_object($lexer) or $lexer->checkNode($node) === false) {    throw new Exception("CreateIndexTable: Wrong Parametertype(s).");  }    for ($node = $lexer->firstChild($node); $node != null; $node = $lexer->nextSibling($node)) {    out($node);
    if ($node['NAME'] == "header") {      $fc = $lexer->getText($node);
      $entry['TEXT'] = utf8_trim(pw_e2u($fc));      $entry['LEVEL'] = utf8_strlen($node['CONFIG'][0]);            $indextable->add($entry['LEVEL'], $entry['TEXT']);
      if ($GLOBALS['indextable']['LEVELS']['LASTLEVEL'] > $entry['LEVEL']) {        $GLOBALS['indextable']['LEVELS'][$entry['LEVEL']+1] = 0;        $GLOBALS['indextable']['LEVELS'][$entry['LEVEL']+2] = 0;        $GLOBALS['indextable']['LEVELS'][$entry['LEVEL']+3] = 0;        $GLOBALS['indextable']['LEVELS'][$entry['LEVEL']+4] = 0;      }
      $GLOBALS['indextable']['LEVELS'][$entry['LEVEL']]++;      $l = $GLOBALS['indextable']['LEVELS'];      $l = StringTools::rightTrim("$l[1].$l[2].$l[3].$l[4].$l[5]", ".0");
      $entry['ID'] = $l;
      $GLOBALS['indextable']['CONT'][] = $entry;
      $GLOBALS['indextable']['LEVELS']['LASTLEVEL'] = $entry['LEVEL'];    }
    if ($lexer->hasChildNodes($node) and $node['NAME'] != "notoc") {        createindextable($lexer, $node, $indextable);    }
  }
}

function getindextable() {
  return $GLOBALS['indextable'];
}

function getindexitem($idxortxt, $id = true) {
  global $indextable;
  $idxortxt = strtolower(pw_s2u(trim($idxortxt)));
  if (!is_array($indextable['CONT'])) {
    return false;
  }

  foreach ($indextable['CONT'] as $item) {
    if ($id and $item['ID'] == $idxortxt) {
      return $item;
    }
    $itemtext = strtolower(pw_s2e(trim($item['TEXT'])));
    #out("$itemtext --- $idxortxt");
    if (!$id and $itemtext == $idxortxt) {
      #out($item);
      return $item;
    }
  }

  return NULL;
}

function plugin_trace ($lexer, $node, $sep = "&raquo;") {
  $sep = ' '.$sep.' ';

  $id = pw_wiki_getcfg('id');
  $mode = pw_wiki_getcfg('mode');
  $startpage = pw_wiki_getcfg('startpage');

  #out($id);

  $o = "<a href='?mode=cleared&id=".pw_s2url($startpage)."'>Home</a>";

  $fullpath = explode(":", $id);
  $pg = array_pop($fullpath);

  $p = "";
  foreach ($fullpath as $i) {
    $p .= "$i:";
    $i = pw_url2u($i);
    $i = utf8_ucfirst($i);
    $i = pw_s2e($i);
    $o .= $sep."<a href='?mode=cleared&id=".rtrim($p, ":")."'>".$i."</a>";
  }

  $pg = pw_url2u($pg);
  $pg = utf8_ucfirst($pg);
  $pg = pw_s2e($pg);
  $o .= $sep.$pg;

  return $o;
}

?>