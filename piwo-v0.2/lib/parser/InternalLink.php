<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'pwTools/parser/LexerRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRuleHandler.php';
require_once INC_PATH.'pwTools/parser/ParserRule.php';
require_once INC_PATH.'pwTools/parser/Pattern.php';

class InternalLink extends ParserRule implements ParserRuleHandler, LexerRuleHandler {
	
	public function getName() {
		return strtolower(__CLASS__);
	}
	
	public function onEntry() {
		//@TODO: clean redundant code... specially for encoding-functions!
		global $indextable;
		global $moditext;
		
		$node = $this->getNode();
		
		$linkPositionNode = $node->getFirstChild();
		$linkPositionText = $this->getTextFromNode($linkPositionNode);
		
//   		TestingTools::inform($linkPositionText);
		
		//@TODO: refactor... common function... bubble-up of an error until ????
		if ($_SESSION['pw_wiki']['error']) {
			$_SESSION['pw_wiki']['error'] = false;
			return $linkPositionText.nop("Interner Link kann wegen interner Fehler nicht aufgel&ouml;st werden.");
		}
	
		$fullid = $linkPositionText;
		$modus = false;
	
		if (preg_match("#(.*)&gt;(.*)#", $linkPositionText, $xp_lpt)) {
			$modus = $xp_lpt[1];
			$fullid = $xp_lpt[2];
	
			$modi = explode("|", $moditext);
			if (!in_array($modus, $modi)) {
				return nop("Interner Link mit falschem Modus '$modus'. Erlaubte Modi sind: ".$moditext);
			}
		}
	
		if (!$fullid) {
			return nop("Interner Wikilink ohne Zielangabe. Leerer Wikilink?", false);
		}
	
		//TODO loop to catch all parts until the end of the link
		$textNode = $linkPositionNode->getNextSibling();
		$text = null;
		if ($textNode != null) {
// 			TestingTools::inform($textNode->__toString());
	// 		$parser = new ParserRule($textNode, $this->getParser());
	// 		$text = $parser->getText();
			$text = $textNode->getData();
// 			TestingTools::inform($text, "link text");
		}
	
		//@TODO: refactor... common function... bubble-up of an error until ????
		if ($_SESSION['pw_wiki']['error']) {
			$_SESSION['pw_wiki']['error'] = false;
			//@TODO: refactor... Interner Link Token einfach ausgeben ohne ihn zu verarbeiten... restore! und darin enthalten die interne Fehlermeldung!!!
			return pw_e2u($text)." ".nop("Interner Link kann wegen interner Fehler nicht aufgel&ouml;st werden.");
		}
	
	/*
		$text = "";
		for ($textpos = $lexer->nextSibling($linkpos); $textpos != null; $textpos = $lexer->nextSibling($textpos)) {
			$ret = $lexer->getText($textpos);
			if (!$ret) {
				$ret = $lexer->callFunction($textpos, ONENTRY);
				$ret .= $lexer->callFunction($textpos, ONEXIT);
	
			}
			$ret = utf8_encode(htmlentities(utf8_decode($ret)));
			$text .= $ret;
		}
	*/
	
		$found = true;
		$na = "";
		$type = "INTERNAL";
		$section = null;
		if ($fullid[0] == "#") {
			$idtxt = ltrim($fullid, "#");
			$id = pw_s2u($idtxt);
			$id = utf8_strtolower($id);
			$type = "JUMP";
	
			switch($id) {
				case "_top": $href = "#__main"; break;
				case "_bottom": $href = "#__bottom"; break;
				case "_toc": $href = "#__toc"; break;
				case "_maintitle": $href = "#__fullsite"; break;
				default:
					
					try {
	
						$item = $indextable->getByIdOrText($id);
							$section = $item->getId();
							if (!$text) {
									$text = $item->getText();
							}
							$text = pw_s2e($text);
							
					} catch (Exception $e) {
							$found = false;
							$href = "#";
							$text = pw_url2u($id);
							$na = ' class="pw_wiki_link_na"';
					}
	
				break;
			}
	
		} else {
	
			//out($fullid);
			preg_match("/(.*)#(.*)/", $fullid, $lpt);
			#out($lpt);
	
			$id = isset($lpt[1]) ? $lpt[1] : $fullid;
			$jump = "";
			if (isset($lpt[2]) and strlen($lpt[2]) > 0) {
				$jump = "#".utf8_strtolower(pw_s2url($lpt[2]));
			}
	
			$id = pw_url2t($id);
	
			// Absolute Pfadangabe...
			if ($id[0] == ':') {
				$id = ltrim($id, ':');
			} else {
				$ns = pw_wiki_getcfg('fullns');
				$id = $ns ? $ns.$id : $id;
			}
	
			$id = pw_e2u($id);
			$filename = pw_wiki_path($id, ST_FULL);
			#out($id);
			#$filename = pw_u2t($filename);
			#out(file_exists($filename), $filename);
			#out($filename);
			#out2(utf8_check($filename));
			#die();
	
			if (!file_exists($filename) and !$modus) {
				$na = ' class="pw_wiki_link_na"';
				$modus = "edit";
				$found = false;
			}
			
			if (!$text) {
				$text = pw_wiki_pg(pw_e2u($fullid));
				#out(pw_e2u($fullid));
			}
	
			$href = "?id=".pw_s2url($id).$jump;
	
		}
	
		if ($type == "JUMP" and !$text) {
			$text = pw_wiki_getcfg('anchor_text', $id);
			if (!$text) {
				$text = $item->getText();
			}
			$text = pw_s2e($text);
		}
	
	
		if ($modus == "edit" and $section) {
			$href = "?id=".pw_wiki_getcfg('id');
			$href .= "&mode=editpage&amp;section=$section";
		}
	
		if ($type == "JUMP" and !$modus and $section) {
			$href = "#header_".$section;
		}
	
		if ($type == "INTERNAL") {
			if ($modus == "edit" or !$found) {
				$href .= '&mode=editpage';
			}
			if ($modus == "showpages") {
				$href .= "&mode=showpages";
				$na = '';
			}
		}
	
	
		// AJAX-Links...
		#return '<a onclick="wikilink(\''.$fullid.'\'); return false;" href="#id='.$fullid.'"'.$na.'>'.$text.'</a>';
		#out("LPTXT=$linkpostxt; MODUS=$modus; TEXT=$text; LINK=$fullid; TYPE=$type; \nID=$id; HREF=$href; FOUND=".($found?"TRUE":"FALSE")."; SECTION=$section;");
		#return $linkpostxt.'|'.$textnode['VALUE'].' [a href="'.$href.'"'.$na.']'.$text.'[/a]';
	
		//@TODO alle hrefs encodieren und strtolower anwenden (achtung bei utf8-Sonderzeichen)
		#$href = pw_wiki_urlencode($href);
		#$text = pw_wiki_entities($text);
		#$text = pw_s2e($text);
		return '<a href="'.$href.'"'.$na.'>'.$text.'</a>';
	}

	public function onExit() {
		return '';
	}

	public function doRecursion() {
		return false;
	}

	public function getPattern() {
		return new Pattern($this->getName(), Pattern::TYPE_SECTION, '(?=\[\[)', '\]\]');
	}
	
	public function getAllowedModes() {
		return array(
				"#DOCUMENT", "tablecell", "listitem", "multiline", "bold", "underline", 
				"italic", "monospace", "small", "big", "strike", "sub", "sup", "hi", "lo", 
				"em", "bordererror", "borderinfo", "borderwarning", "bordersuccess", "bordervalidation", "border", 
				"tablecell", "tableheader", "wptableheader", "wptablecell", "align", 
				"justify", "alignintable", "indent", "left", "right", "footnote", "defitem", "defterm");
	}
}

?>