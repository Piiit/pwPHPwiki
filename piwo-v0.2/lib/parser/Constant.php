<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'pwTools/parser/LexerRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRule.php';
require_once INC_PATH.'pwTools/parser/Pattern.php';
require_once INC_PATH.'piwo-v0.2/lib/parser/Variable.php';

class Constant extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {

		$nodeData = $this->getNode()->getData();
  		$conf = pw_s2u($nodeData[0]);

  		//TODO do this with quoted string tokens...
  		if (preg_match("#(.*) *= *(.*)#i", $conf, $ass)) {
    		$varname = utf8_strtolower(utf8_trim($ass[1]));
    		$value = utf8_trim($ass[2]);
    		Variable::$variables[$varname] = $value;
    		return;
  		}

  		$conf = utf8_strtolower($conf);

  		$txt = "";

		//TODO: Substitute this translations with real PHP i18n functions!
		$months_translated = array("Januar","Februar","M&auml;rz","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember");;
		$months = array("January","February","March","April","May","June","July","August","September","October","November","December");
		$days = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
		$days_translated = array("Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag");
	
		$varnames = explode(":", $conf);
		$varname = array_shift($varnames);
		$subcat = array_pop($varnames);
		
		switch($varname) {
			case 'date': $txt = date('d.m.Y'); break;
			case 'month': $txt = date('m'); break;
			case 'monthname': $txt = str_replace($months,$months_translated,date('F')); break;
			case 'day': $txt = date('d'); break;
			case 'dayname': $txt = str_replace($days,$days_translated,date('l')); break;
			case 'year': $txt = date('Y'); break;
			case 'time': $txt = date('H:i'); break;
			case 'pi': $txt = str_replace('.',',',round(pi(), 5)); break;
			case 'e': $txt = str_replace('.',',',round(2.718281828459045235, 5)); break;
			case 'ns': $txt = pw_url2u(pw_wiki_getcfg('ns')); if ($txt === false) $txt = "[root]"; break;
			case 'id': $txt = pw_url2u(pw_wiki_getcfg('pg')); break;
			case 'wrongid':
				try { 
					$txt = pw_url2u(pw_wiki_getcfg('wrongid'));
				} catch (Exception $e) {
					$txt = "";
				} 
			break;
			case 'fullid': $txt = pw_url2u(pw_wiki_getcfg('id')); break;
			case 'startpage': $txt = ':'.pw_url2u(pw_wiki_getcfg('startpage')); break;
			case 'version': $txt = $this->getParser()->getUserInfo('piwoversion'); break;
			case 'lexerversion': $txt = Lexer::getVersion(); break;
			case 'path': $txt = 'http://'.$_SERVER['SERVER_NAME'].pw_dirname($_SERVER['PHP_SELF']); break;
			case 'countsubs':
				// count all wikipages within the current namespace
				$path = pw_wiki_getcfg('path');
				$ext = pw_wiki_getcfg('fileext');
				$txt = count(glob($path."/*".$ext));
			break;
			case 'performance':
				$txt = $this->getParser()->getUserInfo('lexerperformance');
			break;
			case 'file':
				$txt = pw_wiki_fileinfo($subcat);
			break;
			default:
// 				TestingTools::inform($varname);
				if (isset(Variable::$variables[$varname])) {
					$txt = Variable::$variables[$varname];
					$txt = self::unescape($txt);
					return $txt;
				} else {
					$_SESSION['pw_wiki']['error'] = true;
					return nop("VARIABLE '$varname' wurde nicht gesetzt.", false);
				}
	
			break;
		}
	
		return pw_s2e($txt);
	}

	public function onExit() {
		return '';
	}

	public function doRecursion() {
		return true;
	}

	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_WORD, '{{(.*?)}}');
	}
	
	public function getAllowedModes() {
		return array("#DOCUMENT", "tablecell", "listitem", "multiline", "bordererror", "borderinfo", "borderwarning", 
				"bordersuccess", "bordervalidation", "border", "bold", "underline", "italic", "monospace", "small", "big", 
				"strike", "sub", "sup", "hi", "lo", "em", "tablecell", "tableheader", "wptableheader", "wptablecell",
				"align", "justify", "alignintable", "indent", "left", "right", "pluginparam", "header", "internallinkpos", 
				"internallink", "externallink", "externallinkpos", "variable", "plugin", "pluginparameter"
				);
	}
	
	private static function unescape($txt) {
  		return str_replace(array('\"', '\>'), array('"', '&gt;'), $txt);
	}
}

?>