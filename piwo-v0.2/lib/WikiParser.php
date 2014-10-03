<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/common.php';
require_once INC_PATH.'piwo-v0.2/plugins/toc.php';
require_once INC_PATH.'piwo-v0.2/cfg/main.php';
require_once INC_PATH.'piwo-v0.2/lib/WikiTocTools.php';
require_once INC_PATH.'pwTools/parser/Lexer.php';
require_once INC_PATH.'pwTools/tree/TreePrinter.php';

class WikiParser {
	private $lexer = null;
	private $parser = null;
	private $handlerList = null;
	private $result = null;
	
	public function __construct() {
		$this->loadTokensHandlerList();
		$this->lexer = new Lexer();
		$this->parser = new Parser();
		$this->lexer->registerHandlerList($this->handlerList);
		$this->parser->registerHandlerList($this->handlerList);
	}
	
	public function parse($text) {
		$this->lexer->setSource($text);
		$this->lexer->parse();
		
		$this->setUserInfo('lexer.performance', $this->lexer->getExecutionTime());
		$this->setUserInfo('lexer.version', $this->lexer->getVersion());
		$this->setUserInfo('indextable', WikiTocTools::createIndexTable($this->parser, $this->lexer->getRootNode()));
		
		$treeWalker = new TreeWalker($this->lexer->getRootNode(), $this->parser);
		$this->result = implode($treeWalker->getResult());
	}
	
	public function getSource() {
		return $this->lexer->getSource();
	}
	
	public function getResult() {
		if($this->result == null) {
			throw new Exception("No parsed results found!");
		}
		return $this->result;
	}
	
	public function setUserInfo($key, $value) {
		$this->parser->setUserInfo($key, $value);
	}
	
	public function getLexer() {
		return $this->lexer;
	}

	public function getParser() {
		return $this->parser;
	}

	private function loadTokensHandlerList() {
		// include all parser token handler...
		$parserTokenList = glob(INC_PATH."piwo-v0.2/lib/tokens/*.php");
		foreach ($parserTokenList as $parserToken) {
			require_once $parserToken;
		}
		
		if($this->handlerList != null) {
			return;
		}
		
		$this->handlerList = array(
				new Header(),
				new Border(),
				new BorderError(),
				new BorderInfo(),
				new BorderSuccess(),
				new BorderValidation(),
				new BorderWarning(),
				new Plugin(),
				new PluginParameter(),
				new InternalLink(),
				new InternalLinkText(),
				new InternalLinkMode(),
				new InternalLinkPos(),
				new Url(),
				new UrlNoProtocol(),
				new Big(),
				new Bold(),
				new Em(),
				new Hi(),
				new Italic(),
				new Lo(),
				new Monospace(),
				new Small(),
				new Strike(),
				new Sub(),
				new Sup(),
				new Underline(),
				new Code(),
				new NoWiki(),
				new NoWikiAlt(),
				new Newline(),
				new Multiline(),
				new Preformat(),
				new Align(),
				new Justify(),
				new Indent(),
				new Right(),
				new Left(),
				new Constant(),
				new Symbol(),
				new Variable(),
				new ExternalLink(),
				new ExternalLinkPos(),
				new Pre(),
				new TableCell(),
				new TableRow(),
				new TableHeader(),
				new Table(),
				new TableSpan(),
				new AlignInTable(),
				new HorizontalRule(),
				new DefTerm(),
				new DefList(),
				new DefItem(),
				new ListItem(),
				new Lists(),
				new Footnote(),
				new QuotedString(),
				new Math(),
				new NoToc(),
				new Comment(),
				new Comment2()
		);
	}
}

?>