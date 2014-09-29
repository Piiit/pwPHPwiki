<?php
if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/lexerconf.php';
require_once 'PHPUnit/Framework/TestCase.php';

class TestsSyntax extends PHPUnit_Framework_TestCase {
	
	protected function setUp() {
		parent::setUp ();
	}
	
	protected function tearDown() {
		parent::tearDown ();
	}
	
	public function __construct() {
	}
	
	public function testListsOrdered01() {
		$input = "# hallo";
		$expected = "<ol><li>hallo</li></ol>";
		$result = parse($input);
		$this->assertEquals($expected, $result, "RES: $result;\n EXP: $expected; INPUT: $input;");
	}	
	
	public function testListsOrdered02() {
		$input = "# hallo\n# item 2";
		$expected = "<ol><li>hallo</li><li>item 2</li></ol>";
		$result = parse($input);
		$this->assertEquals($expected, $result, "RES: $result;\n EXP: $expected; INPUT: $input;");
	}
	
	public function testListsOrdered03() {
		$input = "# hallo\n  # item 2";
		$expected = "<ol><li>hallo</li><ol><li>item 2</li></ol></ol>";
		$result = parse($input);
		$this->assertEquals($expected, $result, "RES: $result;\n EXP: $expected; INPUT: $input;");
	}

	public function testListsOrdered04() {
		$input = "# hallo\n  # item 2\n# hallo2";
		$expected = "<ol><li>hallo</li><ol><li>item 2</li></ol><li>hallo2</li></ol>";
		$result = parse($input);
		$this->assertEquals($expected, $result, "RES: $result;\n EXP: $expected; INPUT: $input;");
	}

	public function testListsMixed01() {
		$input = "# hallo\n  * item 2\n# hallo2";
		$expected = "<ol><li>hallo</li><ul><li>item 2</li></ul><li>hallo2</li></ol>";
		$result = parse($input);
		$this->assertEquals($expected, $result, "RES: $result;\n EXP: $expected; INPUT: $input;");
	}
	
	public function testPreBlock01() {
		$input = "$$ test\n= title =";
		$expected = "<pre><div> test\n</div></pre><h1 id=\"header_0\">title</h1>";
		$result = parse($input);
		$this->assertEquals($expected, $result, "RES: $result;\n EXP: $expected; DIFF: ".StringTools::deleteUntilDiff($result, $expected)."INPUT: $input;");
	}
	
	public function testPreBlockAttachedToListitems() {
		$input = "* ListItem\n$$ pre-block-Text";
		$expected = "<ul><li>ListItem</li></ul><pre><div> pre-block-Text\n</div></pre>";
		$result = parse($input);
		$this->assertEquals($expected, $result, "RES: $result;\n EXP: $expected; DIFF: ".StringTools::deleteUntilDiff($result, $expected)."INPUT: $input;");
	}
	
	public function testInternalLinkSpecialChars() {
		$input = "[[Überschriften]]";
		$expected = '<a href="?id=:%fcberschriften&mode=edit" class="pw_wiki_link_na">&Uuml;berschriften</a>';
		$result = parse($input);
		$this->assertEquals($expected, $result, "RES: $result;\n EXP: $expected; DIFF: ".StringTools::deleteUntilDiff($result, $expected)."INPUT: $input;");
	}

	public function testIndexTable() {
		$input = "= h1 =\n= h2 =";
		$expected = '<h1 id="header_0">h1</h1>';
		$result = parse($input);
		$this->assertEquals($expected, $result, "RES: $result;\n EXP: $expected; DIFF: ".StringTools::deleteUntilDiff($result, $expected)."INPUT: $input;");
	}
	
	
	
} 

