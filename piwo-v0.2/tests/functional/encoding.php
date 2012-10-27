<?php

if (!defined('INC_PATH')) {
  define ('INC_PATH', '../../');
}

require_once INC_PATH."/bin/testing.php";

die("This file is not compatible with the PiWo Testroutines!!!");

iconv_set_encoding("internal_encoding", "UTF-8");
iconv_set_encoding("output_encoding", "UTF-8");
$loc_de = setlocale(LC_ALL, 'de_DE.UTF8', 'de_DE@euro', 'de_DE', 'deu_deu', 'de_DE.UTF8');
#setlocale(LC_ALL, 'de_DE.UTF-8');

function getlocale($category) {
        return setlocale($category, NULL);
}




html_header();
echo "LOCALE = '$loc_de'";
#o(getlocale(LC_ALL));

function o($t, $i = null) {
  echo "<pre>";
  if ($i) echo "$i: ";
  var_dump($t);
  echo "</pre>";
}

function tests($t) {
  $c = utf8_check($t);
  o(pw_s2e($t), "INPUT");
  o($c, "UTF8-CHECK");
  $urle = urlencode($t);
  o(pw_s2e($urle), "URLENCODE");
  o(pw_s2e($x = pw_s2url($t)), "PW_URLENCODE");
  o(pw_s2e(pw_url2u($x)), "PW_URLDECODE");
  $urld = urldecode($urle);
  o(pw_s2e($urld), "URLDECODE");
  $e = utf8_encode($t);
  o(pw_s2e($e), "ENCODE");
  $d = utf8_decode($e);
  o(pw_s2e($d), "DECODE");
  o(pw_s2e(strtolower($t)), "STRTOLOWER");
  o(pw_s2e(utf8_strtolower(pw_s2u($t))), "UTF8_STRTOLOWER");
  o(pw_s2e(pw_wiki_pg($t)), "PW_WIKI_PG");
  o(pw_s2e(pw_wiki_ns($t)), "PW_WIKI_NS");

  o($e = pw_url2e($x), "URL2E");
  o(pw_e2url($e), "E2URL");
  o(pw_s2e($t));
}

function wikitests($t) {
  $c = utf8_check($t);
  o(pw_s2e($t), "INPUT");
  o($c, "UTF8-CHECK");

  for ($i = 0; $i <= 7; $i++) {
    o(pw_s2e(pw_wiki_path($t, $i)), "WIKIPATH: Cfg=$i");
  }
}


$t = isset($_GET['t']) ? $_GET['t'] : "Tests:sonderzeichen öp:S";

echo "<h1>UTF8-String</h1>";
tests($t);

echo "<h1>Ascii-String</h1>";
tests(utf8_decode($t));

echo "<h1>Wiki</h1>";
wikitests($t);

#echo "<h1>URL-String (Ascii) (OE = %D6; oe = %f6)</h1>";
#tests(urlencode(utf8_decode($t)));


html_footer(false);

?>