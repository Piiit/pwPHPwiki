<?phpif (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}
require_once INC_PATH."piwo-v0.2/lib/pw_lexer.php";require_once INC_PATH.'piwo-v0.2/lib/common.php';require_once INC_PATH.'pwTools/data/IndexTable.php';require_once INC_PATH.'pwTools/parser/Lexer.php';// include all parser token handler...$parserTokenList = glob(INC_PATH."piwo-v0.2/lib/parser/*.php");
foreach ($parserTokenList as $parserToken) {
	require_once $parserToken;
}$variables = array();$norecursion = array("externallink", "variable", "footnote", "tableheader", "tablecell");#$dontencode = array("ilinkpos");$moditext = "edit|showpages";$dontencode = array();$footnote = 0;$footnotes = array();$indextable = null;function parse($txt) {	try {		$loglevel = pw_wiki_getcfg('debug');				if($loglevel === true) {			$loglevel = Log::DEBUG;		} else {			$loglevel = Log::INFO;		}		$lexer = new Lexer($txt, $loglevel);				$handlerList = array(				new Header(),				new Border(),				new BorderError(),				new BorderInfo(),				new BorderSuccess(),				new BorderValidation(),				new BorderWarning(),				new Plugin(),				new InternalLink(),				new InternalLinkPos(),				new Url(),				new Big(),				new Bold(),				new Em(),				new Hi(),				new Italic(),				new Lo(), 				new Monospace(),				new Small(),				new Strike(),				new Sub(),				new Sup(),				new Underline(),				new Code(),				new NoWiki(),				new NoWikiAlt(),// 				new Table(),// 				new TableCell(),// 				new TableRow()				);						$lexer->registerHandlerList($handlerList);// 		$lexer->connectTo("tablerow", "table");
				//TODO No pattern? AST = #DOCUMENT with a single #TEXT node
		$lexer->parse();		$parser = new TreeParser();		$parser->registerHandlerList($handlerList);
				$GLOBALS['idheader'] = 0;		$it = new IndexTable();		createindextable($parser, $lexer->getRootNode(), $it);		$GLOBALS['indextable'] = $it;				$_SESSION["pw_wiki"]["error"] = false;		$o = StringFormat::htmlIndent("<div id='imwiki'>", StringFormat::START);		// 		TestingTools::inform($lexer->getRootNode());		 		$ta = new TreeWalker($lexer->getRootNode(), $parser);
 		$o .= implode($ta->getResult());
				$o .= StringFormat::htmlIndent("</div>", StringFormat::END);		echo StringFormat::preFormat($lexer->getLog());
				return $o;	} catch (Exception $e) {		$o = "";
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
		
		echo $o;		if (isset($lexer)) {			$o = "<pre style='white-space: pre-wrap'>";			$o .= "\n\nPATTERNTABLE: \n";			$o .= $lexer->getPatternTableAsString();			$o .= "</pre>";			echo $o;		}	}	}function lexerconf($txt, $hdlen = -1, $ftlen = -1) {  try {    // Erstelle ein neues Lexer-Objekt...    $lexer = new pwLexer($txt, pw_wiki_getcfg('debug'));    pw_wiki_lexerconf($lexer);    // Parse den Text; ignoriere CDATA-Einträge...    $lexer->parse(false);    // @TODO: PLUGINs -> runBeforeOutput/Renderer... registerFunction?//     out($GLOBALS['indextable']);    $GLOBALS['indextable'] = array("LEVELS" => array(1=>0,2=>0,3=>0,4=>0,5=>0,"LASTLEVEL"=>0,"CONT"=>array()));    $GLOBALS['idheader'] = 0;    $it = new IndexTable();    createindextable($lexer, null, $it);//     echo $it;    $_SESSION["pw_wiki"]["error"] = false;    $o = StringFormat::htmlIndent ("<div id='imwiki'>", StringFormat::START);    #$node = $lexer->getNode(9);    $o .= $lexer->getText(null);    $o .= StringFormat::htmlIndent ("</div>", StringFormat::END);    #TestingTools::inform($lexer->AST);    if (pw_wiki_getcfg('debug')) {      $o .= "<div id='imdebug'>";      $o .= "<h3>Lexer: Version und Kurzinfos.</h3>";      $o .= $lexer;      $o .= "<h3>AST</h3>";      $o .= "<pre style='overflow: auto'>";      $o .= $lexer->getAST();      $o .= "</pre>";      $o .= "<h3>Logdatei (ohne INFO-Zeilen)</h3>";      $o .= "<pre style='overflow: auto'>";      #$o .= utf8_encode(htmlentities(utf8_decode($lexer->getLogText(false))));      $logtext = $lexer->getLogText(false);      $o .= pw_s2e($logtext);      $o .= "</pre>";      #$o .= "<h3>Performance</h3>";      #$o .= "Text in ".$lexer->getExecutionTime()."s geparsed!";      $o .= "<h3>Debug: Parser - Schritte</h3>";      $lexer->printDebugInfo(1,1);      #$o .= "<h3>Text, der geparsed werden soll (mit Zeilennummern).</h3>";      #$o .= "Die erste und letzte Zeile werden vom Lexer automatisch hinzugef&uuml;gt.";      #$lexer->printSource(true);      #$o .= "<h3>Lexer: PatternTable</h3>";      #$lexer->printPatternTable();      $o .= "</div>";    }    return $o;  } catch (Exception $e) {  	TestingTools::inform($e);    global $idurl;    $o = "";    $err = array_pop($lexer->getLog(false));    /*    $o .= "Bitte w&auml;hlen: ";    $o .= "<span class='edit'>[<a href='?mode=editpage&id=$idurl'>Bearbeiten</a>]</span> | ";    $o .= "<span class='edit'>[<a href='?mode=$MODE&dialog=delpage&id=$idurl'>L&ouml;schen</a>]</span> | ";    $o .= "<span class='edit'>[<a href='?mode=showpages&id=$idurl'>Seiten&uuml;berblick anzeigen</a>]</span> | ";    $o .= "<span class='edit'>[<a href='?mode=cleared&id='>Zur&uuml;ck zur Startseite</a>]</span>";    $o .= "<hr />";    */    $o .= "<h1>Fehler im Wikitext erkannt</h1>";    $o .= "Fehler in der Zeile ".$err['DATA']['LINENR'].". ";    $o .= "<pre style='white-space: pre-wrap'>";    $o .= pw_wiki_syntaxerr(pw_u2t($lexer->getSource(false)), $err['DATA']['LINENR'], $err['DATA']['TEXT'], $hdlen, $ftlen);    $o .= "</pre>";    $o .= "<i>Der graue Text zeigt die Kopf- und Fu&szlig;zeile des Dokumentes.</i>";    /*    $o .= "<hr />Bitte w&auml;hlen: ";    $o .= "<span class='edit'>[<a href='?mode=editpage&id=$idurl'>Bearbeiten</a>]</span> | ";    $o .= "<span class='edit'>[<a href='?mode=$MODE&dialog=delpage&id=$idurl'>L&ouml;schen</a>]</span> | ";    $o .= "<span class='edit'>[<a href='?mode=showpages&id=$idurl'>Seiten&uuml;berblick anzeigen</a>]</span> | ";    $o .= "<span class='edit'>[<a href='?mode=cleared&id='>Zur&uuml;ck zur Startseite</a>]</span>";    */    if (pw_wiki_getcfg('debug')) {      $o .= "<div id='imdebug'>";      $o .= "<h3>Exception catched! Logfile output...</h3>";      $o .= "<pre>";      $o .= "ERROR MESSAGE: <pre>".pw_s2e(print_r($e->getMessage(), true))."</pre>";      $o .= "ERROR TRACE: <pre>".pw_s2e($e->getTraceAsString())."</pre>";      $o .= "SOURCE: <pre>".pw_s2e($lexer->getSource(true))."</pre>";      $o .= "PARSER STEP-BY-STEP: <pre>".$lexer->printDebugInfo(1,1, false)."</pre>";      $o .= "</pre>";      $o .= "</div>";    }    return $o;    #die("Programm terminated!");  }}function pw_wiki_lexerconf(Lexer $lexer) {
	$lexer->addWordPattern("newline", '(?<=\n)\n');
	$lexer->addWordPattern("newline2", '\\\\\\\\');
	$lexer->addWordPattern("eof", '(?<=\n)$');
	$lexer->addWordPattern("const", '{{(.*?)}}');
	$lexer->addWordPattern("symbol", '&(\#*[a-zA-Z0-9]{2,9});');
	$lexer->addLinePattern("hrule", '----');
	$lexer->addSectionPattern("footnote", '\(\(', '\)\)');

	$lexer->addWordPattern("url2", '(www\.[^ \"\n\r\t<\]]*)');

	$lexer->addSectionPattern("externallink", '(?=\[(?!\[))', '\]');
	$lexer->addSectionPattern("elinkpos", '\[(?!\[)', ' |(?=\])');

	// Single lines...
	$lexer->addLinePattern("preformat", '( *\$\$ | *\$\$)');
	$lexer->connectTo("preformat", "pre");
	$lexer->addSectionPattern("plugin", '~~([\w]+):*([\w]+)*', '~~');
	$lexer->addSectionPattern("pluginparam", '\|', '(?=\||~~)');
	$lexer->addLinePattern("variable", '!! ([\w]+) *= *');
	$lexer->addSectionPattern("quoted_string", '(?<!\\\)"', '(?<!\\\)"');

	// Blocks...
	$blocks = array("#DOCUMENT", "tablecell", "listitem", "multiline");
	#$lexer->addSectionPattern("tablerow", '(?=\n *\||\^)', '\||\^ *\n');                 # EXIT = '\||\^ *\n'
	#$lexer->addSectionPattern("tableheader", '\^(?! *\n)', '(?=\||\^|\n)');
	#$lexer->addSectionPattern("tablecell", '\|(?! *\n)', '(?=\||\^|\n)');

	$lexer->addSectionPattern("tablerow", '\n *(?=\||\^)', '\n');
	$lexer->addSectionPattern("tableheader", '\^', '(?=\||\^|\n)');
	$lexer->addSectionPattern("tablecell", '\|', '(?=\||\^|\n)');
	$lexer->addWordPattern("tablespan", ' *::: *');
	$lexer->connectTo("tablerow", "table");
	$lexer->addSectionPattern("listitem", '\n( *)([\*\#]) ', '\n');
	$lexer->connectTo("listitem", "list");
	$lexer->addSectionPattern("multiline", '{{{', '}}}');
	$lexer->addSectionPattern("defterm", '\n( *)\; ', '\n');
	$lexer->addSectionPattern("defitem", '(\n: |: )', '(?=\n)');  // bracket this... BUG countPatternLevel
	$lexer->connectTo("defterm", "deflist");
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

	// Format...
	$format = array("bold", "underline", "italic", "monospace", "small", "big", "strike", "sub", "sup", "hi", "lo", "em");

	//Alignment...
	$align = array("align", "justify", "alignintable", "indent", "left", "right");
	$lexer->addSectionPattern("align", '\n:>(>*) ', '\n');
	$lexer->addSectionPattern("justify", '\n:<> ', '\n');
	$lexer->addSectionPattern("alignintable", ':>(>*) ', '(?=\|)');
	$lexer->addSectionPattern("indent", '\n:(:*) ', '\n');

	// Columncontrol
	$lexer->addSectionPattern("left", '<left>', '<\/left>');
	$lexer->addSectionPattern("right", '<right( *)(alone)*>', '<\/right>');


	// Sections...
	$lexer->addSectionPattern("math", '<math>', '<\/math>');
	$lexer->addSectionPattern("notoc", '<notoc>', '<\/notoc>');

	$boxes = array("error", "info", "warning", "success", "validation", "border");


	// ----------------------
	// Permissions
	// ----------------------

	// TODO: Aggregate categories and cleanup, Attention with Deflists and Modi, which must (not) be a part of their selfs...
	$tables = array("tablecell", "tableheader", "wptableheader", "wptablecell");

	$lexer->setAllowedModes("footnote", array_merge($format, $blocks, $boxes, $align, $tables, array("defitem", "defterm")));
	$lexer->setAllowedModes("newline", array_merge($blocks, $boxes, array("#DOCUMENT", "multiline")));
	$lexer->setAllowedModes("newline2", array_merge($blocks, $boxes, $format, $align, $tables, array("footnote")));
	$lexer->setAllowedModes("header", array_merge($boxes, array("#DOCUMENT", "left", "right", "notoc", "multiline")));
	$lexer->setAllowedModes("multiline", array("#DOCUMENT", "listitem", "tablecell", "multiline", "alignintable", "justify", "align", "defitem", "indent"), true);
	$lexer->setAllowedModes("preformat", array_merge($blocks, $tables, $boxes, $align));
	$lexer->setAllowedModes("plugin", array_merge($format, $blocks, $boxes, $align, $tables, array("multiline", "header", "ilinkpos", "internallink", "externallink", "elinkpos")));
	$lexer->setAllowedModes("variable", array_merge($format, $blocks, $boxes, $align, $tables));
	$lexer->setAllowedModes("comment", array_merge($format, $blocks, $boxes, $align, $tables));
	$lexer->setAllowedModes("comment2", array_merge($format, $blocks, $boxes, $align, $tables));


	$lexer->setAllowedModes("quoted_string", array("variable"));
	$lexer->setAllowedModes("pluginparam", array("plugin"));
	$lexer->setAllowedModes("eof", array("#DOCUMENT"));
	$lexer->setAllowedModes("align", array_merge($blocks, $boxes, $align));
	$lexer->setAllowedModes("justify", array_merge($blocks, $boxes));
	$lexer->setAllowedModes("indent", array_merge($blocks, $boxes));
	$lexer->setAllowedModes("defterm", array_merge($blocks, $boxes, $align));
	$lexer->setAllowedModes("defitem", array("defterm"));
	$lexer->setAllowedModes("const", array_merge($format, $blocks, $boxes, $tables, $align, array("pluginparam", "header", "ilinkpos", "internallink", "externallink", "elinkpos", "variable")));
	$lexer->setAllowedModes("symbol", array_merge($format, $blocks, $boxes, $tables, $align, array("math", "defitem", "defterm")));
	$lexer->setAllowedModes("code", array_merge($format, $blocks, $boxes));
	$lexer->setAllowedModes("math", array_merge($format, $blocks, $boxes, $tables));
	$lexer->setAllowedModes("notoc", array_merge($blocks, $boxes, $tables, array("left", "right")));
	$lexer->setAllowedModes("left", array("#DOCUMENT"));
	$lexer->setAllowedModes("right", array("#DOCUMENT"));
	$lexer->setAllowedModes("hrule", array_merge(array("#DOCUMENT", "multiline", "left", "right"), $boxes));

	// Tables...
	$lexer->setAllowedModes("alignintable", array("tablecell"));
	$lexer->setAllowedModes("tablecell", array("tablerow"));
	$lexer->setAllowedModes("tableheader", array("tablerow"));
	$lexer->setAllowedModes("tablespan", array("tablecell", "tableheader"));
	$lexer->setAllowedModes("tablerow", array("#DOCUMENT", "multiline", "notoc"));
	$lexer->setAllowedModes("wptable", array("#DOCUMENT", "multiline", "wptablecell"));
	$lexer->setAllowedModes("wptableline", array("wptable"));
	$lexer->setAllowedModes("wptabletitle", array("wptable"));
	$lexer->setAllowedModes("wptableheader", array("wptableline", "wptable"));
	$lexer->setAllowedModes("wptablecell", array("wptableline", "wptable"));
	$lexer->setAllowedModes("wptableconfig", array("wptable", "wptableline", "wptableheader", "wptablecell", "wptabletitle"));

	$lexer->setAllowedModes("listitem", array("#DOCUMENT", "multiline", "left", "right", "wptablecell"));

	// Hyperlinks...
	$lexer->setAllowedModes("url2", array_merge($format, $blocks, $boxes, array("externallink", "footnote", "defitem")) );
	$lexer->setAllowedModes("externallink", array_merge($format, $blocks, $boxes, $tables, $align, array("footnote", "defitem", "defterm")));
	$lexer->setAllowedModes("elinkpos", array("externallink"));

	return $lexer;
}?>