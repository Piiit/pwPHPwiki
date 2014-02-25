<?php
if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}

require_once INC_PATH.'piwo-v0.2/lib/lexerconf.php';
TestingTools::logOn();

$input = "# hallo\n  * item 2\n# hallo2";
$expected = "<ol><li>hallo</li><ul><li>item 2</li></ul><li>hallo2</li></ol>";
$result = parse($input);

echo pw_s2e($result);
echo "<br />";
echo pw_s2e($expected);

echo $result;
echo "<br />";
echo $expected;

echo StringTools::preFormat(pw_s2e(TestingTools::getLog()->toStringReversed()));

?>