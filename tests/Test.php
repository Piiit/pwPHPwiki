<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', true);


if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}

require_once INC_PATH.'piwo-v0.2/lib/WikiParser.php';
require_once INC_PATH.'pwTools/string/Diff.php';

TestingTools::logOn();

TestingTools::debugOn();

$pathToTokens = INC_PATH."piwo-v0.2/lib/tokens";
$pathToPlugins = INC_PATH."piwo-v0.2/lib/plugins";
$wikiParser = new WikiParser($pathToTokens, $pathToPlugins);

$input = "~~TOC~~\n= h1 =\n= h2 =";
$expected = '<div class="toc" id="__toc"><ul><li><a href="#header_0">1 h1</a></li><li><a href="#header_1">2 h2</a></li></ul></div><h1 id="header_0">h1</h1><h1 id="header_1">h2</h1>';

// $input = "= h1 =\n== h1.1 ==\n= h2 =";
// $expected = '<h1 id="header_0">h1</h1><h2 id="header_1">h1.1</h2><h1 id="header_2">h2</h1>';
// $input = "[[Überschriften]]";
// $expected = '<a href="?id=:%fcberschriften&mode=edit" class="pw_wiki_link_na">&Uuml;berschriften</a>';

$wikiParser->parse($input);
$result = $wikiParser->getResult();
echo "INPUT:\n";
echo StringTools::preFormatShowLineNumbers(pw_s2e($input));
echo StringTools::preFormatShowLineNumbers("RESULT  : ".pw_s2e($result));
echo StringTools::preFormatShowLineNumbers("EXPECTED: ".pw_s2e($expected));
$diff = StringTools::deleteUntilDiff($result, $expected);
$tt = Diff::compare($expected, $result, true);

for($i = 0; i < sizeof($tt); $i++) {
	if($tt[$i][1] == 0) {
		echo $tt[$i][0];
	}
	if($tt[$i][1] == 1) {
		echo "<b>".$tt[$i][0]."</b>";
	}
}

var_dump($tt);
echo Diff::toString($tt, '');

if(strlen($diff) == 0) {
	echo "<b>TEST SUCCEEDED: NO DIFF!</b>";
} else {
	echo StringTools::preFormatShowLineNumbers("DIFF    : ".pw_s2e($diff));
}

echo StringTools::preFormat("RESULT: Output");
echo $result;

echo StringTools::preFormat("EXPECTED: Output");
echo $expected;


echo "<hr />";
echo StringTools::preFormat("DEBUG:\n".pw_s2e(TestingTools::getLog()->toStringReversed()));

?>