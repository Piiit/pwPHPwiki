<?phpif (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH."piwo-v0.2/lib/pw_lexer.php";require_once INC_PATH.'piwo-v0.2/lib/common.php';require_once INC_PATH.'piwo-v0.2/plugins/toc.php';
require_once INC_PATH.'pwTools/data/IndexTable.php';require_once INC_PATH.'pwTools/parser/Lexer.php';// include all parser token handler...$parserTokenList = glob(INC_PATH."piwo-v0.2/lib/parser/*.php");
foreach ($parserTokenList as $parserToken) {
	require_once $parserToken;
}$variables = array();$norecursion = array("externallink", "variable", "footnote", "tableheader", "tablecell");#$dontencode = array("ilinkpos");$moditext = "edit|showpages";$dontencode = array();$footnote = 0;$footnotes = array();$indextable = null;function parse($txt) {	try {		$loglevel = pw_wiki_getcfg('debug');				if($loglevel === true) {			$loglevel = Log::DEBUG;		} else {			$loglevel = Log::INFO;		}		$lexer = new Lexer($txt, $loglevel);				$handlerList = array(				new Header(),				new Border(),				new BorderError(),				new BorderInfo(),				new BorderSuccess(),				new BorderValidation(),				new BorderWarning(),				new Plugin(),				new PluginParameter(),
				new InternalLink(),				new InternalLinkPos(),				new Url(),				new Big(),				new Bold(),				new Em(),				new Hi(),				new Italic(),				new Lo(), 				new Monospace(),				new Small(),				new Strike(),				new Sub(),				new Sup(),				new Underline(),				new Code(),				new NoWiki(),				new NoWikiAlt(),				new Newline(),				new Multiline(),				new Preformat(),				new Align(),				new Justify(),				new Indent(),				new Right(),				new Left(),				new Constant(),				new Symbol(),				new Variable(),				new ExternalLink(),				new ExternalLinkPos(),				new Pre(),				new TableCell(),				new TableRow(),				new TableHeader(),				new Table(),				new TableSpan(),				new HorizontalRule(),				new DefTerm(),				new DefList(),				new DefItem(),		);						$lexer->registerHandlerList($handlerList);				//TODO No pattern? AST = #DOCUMENT with a single #TEXT node
		$lexer->parse();		$parser = new TreeParser();		$parser->registerHandlerList($handlerList);
				$GLOBALS['idheader'] = 0;		$it = new IndexTable();		createindextable($parser, $lexer->getRootNode(), $it);		$GLOBALS['indextable'] = $it;				$_SESSION["pw_wiki"]["error"] = false;		$o = StringFormat::htmlIndent("<div id='imwiki'>", StringFormat::START);		// 		TestingTools::inform($lexer->getRootNode());		 		$ta = new TreeWalker($lexer->getRootNode(), $parser);
 		$o .= implode($ta->getResult());
				$o .= StringFormat::htmlIndent("</div>", StringFormat::END);// 		echo StringFormat::preFormat(StringFormat::showLineNumbers(pw_s2e($o)));// 		echo $o;//   		TestingTools::informPrintNewline($lexer->getLog()->__toString());		return $o;	} catch (Exception $e) {		$o = "";
		$src = "N/A";
		$log = "N/A";
		if (isset($lexer)) {
			$err = $lexer->getLog()->getLastLog();
			$log = $lexer->getLog();
			$src = $lexer->getSource();
		}
		$o .= "<h3>Exception catched! Logfile output...</h3>";
		$o .= "<pre style='white-space: pre-wrap'>";
		$o .= "ERROR MESSAGE: \n".pw_s2e(print_r($e->getMessage(), true));
		$o .= "\n\nERROR TRACE: \n".pw_s2e($e->getTraceAsString());
		$o .= "\n\nSOURCE: \n".pw_s2e(StringFormat::showLineNumbers($src));
		$o .= "\n\nLOG: \n".$log;
		$o .= "</pre>";
		
		echo $o;		if (isset($lexer)) {			$o = "<pre style='white-space: pre-wrap'>";			$o .= "\n\nPATTERNTABLE: \n";			$o .= $lexer->getPatternTableAsString();			$o .= "</pre>";			echo $o;		}	}		if (pw_wiki_getcfg('debug')) {
	
		$o .= "<div id='imdebug'>";
		$o .= "<h3>Lexer: Version und Kurzinfos.</h3>";
		$o .= $lexer;
	
		$o .= "<h3>AST</h3>";
		$o .= "<pre style='overflow: auto'>";
		$o .= $lexer->getAST();
		$o .= "</pre>";
	
		$o .= "<h3>Logdatei (ohne INFO-Zeilen)</h3>";
		$o .= "<pre style='overflow: auto'>";
	
		#$o .= utf8_encode(htmlentities(utf8_decode($lexer->getLogText(false))));
		$logtext = $lexer->getLogText(false);
		$o .= pw_s2e($logtext);
		$o .= "</pre>";
	
		#$o .= "<h3>Performance</h3>";
		#$o .= "Text in ".$lexer->getExecutionTime()."s geparsed!";
	
		$o .= "<h3>Debug: Parser - Schritte</h3>";
		$lexer->printDebugInfo(1,1);
	
		#$o .= "<h3>Text, der geparsed werden soll (mit Zeilennummern).</h3>";
		#$o .= "Die erste und letzte Zeile werden vom Lexer automatisch hinzugef&uuml;gt.";
		#$lexer->printSource(true);
	
		#$o .= "<h3>Lexer: PatternTable</h3>";
		#$lexer->printPatternTable();
		$o .= "</div>";
	}
	
	return $o;
}function pw_wiki_lexerconf(Lexer $lexer) {
	$lexer->addWordPattern("newline", '(?<=\n)\n');
	$lexer->addWordPattern("eof", '(?<=\n)$');
	$lexer->addSectionPattern("footnote", '\(\(', '\)\)');

	$lexer->addWordPattern("url2", '(www\.[^ \"\n\r\t<\]]*)');

	// Single lines...
	$lexer->addSectionPattern("quoted_string", '(?<!\\\)"', '(?<!\\\)"');

	// Blocks...
	
	#$lexer->addSectionPattern("tablerow", '(?=\n *\||\^)', '\||\^ *\n');                 # EXIT = '\||\^ *\n'
	#$lexer->addSectionPattern("tableheader", '\^(?! *\n)', '(?=\||\^|\n)');
	#$lexer->addSectionPattern("tablecell", '\|(?! *\n)', '(?=\||\^|\n)');

	$lexer->addSectionPattern("listitem", '\n( *)([\*\#]) ', '\n');
	$lexer->connectTo("listitem", "list");
	$lexer->addSectionPattern("comment", '<!--', '-->'); // @TODO: Einstellung -> NO-NODE (do not create a node)
	$lexer->addSectionPattern("comment2", '"""', '"""'); // @TODO: Einstellung -> NO-NODE (do not create a node)

	// Wikipedia-Tables...
	$lexer->addSectionPattern("wptable", '\n\{\|', '\|\}');
	$lexer->addSectionPattern("wptableline", '\|-', '(?=\|\}|\|-)');
	#$lexer->addSectionPattern("wptableheader", '\!\! *|\n\! *', '(?= *\!\!|\n)');
	$lexer->addSectionPattern("wptableheader", '(\!\! *|\n\! *)', '(?= *\!\!|\n)'); //ENTRY must be bracketed... otherwise wptable finds its EXIT too early... wrong count!
	$lexer->addSectionPattern("wptablecell", '(\|\| *|\n\|(?![\}-]) *)', '(?= *\|\||\n)');
	$lexer->addLinePattern("wptabletitle", '\|\+ *');
	$lexer->addWordPattern("wptableconfig", '([\w]+) *= *[\"\']?([^\"\']*)[\"\']? *\|* *');
	$lexer->connectTo("wptableheader", "wptableline2");
	$lexer->connectTo("wptablecell", "wptableline2");


	$lexer->addSectionPattern("alignintable", ':>(>*) ', '(?=\|)');

	// Sections...
	$lexer->addSectionPattern("math", '<math>', '<\/math>');
	$lexer->addSectionPattern("notoc", '<notoc>', '<\/notoc>');

	
	// TODO: Aggregate categories and cleanup, Attention with Deflists and Modi, which must (not) be a part of their selfs...
	$blocks = array("#DOCUMENT", "tablecell", "listitem", "multiline");	$tables = array("tablecell", "tableheader", "wptableheader", "wptablecell");
	$boxes = array("bordererror", "borderinfo", "borderwarning", "bordersuccess", "bordervalidation", "border");	$format = array("bold", "underline", "italic", "monospace", "small", "big", "strike", "sub", "sup", "hi", "lo", "em");	$align = array("align", "justify", "alignintable", "indent", "left", "right");	
	$lexer->setAllowedModes("footnote", array_merge($format, $blocks, $boxes, $align, $tables, array("defitem", "defterm")));
	$lexer->setAllowedModes("newline", array_merge($blocks, $boxes, array("#DOCUMENT", "multiline")));
	$lexer->setAllowedModes("comment", array_merge($format, $blocks, $boxes, $align, $tables));
	$lexer->setAllowedModes("comment2", array_merge($format, $blocks, $boxes, $align, $tables));


	$lexer->setAllowedModes("quoted_string", array("variable"));
	$lexer->setAllowedModes("eof", array("#DOCUMENT"));
	$lexer->setAllowedModes("indent", array_merge($blocks, $boxes));
	$lexer->setAllowedModes("math", array_merge($format, $blocks, $boxes, $tables));
	$lexer->setAllowedModes("notoc", array_merge($blocks, $boxes, $tables, array("left", "right")));

	// Tables...
	$lexer->setAllowedModes("alignintable", array("tablecell"));
	$lexer->setAllowedModes("wptable", array("#DOCUMENT", "multiline", "wptablecell"));
	$lexer->setAllowedModes("wptableline", array("wptable"));
	$lexer->setAllowedModes("wptabletitle", array("wptable"));
	$lexer->setAllowedModes("wptableheader", array("wptableline", "wptable"));
	$lexer->setAllowedModes("wptablecell", array("wptableline", "wptable"));
	$lexer->setAllowedModes("wptableconfig", array("wptable", "wptableline", "wptableheader", "wptablecell", "wptabletitle"));

	$lexer->setAllowedModes("listitem", array("#DOCUMENT", "multiline", "left", "right", "wptablecell"));

	// Hyperlinks...
	$lexer->setAllowedModes("url2", array_merge($format, $blocks, $boxes, array("externallink", "footnote", "defitem")) );

	return $lexer;
}?>