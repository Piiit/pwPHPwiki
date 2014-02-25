<?phpif (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/common.php';require_once INC_PATH.'piwo-v0.2/plugins/toc.php';
require_once INC_PATH.'piwo-v0.2/cfg/main.php';require_once INC_PATH.'piwo-v0.2/lib/WikiTocTools.php';require_once INC_PATH.'pwTools/parser/Lexer.php';require_once INC_PATH.'pwTools/tree/TreePrinter.php';
// include all parser token handler...$parserTokenList = glob(INC_PATH."piwo-v0.2/lib/tokens/*.php");
foreach ($parserTokenList as $parserToken) {
	require_once $parserToken;
}function getTokenHandlerList() {	$handlerList = array(
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
	return $handlerList;
}function parse($txt, $forse_debug = true) {		$debugCatchedException = false;
		try {		$handlerList = getTokenHandlerList();				$lexer = new Lexer();
		$lexer->registerHandlerList($handlerList);		$lexer->setSource($txt);
		$lexer->parse(); //TODO No pattern? AST = #DOCUMENT with a single #TEXT node
		
		$parser = new Parser();
		$parser->registerHandlerList($handlerList);
		$parser->setUserInfo('indextable', WikiTocTools::createIndexTable($parser, $lexer->getRootNode()));
		$parser->setUserInfo('lexerperformance', $lexer->getExecutionTime());
		$parser->setUserInfo('piwoversion', PIWOVERSION);
		
		$ta = new TreeWalker($lexer->getRootNode(), $parser);
		$o = implode($ta->getResult());
	} catch (Exception $e) {		$debugCatchedException = true;		TestingTools::inform("Exception catched! ERROR MESSAGE: ".pw_s2e(print_r($e->getMessage(), true)));		TestingTools::inform("ERROR TRACE: \n".pw_s2e($e->getTraceAsString()));	}		if ($debugCatchedException || $forse_debug) {			TestingTools::inform("LEXER: ".$lexer, TestingTools::NOTYPEINFO);				if (isset($lexer)) {
			TestingTools::inform("PATTERN TABLE: \n" . $lexer->getPatternTableAsString());
			$treePrinter = new TreeWalker($lexer->getRootNode(), new TreePrinter());			TestingTools::inform("PARSE TREE: \n".StringTools::showLineNumbers($treePrinter->getResult()));			TestingTools::inform("SPEED: Text parsed in ".$lexer->getExecutionTime()." seconds!");			TestingTools::inform("SOURCE:\n".StringTools::showLineNumbers($lexer->getSource()));		}
	
		//$debugString .= "<h3>Debug: Parser - Schritte (TODO: ADAPT TO NEW LEXER)</h3>";
		//$lexer->printDebugInfo(1,1);
	
	}	return $o;
}function pw_wiki_lexerconf(Lexer $lexer) {
	$lexer->addWordPattern("newline", '(?<=\n)\n');
	$lexer->addSectionPattern("wptable", '\n\{\|', '\|\}');
	$lexer->addSectionPattern("wptableline", '\|-', '(?=\|\}|\|-)');
	#$lexer->addSectionPattern("wptableheader", '\!\! *|\n\! *', '(?= *\!\!|\n)');
	$lexer->addSectionPattern("wptableheader", '(\!\! *|\n\! *)', '(?= *\!\!|\n)'); //ENTRY must be bracketed... otherwise wptable finds its EXIT too early... wrong count!
	$lexer->addSectionPattern("wptablecell", '(\|\| *|\n\|(?![\}-]) *)', '(?= *\|\||\n)');
	$lexer->addLinePattern("wptabletitle", '\|\+ *');
	$lexer->addWordPattern("wptableconfig", '([\w]+) *= *[\"\']?([^\"\']*)[\"\']? *\|* *');
	$lexer->connectTo("wptableheader", "wptableline2");
	$lexer->connectTo("wptablecell", "wptableline2");
	
	// TODO: Aggregate categories and cleanup, Attention with Deflists and Modi, which must (not) be a part of their selfs...
	$blocks = array("#DOCUMENT", "tablecell", "listitem", "multiline");	$tables = array("tablecell", "tableheader", "wptableheader", "wptablecell");
	$boxes = array("bordererror", "borderinfo", "borderwarning", "bordersuccess", "bordervalidation", "border");	$format = array("bold", "underline", "italic", "monospace", "small", "big", "strike", "sub", "sup", "hi", "lo", "em");	$align = array("align", "justify", "alignintable", "indent", "left", "right");	
	$lexer->setAllowedModes("newline", array_merge($blocks, $boxes, array("#DOCUMENT", "multiline")));
	$lexer->setAllowedModes("wptable", array("#DOCUMENT", "multiline", "wptablecell"));
	$lexer->setAllowedModes("wptableline", array("wptable"));
	$lexer->setAllowedModes("wptabletitle", array("wptable"));
	$lexer->setAllowedModes("wptableheader", array("wptableline", "wptable"));
	$lexer->setAllowedModes("wptablecell", array("wptableline", "wptable"));
	$lexer->setAllowedModes("wptableconfig", array("wptable", "wptableline", "wptableheader", "wptablecell", "wptabletitle"));

	return $lexer;
}?>