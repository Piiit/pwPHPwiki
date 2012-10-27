<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH."pw_lexer.php";

$norecursion = array();

function ssec_input($lexer, $node) {
	var_dump($node);
	
}

class pwLexer_parsetestfile {

	protected $text = "";
	protected $lexer;

	public function __construct($filename) {
		$text = file_get_contents($filename);
		if ($text) {
			$this->text = $text;
			$this->configLexer();
			//$this->cleanAST();
			$x = $this->lexer->getArray(null);

			var_dump($x);
		}
	}
	
	private function configLexer() {
		$this->lexer = new pwLexer($this->text, true);
		
		/*
		CONFIG FOR LEXER... future idea!
		----------------------------------
		
		// INTEGER, FLOAT, WORD, TEXT, NEWLINE and EOF are fixed implementations!
		// 'blabla' -> move the textpointer
		// ?'blabla' -> lookahead only do not consume literals!
		// start pattern only = addWordPattern...
		// start and exit pattern = addSectionPattern
		// if no content pattern is set = .*? + allowed modes
		// section name:
		//    start pattern
		//    exit pattern
		//    content pattern (current implementation: TEXT + allowed modes)
					
		SEC_DESC: 
			'[DESC]' NEWLINE 
			SEC_INPUT | SEC_EXPECTED | SEC_TEST | SEC_CONFIG | EOF
		*/
		
		$this->lexer->addSectionPattern("SEC_DESC", '\[DESC\]\n', '(?=\[[a-zA-Z]*\])');
		$this->lexer->setAllowedModes("SEC_DESC", array("#DOCUMENT"));
		$this->lexer->addSectionPattern("SEC_INPUT", '\[INPUT\]\n', '(?=\[[a-zA-Z]*\])');
		$this->lexer->setAllowedModes("SEC_INPUT", array("#DOCUMENT"));
		$this->lexer->addSectionPattern("SEC_EXPECTED", '\[EXPECTED\]\n', '(?=\[[a-zA-Z]*\])');
		$this->lexer->setAllowedModes("SEC_EXPECTED", array("#DOCUMENT"));
		$this->lexer->addSectionPattern("SEC_TEST", '\[TEST\]\n', '(?=\[[a-zA-Z]*\])');
		$this->lexer->setAllowedModes("SEC_TEST", array("#DOCUMENT"));
		$this->lexer->addSectionPattern("SEC_CONFIG", '\[CONFIG\]\n', '(?=\[[a-zA-Z]*\])');
		$this->lexer->setAllowedModes("SEC_CONFIG", array("#DOCUMENT"));
		
		$this->lexer->addWordPattern("newline", '(?<=\n)');
		$this->lexer->setAllowedModes("newline", array("#DOCUMENT", "SEC_DESC", "SEC_INPUT", "SEC_EXPECTED", "SEC_TEST", "SEC_CONFIG"));
		
		//$this->lexer->printSource();
		$this->lexer->parse();
		//$this->lexer->printDebugInfo();
		//$this->lexer->printAST();
		//$this->lexer->printLog();
	}
	
	private function cleanAST() {
	
		$documentNode = $this->lexer->firstChild();

		for ($sectionNode = $this->lexer->firstChild($documentNode); $sectionNode != null; $sectionNode = $this->lexer->nextSibling($sectionNode)) {
			for ($innerNode = $this->lexer->firstChild($sectionNode); $innerNode != null; $innerNode = $this->lexer->nextSibling($innerNode)) {
				if ($innerNode['NAME'] == "newline") {
					
				}
			}
			
		}
	
		
		$node = $this->lexer->firstChild();
		
		var_dump($node);
					
	}
	
}


$tf = new pwLexer_parsetestfile("../tests/functional/common.php/pw_dirname5.pwtest");



?>