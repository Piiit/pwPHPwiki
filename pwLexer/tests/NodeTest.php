<?php

require_once 'Node.php';
require_once 'PHPUnit\Framework\TestCase.php';

/**
 * Node test case.
 */
class NodeTest {
	
	private $Node1;
	private $Node2;
	private $N;
	
	protected function setUp() {
		parent::setUp ();
		
		$this->Node1 = new Node("4");
		$this->Node2 = new Node("3");
	}
	
	protected function tearDown() {
		$this->Node1 = null;
		$this->Node2 = null;
		
		parent::tearDown ();
	}
	
	public function test__construct() {
		$node = new Node("0");
		
		$this->setExpectedException('InvalidArgumentException');
		$node = new Node(1);
		
		$node = null;
	}
	
	public function testGetName() {
		// TODO Auto-generated NodeTest->testGetName()
		$this->markTestIncomplete ( "getName test not implemented" );
		$this->Node->getName(/* parameters */);
	}
	
	public function testSetName() {
		// TODO Auto-generated NodeTest->testSetName()
		$this->markTestIncomplete ( "setName test not implemented" );
		$this->Node->setName(/* parameters */);
	}
	
	public function testGetChildren() {
		// TODO Auto-generated NodeTest->testGetChildren()
		$this->markTestIncomplete ( "getChildren test not implemented" );
		$this->Node->getChildren(/* parameters */);
	}
	
	public function testGetData() {
		// TODO Auto-generated NodeTest->testGetData()
		$this->markTestIncomplete ( "getData test not implemented" );
		$this->Node->getData(/* parameters */);
	}
	
	public function testGetParent() {
		// TODO Auto-generated NodeTest->testGetParent()
		$this->markTestIncomplete ( "getParent test not implemented" );
		$this->Node->getParent(/* parameters */);
	}
	
	public function testIsRoot() {
		$node = new Node();
		$this->assertTrue($node->isRoot());
		$parent = new Node();
		$parent->addChild($node);
		$this->assertFalse($node->isRoot());
	}
	
	public function testAddChild() {
		$this->N = new Node("0");
		$this->N->addChild(new Node("1"));
		$this->N->addChild(new Node("2"));
		$this->N->addChild(new Node("3"));
		$this->N = null;
	}
	
	public function testSetData() {
		// TODO Auto-generated NodeTest->testSetData()
		$this->markTestIncomplete ( "setData test not implemented" );
		$this->Node->setData(/* parameters */);
	}
	
	public function testHasChildren() {
		// TODO Auto-generated NodeTest->testHasChildren()
		$this->markTestIncomplete ( "hasChildren test not implemented" );
		$this->Node->hasChildren(/* parameters */);
	}
	
	public function testGetNextSibling() {
		$this->N = new Node("0");
		$this->N->addChild(new Node("1"));
		$this->N->addChild(new Node("2"));
		$this->N->addChild(new Node("3"));
		
		$N2 = $this->N->getFirstChild();
		$this->assertEquals('1', $N2->getName());
		$this->assertEquals('2', $N2->getNextSibling()->getName());
		$this->assertEquals('3', $N2->getNextSibling()->getNextSibling()->getName());
		$this->assertEquals(null, $N2->getNextSibling()->getNextSibling()->getNextSibling());
	}
	
	public function testGetPreviousSibling() {
		// TODO Auto-generated NodeTest->testGetPreviousSibling()
		$this->markTestIncomplete ( "getPreviousSibling test not implemented" );
		$this->Node->getPreviousSibling(/* parameters */);
	}
	
	public function testGetFirstChild() {
		// TODO Auto-generated NodeTest->testGetFirstChild()
		$this->markTestIncomplete ( "getFirstChild test not implemented" );
		$this->Node->getFirstChild(/* parameters */);
	}
	
	public function testGetLastChild() {
		// TODO Auto-generated NodeTest->testGetLastChild()
		$this->markTestIncomplete ( "getLastChild test not implemented" );
		$this->Node->getLastChild(/* parameters */);
	}
	
	public function testIsInside() {
		// TODO Auto-generated NodeTest->testIsInside()
		$this->markTestIncomplete ( "isInside test not implemented" );
		
		$this->Node->isInside(/* parameters */);
	}
	
	public function testGetNodesByName() {
		// TODO Auto-generated NodeTest->testGetNodesByName()
		$this->markTestIncomplete ( "getNodesByName test not implemented" );
		
		$this->Node->getNodesByName(/* parameters */);
	}
	
	public function testGetNextSiblingSameChild() {
		// TODO Auto-generated NodeTest->testGetNextSiblingSameChild()
		$this->markTestIncomplete ( "getNextSiblingSameChild test not implemented" );
		$this->Node->getNextSiblingSameChild(/* parameters */);
	}
	
	public function testGetPreviousSiblingSameChild() {
		// TODO Auto-generated NodeTest->testGetPreviousSiblingSameChild()
		$this->markTestIncomplete ( "getPreviousSiblingSameChild test not implemented" );
		$this->Node->getPreviousSiblingSameChild(/* parameters */);
	}
}

