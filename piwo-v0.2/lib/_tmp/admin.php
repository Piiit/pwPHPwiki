<?php
  #require_once "pw_csv.php";
  #require_once "pw_debug.php";

  require_once "lexerconf.php";

  function pw_ajax_init() {
    echo '<script type="text/javascript" src="../pw_ajax.js"></script>';
  }

  #function pw_ajax_

  function pw_csv_ui_showtables() {
    global $pw_csv;
    $out = "<b>Aktueller Ordner:</b> ".basename($pw_csv[csv_path]);
    $out .= "<br/><b>Aktuelle Tabelle:</b> <span id='curtable'></span>";
    $out .= "<h1>Tabelle w&auml;hlen</h1>";
    $out .= "<form name='listtables' method='post'><select size='3' id='set_table' class='csv_listtables' onchange='listtables_changed(); return false;'>";
    #var_dump(pw_csv_renew_tablelist());
    $tables = pw_csv_get_tablelist();
    if (!$tables) return false;
    foreach ($tables as $tkey => $table)
      $out .= "<option>$tkey</option>";
    $out .= "</select></form>";

    return $out;
  }

  function pw_csv_ui_showtable($table = NULL) {
    if ($th = pw_csv_ui_outheader($table)) {
      $tc = pw_csv_ui_outcontent($table);
      echo "<table>$th$tc</table>";
    } else {
      echo "<div class='ajax_info'><div><img src='../icons/error.png' /><span>Die Tabelle '$table' ist fehlerhaft!</span></div></div>";
    }

  }


  function pw_csv_ui_showinfo($name = NULL) {
    global $pw_csv;

    // Mit welchem Table soll gearbeitet werden?
    if (!$table_name = pw_csv_get_current2($name)) return false;
    if (!$table = pw_csv_get($table_name)) return false;
    if (!$table[struct]) return false;

    $struct = $table[struct];

    // Hauptkommentare ausgeben
    if ($maincomment = pw_csv_get_maincomment($table_name)) echo "<h1>Beschreibung</h1><code>$maincomment</code><br/>";

    // Informationen zur Datei ausgeben
    setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
    echo "<h1>Informationen zur Datei</h1><ul>";
    echo "<li><b>Pfad:</b><i>".$pw_csv[csv_path]."</i></li>";
    echo "<li><b>Name:</b><i>".$table[name].$pw_csv[csv_ext]."</i></li>";
    echo "<li><b>Erstellt am:</b><i>".strftime("%A, den %d.%m.%Y um %H:%M Uhr", $table[file_created])."</i></li>";
    echo "<li><b>Ge&auml;ndert am:</b><i>".strftime("%A, den %d.%m.%Y um %H:%M Uhr", $table[file_modified])."</i></li>";
    echo "<li><b>Gr&ouml;&szlig;e:</b><i>".formatBytes(filesize($pw_csv[csv_path].$table[name].$pw_csv[csv_ext]))."</i></li>";
    echo "</ul>";

    echo "<h1>Allgemeine Konfiguration</h1><ul>";
    if (isset($table[config]))
      foreach ($table[config] as $item => $value)
        echo "<li><b>$item</b><i>".$value[value]."</i></li>";
    else
      echo "<i>Keine Konfiguration vorhanden.</i>";
    echo "</ul>";

    echo "<h1>Tabellenstruktur</h1><ul>";
    if (isset($table[struct]))
      foreach ($table[struct] as $item => $value) {
        if (!preg_match("#(".$pw_csv[keywords].")#i", $item)) {
          $icons = "";

          if ($value[subtype])
            $icons .= "<img title='[$item] ist vom Typ.Untertyp \"".$value[type].".".$value[subtype]."\".' src='../icons/shape_move_backwards.png' />";
          else
            $icons .= "<img title='[$item] hat keinen Untertyp.' src='../icons/x.gif' />";

          if (!$value[config])
            $icons .= "<img title='[$item] verwendet die Standardkonfiguration.' src='../icons/x.gif' />";
          else
            $icons .= "<img title='[$item] wurde manuell konfiguriert.' src='../icons/wrench_orange.png' />";


          $icons .= "&nbsp;&nbsp;&nbsp;&nbsp;";


          if (pw_csv_attrib_primary_key($item))
            $icons .= "<img title='[$item] ist der Prim&auml;rschl&uuml;ssel.' src='../icons/bullet_key.png' />";
          else
            $icons .= "<img title='Attribut \"Prim&auml;rschl&uuml;ssel\" nicht gesetzt!' src='../icons/x.gif' />";


          if (pw_csv_attrib_unique($item))
            $icons .= "<img title='[$item] darf in der Tabelle nur einmal vorkommen.' src='../icons/award_star_gold_1.png' />";
          else
            $icons .= "<img title='Attribut \"UNIQUE\" nicht gesetzt!' src='../icons/x.gif' />";


          if (pw_csv_attrib_auto_inc($item))
            $icons .= "<img title='[$item] wird automatisch inkrementiert (+1).' src='../icons/arrow_up.png' />";
          else
            $icons .= "<img title='Attribut \"AUTO_INC\" nicht gesetzt!' src='../icons/x.gif' />";

          if (pw_csv_attrib_null($item))
            $icons .= "<img title='[$item] darf auch leer sein.' src='../icons/bullet_green.png' />";
          else
            $icons .= "<img title='[$item] darf nicht leer sein.' src='../icons/bullet_red.png' />";


          echo "<li><b><img title='Details betrachten.' onclick='showhide(\"".$item."_item\", this)' src='../icons/plus.gif'/>$item</b><span>$icons</span><i>".$value[type]."</i>";
          echo "<div id='".$item."_item'>";
          echo "<img src='../icons/timeline_marker.png' style='margin: 0px 0px -4px 0'/> Attribute:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\"$value[attrib]\" (hex)<br/>";
          if ($value[lastkey]) {
            $lastkey = $value[lastkey];
            if ($table[content][count] == 0) $lastkey++;
            echo "<img src='../icons/arrow_up.png' style='margin: 0px 0px -4px 0'/> AUTO_INC-Wert:&nbsp;\"".$lastkey."\"<br/>";
          }
          if ($value[subtype]) echo "<img src='../icons/shape_move_backwards.png' style='margin: 0px 0px -4px 0'/> Untertyp:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\"$value[type].$value[subtype]\"<br/>";
          echo "<img src='../icons/wrench_orange.png' style='margin: 0px 0px -4px 0'/> Einstellungen:&nbsp;";
          if (!$value[config])
            echo "&raquo;fehlen&laquo; &rarr; Standardkonfiguration gesetzt.<br/>";
          elseif (is_string($value[config]))
            echo "\"$value[config]\"<br/>";
          elseif (is_array($value[config])) {
            $c = count($value[config]);
            echo "($c Eintr&auml;ge)<br/>";
            foreach ($value[config] as $conf_k => $conf_v) {
              ($c > 10 and $conf_k < 10) ? $co = "&nbsp;" : $co = "";
              echo "&nbsp;&nbsp;&nbsp;$co$conf_k &rarr; \"$conf_v\"<br/>";
            }
          }

          echo "</div></li>";
        }
      }
    else
      echo "<i>Keine Tabellenstruktur vorhanden.</i>";

    echo "</ul>";
    echo "<h1>Statistiken</h1><ul>";
    echo "<li><b>Anzahl der g&uuml;ltigen Datens&auml;tze:</b><i>".$table[content][count]."</i></li>";
    echo "<li><b>Anzahl der fehlerhaften Datens&auml;tze:</b><i>".$table[failed][errors]."</i></li>";
    echo "</ul>";

    return true;
  }

  function pw_csv_ui_outheader($name = NULL) {
    global $pw_csv;

    // Mit welchem Table soll gearbeitet werden?
    if (!$table_name = pw_csv_get_current2($name)) return false;
    if (!$table = pw_csv_get($table_name)) return false;
    if (!$table[struct]) return false;

    $prim_key = pw_csv_get_primary_key($table_name);

    $output = "<thead><tr>";
    foreach ($table[struct] as $key => $heads) {
      if (!preg_match("#(".$pw_csv[keywords].")#i", $key)) {
        $table[config][$key][value] ? $value = $table[config][$key][value] : $value = $key;
        ($key == $prim_key) ? $pkimg = "&nbsp;<img src='../icons/bullet_key.png' />" : $pkimg = "";
        $output .= "<th id='$key'>$value$pkimg</th>";
      }
    }

    return "$output</tr></thead>";

  }

  function pw_csv_ui_outcontent($name = NULL) {
    global $pw_csv;

    // Mit welchem Table soll gearbeitet werden?
    if (!$table_name = pw_csv_get_current2($name)) return false;
    if (!$table = pw_csv_get($table_name)) return false;
    if (!$table[content]) return false;

    $output = "<tbody>";
    $i = 0;
    foreach ($table[content] as $prim_key => $row) {
      if (!preg_match("#(".$pw_csv[keywords].")#i", $prim_key)) {
        ($i++ % 2) ? $output .= "<tr class='roweven'>" : $output .= "<tr class='rowodd'>";
        foreach ($row as $item)
          if (is_array($item)) {
            $output .= "<td>";
            foreach($item as $aritem)
              $output .= $aritem."<br />";
            $output .= "</td>";
          } else
            $output .= "<td>$item</td>";
      }
    }

    return "$output</tbody>";


  }

  function pw_csv_ui_showtoolbar($id = 0) {
      echo "<div id='tab0'>";
      echo "<h1>Grund-Einstellungen</h1>";
      echo "<a href='#' onclick='pw_ui_modaldialog(\"edittable\"); return false'><img src='../icons/table_edit.png' /><span>Tabelle bearbeiten</span></a>";
      echo "<a href='#'><img src='../icons/table_add.png' /><span>Tabelle hinzuf&uuml;gen</span></a>";
      echo "<a href='#' onclick='pw_ui_modaldialog(\"deltable\"); return false'><img src='../icons/table_delete.png' /><span>Tabelle l&ouml;schen</span></a>";
      echo "<a href='#'><img src='../icons/drive_go.png' /><span>Dateien hochladen</span></a>";
      echo "<a href='#'><img src='../icons/table_save.png' /><span>Tabelle herunterladen</span></a>";
      echo "<a href='#'><img src='../icons/email_go.png' /><span>Benachrichtigungen</span></a>";
      echo "<a class='tab' href='#' onclick='tabswitch(tab0, tab1)'>&raquo; Weiter</a>";
      echo "</div>";
      echo "<div id='tab1'>";
      echo "<h1>Erweiterte Einstellungen</h1>";
      echo "<a href='#'><img src='../icons/table_key.png' /><span>Zugriffsrechte bearbeiten</span></a>";
      echo "<a href='#'><img src='../icons/folder_edit.png' /><span>Ordner anpassen</span></a>";
      echo "<a href='#'><img src='../icons/page_code.png' /><span>Quelltext anzeigen</span></a>";
      echo "<a href='#'><img src='../icons/application_home.png' /><span>Ansichten verwalten</span></a>";
      echo "<a href='#'><img src='../icons/application_form_add.png' /><span>Formulare verwalten</span></a>";
      echo "<a href='#'><img src='../icons/database_connect.png' /><span>CSV-Datei importieren</span></a>";
      echo "<a class='tab' href='#' onclick='tabswitch(tab1, tab0)'>&laquo; Zur&uuml;ck</a>";
      echo "</div>";

  }

  function pw_csv_ui_log($type, $title, $text) {
    switch ($type) {
      case "info": $img = "<img src='../icons/info.png' />"; break;
      case "warning": $img = "<img src='../icons/bell.png' />"; break;
      case "error": $img = "<img src='../icons/error.png' />"; break;
    }
    $time = strftime("[%d.%m.%Y|%H:%M:%S]", time())."&nbsp;";

    return "<div class='info'>$img<span>$time</span><b>$title</b>: $text</div>";
  }

  function pw_csv_ui_deltable($table, $destroy) {

    if (!$file = pw_csv_fullpath($table)) return pw_csv_ui_log("error", "Tabelle l&ouml;schen", "Die Tabelle '$table' existiert nicht mehr.");

    if ($destroy == "true" or $destroy == "on") {
      if (pw_csv_del_table($table, DELETE_TABLE))
        return pw_csv_ui_log ("info", "Tabelle l&ouml;schen", "Die Tabelle '$table' wurde gel&ouml;scht.");
      else
        return pw_csv_ui_log ("error", "Tabelle l&ouml;schen", "Die Tabelle '$table' konnte nicht gel&ouml;scht werden.");
    }
    if ($destroy == "false" or $destroy == "off" or $destroy == "") {

      pw_trash_delete($file);

      return pw_csv_ui_log ("info", "Tabelle l&ouml;schen", "Die Tabelle '$table' wurde in den Papierkorb verschoben.");
    }

    return pw_csv_ui_log ("error", "Tabelle l&ouml;schen", "Unbekannter Fehler.");
  }

  function pw_csv_ui_edittable($table, $method, $fixed) {
    echo "<input type='button' onclick='var cont = document.getElementById(\"tablecontent\").value; cont = Url.encode(cont); pw_ajax_call2(\"ajax_log\", \"func=savetable&table=$table&tablecontent=\" + cont, true); listtables_changed(); return false' value='Speichern'>";
    echo "<textarea id='tablecontent' style='width: 980px; height: 300px;' wrap='off'>";
    echo pw_csv_getfile($table, $method);
    echo "</textarea>";
  }

  function pw_csv_ui_savetable($table, $content) {
    #var_dump($content);
    $ret = pw_csv_savefile($table, $content);
    if ($ret === false)
      return pw_csv_ui_log("error", "Tabelle speichern", "Die Tabelle '$table' konnte NICHT gespeichert werden.");

    $ret = formatBytes($ret);
    return pw_csv_ui_log("info", "Tabelle speichern", "Die Tabelle '$table' wurde gespeichert ($ret).");
  }

  function pw_editmaintitle_save($titel) {

    return "Haupttitel <b>$titel</b> ge&auml;ndert!";
  }


  function pw_trash_delete($filename) {

  }

    #pw_csv_init("lib/tests");
    #pw_debug_init(false);
    #var_dump($_GET);
    #sleep(1);
    switch ($_GET['func']) {
      case "editmaintitle": echo pw_editmaintitle_save($_GET['maintitle']); break;
      case "showcontent":
        $file_name = strtolower(str_replace(":", "/", $_GET['id']));

        if (!file_exists("../dat/$file_name.txt")) {
          $file_name = "notfound";
        }

        echo $file_name;
        $data = file_get_contents("../dat/$file_name.txt");
        $data = utf8_decode($data);

        lexerconf($data);
      break;

      /*
      case "showtable": echo pw_csv_ui_showtable($_GET['table']); break;
      case "showinfo":
        if (!pw_csv_ui_showinfo($_GET['table']))
          echo "<div class='ajax_info'><div><img src='../icons/error.png' /><span>Die Tabelle '$_GET[table]' ist fehlerhaft!</span></div></div>";
      break;
      case "showtoolbar": echo pw_csv_ui_showtoolbar($_GET['tbid']); break;
      case "showdeltable": echo pw_csv_ui_showdeltable($_GET['table']); break;
      case "deltable": echo pw_csv_ui_deltable($_GET['table'], $_GET['destroy_table']); break;
      case "edittable": echo pw_csv_ui_edittable($_GET['table'], $_GET['editmethode'], $_GET['editmethodefixed']); break;
      case "savetable": echo pw_csv_ui_savetable($_GET['table'], $_GET['tablecontent']); break;
      */
    }

?>