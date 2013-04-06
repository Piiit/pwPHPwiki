<?php//FIXME INC_PATH: Define include paths such that they step outside of any subdirectory, than add dirs beginning with the project name itself! //FIXME INC_PATH: Always use realpath and dirname, this definitions are only required within a caller fileif (!defined('INC_PATH')) {	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');}require_once INC_PATH.'pwTools/string/TextFormat.php';require_once INC_PATH.'pwLexer/Lexer.php';require_once INC_PATH.'pwTools/tree/TreePrinter.php';require_once INC_PATH.'pwTools/tree/TreeArray.php';require_once INC_PATH.'pwLexer/lexerconf.php';require_once INC_PATH.'pwLexer/parserconf.php';require_once INC_PATH.'pwLexer/common.php';$fulltxt = <<<EOT\n* ++**X**++\n * 222\nEOT;#$fulltxt = "++x++";$fulltxt = file_get_contents("tmp/todos.txt");#$fulltxt = file_get_contents("tmp/test.txt");html_header();// Should produce a single Node named "#DOCUMENT" with a child node "#TEXT" containing all contents.$lexer = null;try {	$lexer = new Lexer($fulltxt, Log::DEBUG);			#$lexer->addWordPattern("FIRST", "BE");	#$lexer->setAllowedModes("FIRST", array("#DOCUMENT"));	#$lexer->addWordPattern("eof", '(?<=\n)$');	#$lexer->setAllowedModes("eof", array("#DOCUMENT"));
		$lexer = pw_wiki_lexerconf($lexer);	#$lexer->parse();		$root = $lexer->getRootNode();
	$np = new TreeWalker($root, new TreePrinter());
	
	$o = "";
	$o .= "<pre style='white-space: pre-wrap'>";
	$o .= "\n\nSOURCE: \n".TextFormat::showLineNumbers(pw_s2e($lexer->getSource()));	$o .= "\n\nAST: \n".TextFormat::showLineNumbers($np->getResult());
	//$o .= "\n\nPARSER STEP-BY-STEP: \n".$lexer->printDebugInfo(1,1, false);
	#$o .= "\n\nOUTPUT: \n";	$o .= "</pre>";	#$o .= $ast->getText();	$o .= "<pre style='white-space: pre-wrap'>";	$o .= "\n\nLOG: \n".$lexer->getLog();
	$o .= "</pre>";
	echo $o;} catch (Exception $e) {	$o = "";	$src = "N/A";	$log = "N/A";	if ($lexer) { 		$err = $lexer->getLog()->getLastLog();		$log = $lexer->getLog();		$src = $lexer->getSource();	}	$o .= "<h3>Exception catched! Logfile output...</h3>";	$o .= "<pre style='white-space: pre-wrap'>";	$o .= "ERROR MESSAGE: \n".pw_s2e(print_r($e->getMessage(), true));	$o .= "\n\nERROR TRACE: \n".pw_s2e($e->getTraceAsString());	$o .= "\n\nSOURCE: \n".pw_s2e(TextFormat::showLineNumbers($src));	$o .= "\n\nLOG: \n".$log;	$o .= "</pre>";	echo $o;}if ($lexer) {	$o = "<pre style='white-space: pre-wrap'>";
	$o .= "\n\nPATTERNTABLE: \n";
	$o .= $lexer->getPatternTableAsString();	$o .= "</pre>";	echo $o;}
?>