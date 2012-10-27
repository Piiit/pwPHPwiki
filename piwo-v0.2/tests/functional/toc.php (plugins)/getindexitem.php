<?php

if (!defined('INC_PATH')) {
  define ('INC_PATH', '../../../');
  #define('INC_PATH',realpath(dirname(__FILE__).'/').'/');
}

session_start();

require_once INC_PATH."/bin/testing.php";
require_once INC_PATH."/lib/pw_debug.php";
require_once INC_PATH."/lib/pw_lexer.php";
require_once INC_PATH."/bin/lexerconf.php";
require_once INC_PATH."/bin/parserconf.php";
require_once INC_PATH."/plugins/toc.php";

$text = "
= Hallo =

== U 1.1 ==
== U 1.2 ==
Text Text Text...
=== U 1.2.1 ÄÖÜ ===
== U 1.3 ==

== {{lexerversion}} ==

";
$debugmode = true;

$lexer = new pwLexer($text, $debugmode);
$lexer = pw_wiki_lexerconf($lexer);

// Parse (ignore CData entries...)
$lexer->parse(false);

echo $lexer->getAST();

createindextable($lexer);
out2("INDEXTABLE = ");
out($GLOBALS['indextable']);

$idxtable = getindextable();

// Inputs... Ids = 1, 1.2.1, 6.4.2.1 (chapter-numbers, c1.c2.c3.c4.c5)
$ids = array (
  "1",
  "0",
  "1.3",
  "Hallo",
  "U 1.2.1",
  "1.2.1",
  " 1. 2. 1",
  "  1.2",
  "1.4"
);

// Expected results...
$expres1 = array (
  "Hallo",
  null,
  "U 1.3",
  null,
  null,
  "U 1.2.1 ÄÖÜ",
  null,
  "U 1.2",
  "0.42d - PIWO"
);

// Inputs... Names = Trimmed Texts inside the header-boundaries -> = TEXT =
$names = array (
  "Hallo",
  "U 1.1",
  "U 1.2.1 ÄÖÜ",
  "nitdo"
);

$expres2 = array (
  "Hallo",
  "U 1.1",
  "U 1.2.1 ÄÖÜ",
  null

);


function test_getindexitem($ids, $searchids = true) {
  $res = array();
  foreach ($ids as $i => $id) {
    $r = getindexitem($id, $searchids);
    $res[$i] = $r["TEXT"];
  }

  return $res;
}


testing_html_header('getindexitem');

$res1 = test_getindexitem($ids);
$res2 = test_getindexitem($names, false);

if (test_compare($expres1, $res1, $errors) and test_compare($expres2, $res2, $errors)) {
  test_overview_output("getindexitem", count($ids)+count($names), $errors);
} else {
  test_overview_output("getindexitem", null, null);
}

echo "
<pre>
This is the documenttext:
".pw_s2e($text)."
</pre>";


#echo( $lexer->getText(null) );

#out($res);


echo "<h2>Parameter #2 = true (search by IDs)</h2>";
test_output($ids, $expres1, $res1);



echo "<h2>Parameter #2 = false (search by NAMEs)</h2>";
test_output($names, $expres2, $res2);

testing_html_footer();

?>