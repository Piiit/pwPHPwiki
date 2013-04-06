<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH."pwTools/string/utf8.php";
require_once INC_PATH."pwTools/string/encoding.php";
require_once INC_PATH."piwo-v0.2/lib/common.php";
require_once INC_PATH."piwo-v0.2/lib/pw_debug.php";

class pwTest {
  protected $testName = "";
  protected $inputArray = array();
  protected $expectedResultsArray = array();
  protected $resultsArray = array();
  protected $errors = -1;
  protected $testRoutine = "";
  protected $testParameter = array();
  protected $config = "";

  public function __construct($name) {
    $this->testName = $name;
	if ($this->readConfigFile()) {
		$this->parseConfigFile();
	}
	return false;
	
  }

  public function addInput($input, $expResult) {
    array_push($this->inputArray, $input);
    array_push($this->expectedResultsArray, $expResult);
  }

  public function addTestRoutine($testR, $param) {
    $this->testRoutine = $testR;
    $this->testParameter = $param;
  }
  
  public function getInputs() {
	return $this->inputArray;
  }

  public function runTest() {
    call_user_func($this->testRoutine);
  }
  
  private function readConfigFile($filename) {
  	$c = file_get_contents($filename);
	if ($c) {
		$this->config = $c;
		return true;
	}
	return false;
  }
  
  private function parseConfigFile() {
  }

}


function test_dependencies($name, $dependencies = array()) {

}

function test_overview_output($name, $countedtests, $countederrors) {

  $info = "";
  if ($countederrors === false or $countederrors === null) {
    $info = "test_overview_output failed.";
    $countederrors = 1;
  }

  $c2 = ' passed';
  if ($countederrors > 0) {
    $c2 = ' failed';
  }

  echo "<div class='info$c2'>";
  echo "<h1>Overview: $name</h1>";
  echo "<p>$info</p>";
  if ($info == "") {
    echo "<dl><dt>TESTS</dt><dd>$countedtests</dd><dt>FAILED</dt><dd>$countederrors</dd><dt>PASSED</dt><dd>".($countedtests-$countederrors)."</dd></dl>";
  }
  echo "</div>";
}

function test_compare($expres, $res, &$errors = -1) {

  if (count($res) != count($expres) or !is_array($expres) or !is_array($res)) {
    return false;
    #return -1;
  }

  if ($errors == -1) {
    $errors = 0;
  }
  #$errors = 0;

  $ret = array();
  foreach ($res as $i => $sres) {
    if (is_string($res[$i]) and is_string($expres[$i])) {
      $res[$i] = pw_s2u($res[$i]);
      $expres[$i] = pw_s2u($expres[$i]);
    }

    if ($res[$i] === $expres[$i]) {
      $ret[$i] = true;
    } else {
      $ret[$i] = false;
      $errors++;
    }
  }
  return $ret;
}

function test_valueout($value) {
  if (is_bool($value)) {
    return $value ? '<i>[true]</i>' : '<i>[false]</i>';
  }

  if (is_null($value)) {
    return '<i>[null]</i>';
  }

  return pw_s2e($value);
}

function test_output($input, $expres, $res, &$errors = -1) {

  $cmpres = test_compare($expres, $res, $errors);
  if (!$cmpres) {
    echo "Der Test hat weniger Ergebnisse geliefert als vorgesehen. Es müssen gleich viele Inputdaten, wie 'erwartete Ergebnisse' vorliegen. ";
    return;
  }

  echo "<pre>TESTS = ".count($res)."; FAILED = $errors; PASSED = ".(count($res)-$errors)."</pre>";
  echo "<table>";
  echo "<tr><th>#</th><th>Input</th><th>Output</th><th>Expected</th><th>Test result</th></tr>";

  foreach ($expres as $i => $value) {
    $hi = $cmpres[$i] ? " class='passed'" : " class='failed'";
    echo "<tr$hi>";
    echo "<td>$i</td><td>";
    echo (is_array($input) ? pw_s2e($input[$i]) : pw_s2e($input))."</td>";
    echo "<td>".test_valueout($res[$i])."</td>";
    echo "<td>".test_valueout($value)."</td><td>".($cmpres[$i] ? 'PASSED' : 'FAILED')."</td>";
    echo "</tr>";
  }

  echo "</table>";
}

function testing_html_header($title) {
  pw_ne ('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
  pw_ne ('<html>', START);
  pw_ne ('<head>', START);
  pw_ne ("<title>piwo: UNIT-TESTING: $title</title>");
  pw_ne ('<meta http-equiv="Content-Type" content="text/html" charset="utf-8">');
  pw_ne ('<link rel="stylesheet" type="text/css" media="screen" href="'.INC_PATH.'testing.css">');
  pw_ne ('</head>', END);
  pw_ne ('<body>', START);
  pw_ne ("<h1>Test: $title</h1>");
}

function testing_html_footer() {
  pw_ne ('</body>', END);
  pw_ne ('</html>', END);
}

function pw_test($inputs, $results, $exp_results) {

  $errors = 0;

  if (test_compare($exp_results, $results, $errors)) {
    test_overview_output("", count($inputs), $errors);
    test_output($inputs, $exp_results, $results);
  } else {
    test_overview_output("", null, null);
  }

}

function pw_test_array($inputarr, $resultarr, $exp_resultarr) {

}

?>