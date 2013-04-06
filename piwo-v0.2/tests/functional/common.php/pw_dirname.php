<?php

if (!defined('INC_PATH')) {
  define ('INC_PATH', realpath(dirname(__FILE__).'/../../../../').'/');
}

require_once INC_PATH."piwo-v0.2/lib/testing.php";
require_once INC_PATH."piwo-v0.2/lib/common.php";


// Input...
$filenames = array(
  "dat/dok/todos/v0.01.txt",
  "dat/dok/todos/",
  "dat/dok/todos",
  "צהü/SOnderzeichen/ײה*",    // Error: Symbols like *?\ are not allowed!
  "////hallo//du/",
  "start/../anfang/",
  "/../../",
  "/ִhm/",
  "1/2/3/../../Hallo.php",
  "dat/h1/h2/../../zwei ebenen zurück.txt",
  "dat/tests/x/",
  "/",
  "hallo/../zwei.txt"
);

// Erwartetes Ergebnis...
$expres = array(
  "dat/dok/todos/",
  "dat/dok/todos/",
  "dat/dok/",
  false,
  "/hallo/du/",
  "anfang/",
  "/",
  "/הhm/",
  "1/",
  "dat/",
  "dat/tests/x/",
  "/",
  ""
);

// Expected Results with 2nd parameter set to true (singleDirectory)
$expres2 = array(
  "todos",
  "todos",
  "dok",
  false,
  "du",
  "anfang",
  "",
  "הhm",
  "1",
  "dat",
  "x",
  "",
  ""
);

function test_pw_dirname($filenames, $single) {
  $res = array();
  foreach ($filenames as $i => $filename) {
    $res[$i] = pw_dirname($filename, $single);
  }

  return $res;
}

testing_html_header('pw_dirname');

$errors = 0;
$errors2 = 0;
$res = test_pw_dirname($filenames, false);
$res2 = test_pw_dirname($filenames, true);

if (test_compare($expres, $res, $errors) and test_compare($expres2, $res2, $errors2)) {
  test_overview_output("pw_dirname", count($filenames)*2, $errors+$errors2);
  test_output($filenames, $expres, $res);
  test_output($filenames, $expres2, $res2);
} else {
  test_overview_output("pw_dirname", null, null);
}

testing_html_footer();
?>