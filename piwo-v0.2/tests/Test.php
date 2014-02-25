<?php
if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}

require_once INC_PATH.'piwo-v0.2/lib/lexerconf.php';
TestingTools::logOn();
TestingTools::debugOn();

$input = "* ListItem\n$$ pre-block-Text";
$expected = "<ul><li>ListItem</li></ul><pre><div>pre-block-Text</div></pre>";
$result = parse($input);

echo StringTools::preFormat("RESULT: Code");
echo StringTools::preFormatShowLineNumbers(pw_s2e($result));
echo StringTools::preFormat("RESULT: Output");
echo $result;

echo StringTools::preFormat("EXPECTED: Code");
echo StringTools::preFormatShowLineNumbers(pw_s2e($expected));
echo StringTools::preFormat("EXPECTED: Output");
echo $expected;

echo "<hr />";
echo StringTools::preFormat("DEBUG:\n".pw_s2e(TestingTools::getLog()->toStringReversed()));

?>