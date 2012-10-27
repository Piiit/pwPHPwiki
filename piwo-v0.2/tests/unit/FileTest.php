<?php


require_once 'lib/File.php';
require_once 'PHPUnit\Framework\TestCase.php';


/**
 * File test case.
 */
class FileTest extends PHPUnit_Framework_TestCase {
	
	private $_file;
	private $_dir;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		
		chdir(__DIR__);
		
		
		#if (file_exists("001/f01")) unlink("001/f01");
		#if (file_exists("001/d01")) rmdir("001/d01");
		#if (file_exists("001")) rmdir("001");
		
		if (!file_exists("001")) mkdir("001");
		if (!file_exists("001/d01")) mkdir("001/d01");
		if (!file_exists("001/f01")) touch("001/f01");
		
		#if (!touch("001/f01")) {
		#	$this->fail("INIT FAILED");
		#}
		
		#mkdir("_0002");
		#$this->fail(realpath(dirname(__DIR__)));
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated FileTest::tearDown()
		$this->_file = null;
		$this->_dir = null;
		
		#rmdir("001/d01");
		#unlink("001/f02");
		#rmdir("001");
		
		parent::tearDown ();
	}
	
	/**
	 * Constructs the test case.
	 */
	public function __construct() {
		
	}
	
	/**
	 * Tests File->__construct()
	 */
	public function test__construct() {
		#$this->_file = new File("001/f01");
		#$this->_dir = new File("001");
	}
	
	/**
	 * Tests File->rename()
	 */
	public function testRename() {
		$this->_file = new File("001/f01");
		$this->_file->rename("f01.txt");
		$this->assertTrue(file_exists($this->_file));
	}
	
	/**
	 * Tests File->isDirectory()
	 */
	public function testIsDirectory() {
		// TODO Auto-generated FileTest->testIsDirectory()
		$this->markTestIncomplete ( "isDirectory test not implemented" );
		
		$this->_file->isDirectory(/* parameters */);
	}
}

