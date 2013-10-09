<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH.'pwTools/file/FileTools.php';
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * FileTools test case.
 */
class FileToolsTest extends PHPUnit_Framework_TestCase {
	
	/**
	 *
	 * @var FileTools
	 */
	private $FileTools;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		
		// TODO Auto-generated FileToolsTest::setUp()
		
		$this->FileTools = new FileTools(/* parameters */);
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated FileToolsTest::tearDown()
		$this->FileTools = null;
		
		parent::tearDown ();
	}
	
	/**
	 * Tests FileTools::createFolderIfNotExist()
	 */
	public function testCreateFolderIfNotExist() {
		// TODO Auto-generated FileToolsTest::testCreateFolderIfNotExist()
		$this->markTestIncomplete ( "createFolderIfNotExist test not implemented" );
		
		FileTools::createFolderIfNotExist(/* parameters */);
	}
	
	/**
	 * Tests FileTools::copyFileIfNotExist()
	 */
	public function testCopyFileIfNotExist() {
		// TODO Auto-generated FileToolsTest::testCopyFileIfNotExist()
		$this->markTestIncomplete ( "copyFileIfNotExist test not implemented" );
		
		FileTools::copyFileIfNotExist(/* parameters */);
	}
	
	/**
	 * Tests FileTools::copyMultipleFilesIfNotExist()
	 */
	public function testCopyMultipleFilesIfNotExist() {
		// TODO Auto-generated FileToolsTest::testCopyMultipleFilesIfNotExist()
		$this->markTestIncomplete ( "copyMultipleFilesIfNotExist test not implemented" );
		
		FileTools::copyMultipleFilesIfNotExist(/* parameters */);
	}
	
	/**
	 * Tests FileTools::getUnixFilePermission()
	 */
	public function testGetUnixFilePermission() {
		// TODO Auto-generated FileToolsTest::testGetUnixFilePermission()
		$this->markTestIncomplete ( "getUnixFilePermission test not implemented" );
		
		FileTools::getUnixFilePermission(/* parameters */);
	}
	
	/**
	 * Tests FileTools::removeDirectory()
	 */
	public function testRemoveDirectory() {
		// TODO Auto-generated FileToolsTest::testRemoveDirectory()
		$this->markTestIncomplete ( "removeDirectory test not implemented" );
		
		FileTools::removeDirectory(/* parameters */);
	}
	
	/**
	 * Tests FileTools::removeFile()
	 */
	public function testRemoveFile() {
		// TODO Auto-generated FileToolsTest::testRemoveFile()
		$this->markTestIncomplete ( "removeFile test not implemented" );
		
		FileTools::removeFile(/* parameters */);
	}
	
	/**
	 * Tests FileTools::getTextFileFormat()
	 */
	public function testGetTextFileFormat() {
		// TODO Auto-generated FileToolsTest::testGetTextFileFormat()
		$this->markTestIncomplete ( "getTextFileFormat test not implemented" );
		
		FileTools::getTextFileFormat(/* parameters */);
	}
	
	/**
	 * Tests FileTools::setTextFileFormat()
	 */
	public function testSetTextFileFormat() {
		// TODO Auto-generated FileToolsTest::testSetTextFileFormat()
		$this->markTestIncomplete ( "setTextFileFormat test not implemented" );
		
		FileTools::setTextFileFormat(/* parameters */);
	}
	
	/**
	 * Tests FileTools::basename()
	 */
	public function testBasename() {
		// TODO Auto-generated FileToolsTest::testBasename()
		$this->markTestIncomplete ( "basename test not implemented" );
		
		FileTools::basename(/* parameters */);
	}
	
	/**
	 * Tests FileTools::dirname()
	 */
	public function testDirname() {
		// TODO Auto-generated FileToolsTest::testDirname()
		$this->markTestIncomplete ( "dirname test not implemented" );
		
		FileTools::dirname(/* parameters */);
	}
	
	/**
	 * Tests FileTools::isFilename()
	 */
	public function testIsFilename() {
		// TODO Auto-generated FileToolsTest::testIsFilename()
		$this->markTestIncomplete ( "isFilename test not implemented" );
		
		FileTools::isFilename(/* parameters */);
	}
}

