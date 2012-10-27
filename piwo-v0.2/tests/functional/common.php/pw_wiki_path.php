<?php

// Vorlage
/*

$idX_expres = array(
   $storage."".$ext,
   $storage."",
   $storage."",
   "".$ext,
   "",
   "",
   "".$ext,
   "",
   ""
);

*/

if (!defined('INC_PATH')) {
  define ('INC_PATH', '../../../');
  #define('INC_PATH',realpath(dirname(__FILE__).'/').'/');
}

require_once INC_PATH."/bin/testing.php";

$storage = pw_wiki_getcfg('storage');
$ext = pw_wiki_getcfg('fileext');

if (!is_string($storage) or $storage == "" and !is_string($ext) or $ext == "") {
  die("Voraussetzungen für Test nicht erfüllt.");
}

$dependencies = array("pw_dirname");


// INPUT...
$ids = array();
$ids[1] = "dokumentation:todos:v0.01";   // Einfache ID (relativ)
$ids[2] = "dok:Archäologie:Seite3";// Sonderzeichen (utf8)
$ids[3] = ":tests:ID Tests";       // Absolute Pfadangabe
$ids[4] = "h1:h2:..:..:zwei Ebenen zurück";    // Sprungbefehle
$ids[5] = ":..:hallo:";
$ids[6] = ":*::hallo";
$ids[7] = ":Äffchen:Fritz";
#$ids[8] = "dokumentation:Textgestaltung:Überschriften";

// Erwartete Ergebnisse... (expected results)
// Achtung: Pfadangaben werden in 'cfg/main.php' festgelegt.
$ids_expres = array();

$ids_expres[1] = array(
  $storage."/dokumentation/todos/v0.01".$ext, // ST_FULL
  $storage."/dokumentation/todos/",     // ST_SHORT
  $storage."/dokumentation/todos/v0.01",// ST_NOEXT
  "dokumentation/todos/v0.01".$ext,     // FULL
  "dokumentation/todos/",               // SHORT
  "dokumentation/todos/v0.01",          // NOEXT
  "v0.01".$ext,                         // FNAME
  "v0.01",                              // FNAME_NOEXT
  "todos"                               // DNAME
);

$ids_expres[2] = array(
   $storage."/dok/archäologie/seite3".$ext,
   $storage."/dok/archäologie/",
   $storage."/dok/archäologie/seite3",
   "dok/archäologie/seite3".$ext,
   "dok/archäologie/",
   "dok/archäologie/seite3",
   "seite3".$ext,
   "seite3",
   "archäologie"
);

$ids_expres[3] = array(
   $storage."/tests/id tests".$ext,
   $storage."/tests/",
   $storage."/tests/id tests",
   "/tests/id tests".$ext,
   "/tests/",
   "/tests/id tests",
   "id tests".$ext,
   "id tests",
   "tests"
);

$ids_expres[4] = array(
   $storage."/zwei ebenen zurück".$ext,
   $storage."/",
   $storage."/zwei ebenen zurück",
   "zwei ebenen zurück".$ext,
   "",
   "zwei ebenen zurück",
   "zwei ebenen zurück".$ext,
   "zwei ebenen zurück",
   ""
);

$ids_expres[5] = array(
   $storage."/hallo/",
   $storage."/hallo/",
   $storage."/hallo/",
   "/hallo/",
   "/hallo/",
   "/hallo/",
   "",
   "",
   "hallo"
);

$ids_expres[6] = array(
   false,
   false,
   false,
   false,
   false,
   false,
   false,
   false,
   false
);

$ids_expres[7] = array(
   $storage."/äffchen/fritz".$ext,
   $storage."/äffchen/",
   $storage."/äffchen/fritz",
   "/äffchen/fritz".$ext,
   "/äffchen/",
   "/äffchen/fritz",
   "fritz".$ext,
   "fritz",
   "äffchen"
);

$ids_expres[8] = array(
   $storage."/hallo/",
   $storage."/hallo/",
   $storage."/hallo/",
   "/hallo/",
   "/hallo/",
   "/hallo/",
   "",
   "",
   "hallo"
);

$ids_res = array();
$ids_res[1] = test_pw_wiki_path($ids[1]);
$ids_res[2] = test_pw_wiki_path($ids[2]);
$ids_res[3] = test_pw_wiki_path($ids[3]);
$ids_res[4] = test_pw_wiki_path($ids[4]);
$ids_res[5] = test_pw_wiki_path($ids[5]);
$ids_res[6] = test_pw_wiki_path($ids[6]);
$ids_res[7] = test_pw_wiki_path($ids[7]);

testing_html_header('pw_wiki_path');

$errors = test_counterrors_pw_wiki_path($ids_res, $ids_expres);
$tests = count($ids) * 8;

test_overview_output("pw_wiki_path", $tests, $errors);

echo "<pre>";
#echo "VIELLEICHT BESSER EINE FUNKTION MIT EINFACHEM PFAD-HANDLING. OHNE STORAGE... (cleanfilename) WEGEN ABH&Auml;NGIGKEITEN!\n";
echo "Storage: $storage\nExtension: $ext\nDependencies: pw_dirname\n";
echo "Parameterlist (look for the #-column in tables):
0 = ST_FULL
1 = ST_SHORT
2 = ST_NOEXT
3 = FULL
4 = SHORT
5 = NOEXT
6 = FNAME
7 = FNAME_NOEXT
8 = DNAME";
echo "</pre>";


////////////////////////////////////////////////////
// TESTING pw_wiki_path...
function test_pw_wiki_path($id) {
  $res = array();
  for ($i = 0; $i <= 8; $i++) {
    $res[$i] = pw_wiki_path($id, $i);

  }

  return $res;
}

function test_counterrors_pw_wiki_path($ids_res, $ids_expres) {

  $counterrors = true;
  $errors = 0;

  test_compare($ids_expres[1], $ids_res[1], $errors);
  test_compare($ids_expres[2], $ids_res[2], $errors);
  test_compare($ids_expres[3], $ids_res[3], $errors);
  test_compare($ids_expres[4], $ids_res[4], $errors);
  test_compare($ids_expres[5], $ids_res[5], $errors);
  test_compare($ids_expres[6], $ids_res[6], $errors);
  test_compare($ids_expres[7], $ids_res[7], $errors);

  return $errors;

}

echo "<h2>Input: ".pw_s2e($ids[1])."</h2>";
test_output($ids[1], $ids_expres[1], $ids_res[1]);

echo "<h2>Input: ".pw_s2e($ids[2])." (utf-8 encoded)</h2>";
#echo "FIXME:::Vergleich scheitert: utf8-Codierung-Problem in der Testroutine!!!";
test_output($ids[2], $ids_expres[2], $ids_res[2]);

echo "<h2>Input: ".pw_s2e($ids[3])." (absolute)</h2>";
test_output($ids[3], $ids_expres[3], $ids_res[3]);

echo "<h2>Input: ".pw_s2e($ids[4])." (absolute + ..)</h2>";
test_output($ids[4], $ids_expres[4], $ids_res[4]);

echo "<h2>Input: ".pw_s2e($ids[5])."</h2>";
test_output($ids[5], $ids_expres[5], $ids_res[5]);

echo "<h2>Input: ".pw_s2e($ids[6])." (This characters are not allowed: *?\)</h2>";
test_output($ids[6], $ids_expres[6], $ids_res[6]);

echo "<h2>Input: ".pw_s2e($ids[7])." (make utf-8 encoded symbols lowercase)</h2>";
test_output($ids[7], $ids_expres[7], $ids_res[7]);

testing_html_footer();

?>