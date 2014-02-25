<?php
if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}

require_once INC_PATH.'piwo-v0.2/lib/lexerconf.php';
require_once 'PHPUnit/Framework/TestCase.php';

class SyntaxTests extends PHPUnit_Framework_TestCase {
	
	protected function setUp() {
		parent::setUp ();
	}
	
	protected function tearDown() {
		parent::tearDown ();
	}
	
	public function __construct() {
	}
	
	public function testOrderedList01() {
		$input = "# hallo";
		$expected = "<ol><li>hallo</li></ol>";
		$result = parse($input);
		$this->assertEquals($expected, $result, "RES: $result; EXP: $expected; INP: $input;");
	}	
	
	
}

