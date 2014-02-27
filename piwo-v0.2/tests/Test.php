<?php
if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}

require_once INC_PATH.'piwo-v0.2/lib/WikiParser.php';
TestingTools::logOn();
//TestingTools::debugOn();

$wikiParser = new WikiParser();

$input = "[[Überschriften]]";
$expected = '<a href="?id=:%fcberschriften&mode=edit" class="pw_wiki_link_na">&Uuml;berschriften</a>';
$wikiParser->parse($input);
$result = $wikiParser->getResult();

echo StringTools::preFormat("RESULT: Code");
echo StringTools::preFormatShowLineNumbers(pw_s2e($result));
echo StringTools::preFormat("RESULT: Output");
echo $result;

echo StringTools::preFormat("EXPECTED: Code");
echo StringTools::preFormatShowLineNumbers(pw_s2e($expected));
echo StringTools::preFormat("EXPECTED: Output");
echo $expected;

echo StringTools::preFormat("DIFF: Code");
$diff = StringTools::deleteUntilDiff($result, $expected);
if(strlen($diff) == 0) {
	echo "TEST SUCCEEDED: NO DIFF!";
} else {
	echo StringTools::preFormatShowLineNumbers(pw_s2e($diff));
}

echo "<hr />";
echo StringTools::preFormat("DEBUG:\n".pw_s2e(TestingTools::getLog()->toStringReversed()));

?>