<?php
abstract class ParserToken {
	
	private $node;
	private $parser;
	
	public function setNode(Node $node) {
		$this->node = $node;
	}
	
	public function getNode() {
		return $this->node;
	}
	
	public function setParser(TreeParser $parser) {
		$this->parser = $parser;
	}
	
	public function getParser() {
		return $this->parser;
	}
	
	public function getText() {
		$ta = new TreeWalker($this->node, $this->parser);
		$tmp = $this->parser->getResult();
		$this->parser->resetResult();
		$result = implode($ta->getResult());
		$this->parser->setResult($tmp);
		return $result;
	}
	
}

?>