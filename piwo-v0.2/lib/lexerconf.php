<?phpif (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/common.php';require_once INC_PATH.'piwo-v0.2/plugins/toc.php';
require_once INC_PATH.'piwo-v0.2/cfg/main.php';require_once INC_PATH.'piwo-v0.2/lib/WikiTocTools.php';
// include all parser token handler...$parserTokenList = glob(INC_PATH."piwo-v0.2/lib/tokens/*.php");
foreach ($parserTokenList as $parserToken) {
	require_once $parserToken;
}function parse($txt) {		$debugCatchedException = false;
	$debugString = "";
		try {		$loglevel = Log::INFO;		if(pw_wiki_getcfg('debug') === true) {			$loglevel = Log::DEBUG;		}		$lexer = new Lexer($txt, $loglevel);				$handlerList = array(				new Header(),				new Border(),				new BorderError(),				new BorderInfo(),				new BorderSuccess(),				new BorderValidation(),				new BorderWarning(),				new Plugin(),				new PluginParameter(),
				new InternalLink(),				new InternalLinkText(),				new InternalLinkMode(),				new InternalLinkPos(),				new Url(),				new UrlNoProtocol(),				new Big(),				new Bold(),				new Em(),				new Hi(),				new Italic(),				new Lo(), 				new Monospace(),				new Small(),				new Strike(),				new Sub(),				new Sup(),				new Underline(),				new Code(),				new NoWiki(),				new NoWikiAlt(),				new Newline(),				new Multiline(),				new Preformat(),				new Align(),				new Justify(),				new Indent(),				new Right(),				new Left(),				new Constant(),				new Symbol(),				new Variable(),				new ExternalLink(),				new ExternalLinkPos(),				new Pre(),				new TableCell(),				new TableRow(),				new TableHeader(),				new Table(),				new TableSpan(),				new AlignInTable(),				new HorizontalRule(),				new DefTerm(),				new DefList(),				new DefItem(),				new ListItem(),				new Lists(),				new Footnote(),				new QuotedString(),				new Math(),				new NoToc(),				new Comment(),				new Comment2()		);				$lexer->registerHandlerList($handlerList);				//TODO No pattern? AST = #DOCUMENT with a single #TEXT node
		$lexer->parse();		$parser = new Parser();		$parser->registerHandlerList($handlerList);
		$parser->setUserInfo('indextable', WikiTocTools::createIndexTable($parser, $lexer->getRootNode()));		$parser->setUserInfo('lexerperformance', $lexer->getExecutionTime());		$parser->setUserInfo('piwoversion', PIWOVERSION);				$_SESSION["pw_wiki"]["error"] = false;		 		$ta = new TreeWalker($lexer->getRootNode(), $parser);
 		$o = implode($ta->getResult());
	} catch (Exception $e) {		$debugCatchedException = true;		$src = "N/A";		$log = "N/A";		if (isset($lexer)) {			$err = $lexer->getLog()->getLastLog();			$log = $lexer->getLog();			$src = $lexer->getSource();		}		$debugString .= "<h3>Exception catched! Logfile output...</h3>";		$debugString .= "<pre style='white-space: pre-wrap'>";		$debugString .= "ERROR MESSAGE: \n".pw_s2e(print_r($e->getMessage(), true));		$debugString .= "\n\nERROR TRACE: \n".pw_s2e($e->getTraceAsString());		$debugString .= "\n\nSOURCE: \n".pw_s2e(StringTools::showLineNumbers($src));		$debugString .= "\n\nLOG: \n".$log;		$debugString .= "</pre>";	}		if ($debugCatchedException || pw_wiki_getcfg('debug')) {			
// 		echo $debugString;
		if (isset($lexer)) {
			$debugString = "<pre style='white-space: pre-wrap'>";
			$debugString .= "\n\nPATTERNTABLE: \n";
			$debugString .= $lexer->getPatternTableAsString();
			$debugString .= "</pre>";
// 			echo $debugString;
		}
		
		$debugString .= "<div id='imdebug'>";		$debugString .= "<h2>DEBUG</h2>";
		$debugString .= "<h3>Lexer</h3>";
		$debugString .= $lexer;
	
		$debugString .= "<h3>AST</h3>";
		$debugString .= "<pre style='overflow: auto'>";		$treePrinter = new TreeWalker($lexer->getRootNode(), new TreePrinter());
		$ast = $treePrinter->getResult();
		$debugString .= StringTools::showLineNumbers($ast);
		$debugString .= "</pre>";
	
		$debugString .= "<h3>Log</h3>";
		$debugString .= "<pre style='overflow: auto'>";
	
		$logtext = $lexer->getLog()->toStringReversed();
		$debugString .= pw_s2e($logtext);
		$debugString .= "</pre>";
	
		$debugString .= "<h3>Performance</h3>";
		$debugString .= "Text in ".$lexer->getExecutionTime()."s geparsed!";
	
		//$debugString .= "<h3>Debug: Parser - Schritte (TODO: ADAPT TO NEW LEXER)</h3>";
		//$lexer->printDebugInfo(1,1);
	
		$debugString .= "<h3>Text, der geparsed werden soll (mit Zeilennummern).</h3>";
		$debugString .= "Die erste und letzte Zeile werden vom Lexer automatisch hinzugef&uuml;gt.";
		$debugString .= StringTools::preFormatShowLineNumbers($lexer->getSource());
	
		$debugString .= "<h3>Lexer: PatternTable</h3>";
		$debugString .= StringTools::preFormatShowLineNumbers($lexer->getPatternTableAsString());
		$debugString .= "</div>";
	}	TestingTools::outputOff(); 	TestingTools::inform($debugString);
	
	return $o;
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