<?phperror_reporting(E_ALL);
session_start();if (!defined('INC_PATH')) {	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');}require_once INC_PATH.'pwTools/string/StringTools.php';require_once INC_PATH.'pwTools/tree/TreePrinter.php';require_once INC_PATH.'pwTools/parser/Parser.php';require_once INC_PATH.'pwTools/parser/Lexer.php';require_once INC_PATH.'piwo-v0.2/lib/lexerconf.php';require_once INC_PATH.'piwo-v0.2/lib/common.php';$fulltxt = <<<EOT\n= X =\n=== A ===\n= =\nEOT;// $fulltxt = "\n=X=\n";// $fulltxt = "\n=X=\ntest\nlastline**fett**test";$fulltxt = "Version: {{version}}; Performance: {{perFormance}}";$fulltxt = "\n$$ test\n$$ test2\n";$fulltxt = "\n|A|B\n|c||";$fulltxt = "\n^ A ^ B\n^ C | D \n| ::: | F\n";$fulltxt = "[[{{startpage}}|Startseite]]";$fulltxt = "!! var = \"quoted 				string\"\n{{var}}";$fulltxt = '1<!--comment-->2';$fulltxt = "\n<right alone><border>**Inhaltsverzeichnis:**\\\\~~TOC~~</border></right>\n== Blocksatz ==\n\n$$ :<> {{{...}}}\n:<> {{{asdf}}}\n";
$fulltxt = "~~NSTOC|dokumentation|TITLE~~";
#$fulltxt = file_get_contents("tmp/todos.txt");#$fulltxt = FileTools::setTextFileFormat(file_get_contents("tmp/test.txt"), new TextFileFormat(TextFileFormat::UNIX));StringTools::htmlIndentPrint ('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');StringTools::htmlIndentPrint ('<html>', StringTools::START);StringTools::htmlIndentPrint ('<head>', StringTools::START);StringTools::htmlIndentPrint ('<title>'.pw_wiki_getfulltitle().'</title>');StringTools::htmlIndentPrint ('<meta name="description" content="'.pw_wiki_getcfg('description').'">');StringTools::htmlIndentPrint ('<link rel="shortcut icon" href="media/favicon.ico" type="image/ico" />');StringTools::htmlIndentPrint ('<link rel="stylesheet" type="text/css" media="screen" href="../piwo-v0.2/default.css">');StringTools::htmlIndentPrint ('<link rel="stylesheet" type="text/css" media="screen" href="../piwo-v0.2/admin.css">');if (pw_wiki_getcfg('debug')) {	TestingTools::init();	TestingTools::debugOn();}StringTools::htmlIndentPrint ('</head>', StringTools::END);// Should produce a single Node named "#DOCUMENT" with a child node "#TEXT" containing all contents.echo parse($fulltxt);//session_destroy();
?>