<?php

if (!defined('INC_PATH')) {
  define ('INC_PATH', realpath(dirname(__FILE__).'/../../../../').'/');
}

require_once INC_PATH."piwo-v0.2/lib/testing.php";
require_once INC_PATH."piwo-v0.2/lib/common.php";
require_once INC_PATH."pwTools/debug/TestingTools.php";

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

TestingTools::init();
TestingTools::debugOn();

$test = new pwTest("pw_dirname4");
$test->addInput($filenames, $expres);
$test->addInput($filenames, $expres2);

$inputs = $test->getInputs();

TestingTools::debug($inputs);
die();

function test_pw_dirname($filenames, $single) {
  $res = array();
  foreach ($filenames as $i => $filename) {
    $res[$i] = pw_dirname($filename, $single);
  }

  return $res;
}

$res = test_pw_dirname($filenames, false);
$res2 = test_pw_dirname($filenames, true);

testing_html_header('pw_dirname2');

pw_test($filenames, $res, $expres);
pw_test($filenames, $res2, $expres2);

testing_html_footer();

?>