<?phpif (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/common.php';require_once INC_PATH.'piwo-v0.2/plugins/toc.php';
require_once INC_PATH.'piwo-v0.2/cfg/main.php';require_once INC_PATH.'piwo-v0.2/lib/WikiTocTools.php';require_once INC_PATH.'piwo-v0.2/lib/WikiParser.php';require_once INC_PATH.'pwTools/parser/Lexer.php';require_once INC_PATH.'pwTools/tree/TreePrinter.php';
function parse($text, $forse_debug = true) {		$pathToTokens = INC_PATH."piwo-v0.2/lib/tokens";	$pathToPlugins = INC_PATH."piwo-v0.2/lib/plugins";		$debugCatchedException = false; 
	$wikiParser = new WikiParser($pathToTokens, $pathToPlugins);	$o = "";		try {		TestingTools::inform($text);		$wikiParser->setUserInfo('piwoversion', PIWOVERSION);		//$wikiParser->setUserInfo('indextable', new IndexTable());
				$wikiParser->parse($text);		//TODO Move create index table iterations into plug-in handling...// 		$wikiParser->setUserInfo(// 			'indextable', // 			WikiTocTools::createIndexTable($wikiParser->getParser(), $wikiParser->getLexer()->getRootNode())// 			);		// 		var_dump(WikiTocTools::createIndexTable($wikiParser->getParser(), $wikiParser->getLexer()->getRootNode()));
		
		$o = $wikiParser->getResult();	} catch (Exception $e) {		$debugCatchedException = true;		TestingTools::inform("Exception catched! ERROR MESSAGE: ".pw_s2e(print_r($e->getMessage(), true)));		TestingTools::inform("ERROR TRACE: \n".pw_s2e($e->getTraceAsString()));	}		if ($debugCatchedException || $forse_debug) {			TestingTools::inform("LEXER: ".$wikiParser->getLexer(), TestingTools::NOTYPEINFO);				if (isset($lexer)) {
			TestingTools::debug("PATTERN TABLE: \n" . $wikiParser->getLexer()->getPatternTableAsString());
			$treePrinter = new TreeWalker($wikiParser->getLexer()->getRootNode(), new TreePrinter());			TestingTools::inform("PARSE TREE: \n".StringTools::showLineNumbers($treePrinter->getResult()));			TestingTools::inform("SPEED: Text parsed in ".$wikiParser->getLexer()->getExecutionTime()." seconds!");			TestingTools::inform("SOURCE:\n".StringTools::showLineNumbers($wikiParser->getSource()));		}
	
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