<?php
/**
 * Lexer :: Creates an Abstract Syntax Tree (=AST). Inspired by docuwiki.
 * @author Peter Moser
 * @version 0.5 - newStyle
 * @package Lexer
 */

/*
TODO: old????
abstractnodes in debuginfos aufnehmen...
abstractnodes in patterntable aufnehmen...
bessere debug infos trace damit nicht nur "getPatternInfo" steht...
AST-Schleife und debuginfo-tabelle in separate datei...

TODO: new...
* $this->ids should contain real pattern objects not just strings (_stripExitTagName etc. will be deprecated)
* move debuginfo to a separate class
* Source-code stuff to a separate class (print, showlines, etc.)
* Comments in english!
* combine Token with Node

FIXME LEXER SHOULD ONLY PRODUCE AN AST; PUT EVERYTHING ELSE TO NODE, TREE OR A NEW PARSER CLASS: LIKE GETARRAY, CALLFUNCTION, ETC.
*/

if (!defined('INC_PATH')) {
	define ('INC_PATH', './');
}

require_once INC_PATH.'pwTools/string/encoding.php';
require_once INC_PATH.'pwTools/string/TextFormat.php';
require_once INC_PATH.'pwTools/tree/Node.php';
require_once INC_PATH.'pwTools/data/Collection.php';
require_once 'timer.php';
require_once 'Token.php';
require_once 'Pattern.php';
require_once 'Log.php';

class Lexer {
	
	public static $version = "0.5 - newStyle";
	
	/**
	 * TODO Refactor = move to a new class Parser
	 * @deprecated
	 */
	const ONENTRY		= 0;
	const ONEXIT  		= 1;
	const NO_SHIFT 		= 1;
	const TEXTEMPTY		= 8;  // Im Token darf kein #Text vorkommen
	const TEXTNOTEMPTY	= 16; // Im Token muss #Text vorkommen (NOT EMPTY)
	
	private $_textInput = "";			// Given string to analyze!
	private $_textPosition = 0;			// Current text position inside the string.
	private $_currentMode;
	private $_aftermatch = "";
	private $_currentLine = "";
	private $_currentLineNumber = 0;
	private $_temptxt = "";
	private $_remtext = "";
	private $_parentStack = array();
	private $_executiontime = 0;
	private $_log;
	private $_cycle = -1;
	private $_parsed = false;
	private $_patternTable;
	private $_lastNode = null;
	private $_rootNode = null;  // root of the AST (=Abstract Syntax Tree)
	

	public function __construct($text, $loglevel = Log::INFO) {
		if (!is_string($text)) {
			throw new InvalidArgumentException("No Text to parse given! Wrong datatype!");
		}
		$this->_log = new Log($loglevel);
		$this->_patternTable = new Collection();
		$this->_patternTable->add(Token::DOC, new Pattern(Token::DOC));
		$this->_patternTable->add(Token::TXT, new Pattern(Token::TXT));
		$this->_patternTable->add(Token::EOF, new Pattern(Token::EOF));
		$this->setSource($text);
	}

	public function parse() {
		$timer = new timer();
		$this->_cycle = 0;
		$this->_parsed = false;

		do {
			$this->_cycle++;
			$token = $this->_getToken();
			if ($token) {
				$this->_currentLine = $token->getTextFull();
				$this->_updateTextPosition();
				$this->_addNodeOnOpen($token);
				$this->_addNodeOnClose($token);
				echo TextFormat::preFormat($token);
			}
			$this->_executiontime = $timer->measure_elapsed(4);
		#} while($this->_cycle <= 6);
		} while($token);

		$this->_parsed = true;
	}

	public function getExecutionTime() {
		return $this->_executiontime;
	}

	public function getRootNode() {
		if (!$this->_parsed) {
			$this->parse();
		}
		return $this->_rootNode;
	}

	public function getSource() {
		return $this->_textInput;
	}

	public function setSource($source) {

		if (!is_string($source)) {
			throw new InvalidArgumentException("First argument must be string!");
		}

		pw_normalizeLE($source);
		$source = "\n".$source."\n";
		$this->_textInput = $source; 
		$this->_temptxt = $source;

		$node = new Node(Token::DOC);
		$this->_rootNode = $node;
		$this->_lastNode = $node;
		$this->_parentStack = array();
		
		$this->_log->add("STARTING: Lexer v".$this->getVersion().", debugLevel = ".$this->_log->getLogLevelAsString());
	}


	public static function getVersion() {
		return self::$version;
	}


	public function __tostring() {
		return "Lexer - Version: ".$this->getVersion();
	}

	/**
	 * Fetches a named token out of the result of regexp match! 
	 * All special fields will be stored in CONFIG.
	 *
	 * @param array $regexpMatch Matches
	 */
	private function _getNamedToken($regexpMatch) {
		if (!is_array($regexpMatch)) {
			throw new InvalidArgumentException("Wrong datatype!");
		}
		
		if (empty($regexpMatch) && $this->_currentMode->getName() == Token::DOC) {
			// Nothing matched: EOF reached!
			return new Token(Token::EOF, $this->_textInput, $this->_textInput, 1);
		} 

		$name = $this->_getTokenName($regexpMatch);
		$regexpMatch = $this->_cleanupArray($regexpMatch);
		$beforeMatch = $regexpMatch[1];
		$completeMatch = $regexpMatch[0];
		$conf = array_slice($regexpMatch, 2, -1);
		
		return new Token($name, $beforeMatch, $completeMatch, $conf);
	}

	private function _getTokenName($m) {
		foreach ($this->_currentMode->getModes() as $key => $id) {
			if ($m[$key][1] != -1) {
				return $id->getName();
			}
		}
		throw new Exception("ID not found in patternorder-table!");
	}

	private function _cleanupArray($m) {
		$out = array();

		foreach ($m as $i) {
			if ($i[1] != -1) {
				$out[] = $i[0];
			}
		}
		return $out;
	}

	private function _getToken() {

		// Find Entrance or Exit of a Section...
		$parent = $this->_getParentFromStack();
		$pattern = $this->_patternTable->get($parent->getName());

		if ($pattern->isAbstract()) {
			$parent = $parent->getParent();
		}

		$pattern = $this->_patternTable->get($parent->getName());
		$this->_currentMode = $pattern;
		
		$regex = $this->_currentMode->getRegexp();
		#out($this->_temptxt);
		#out($this->_currentMode->getName());
		if (!preg_match($regex, $this->_temptxt, $m, PREG_OFFSET_CAPTURE) && $pattern->getName() != Token::DOC) {
			$pattern = $this->_patternTable->get($parent->getName());
			$expected = stripslashes($pattern->getExit());
			$found = substr($this->_temptxt, 0, strlen($expected));
			$expected = pw_s2e_whiteSpace($expected);
				
			$dbginf = array(
				'TYPE' 		=> 'Syntax',	// TODO use constants not strings for dbginf types!
				'DESC'		=> "The Mode '{$pattern->getName()}' has been started here, but wasn't ever ended!",
				'LINENR' 	=> $this->_currentLineNumber,
				'TXTPOS' 	=> $this->_textPosition,
				'ENTRYNODE' => $parent,
				'PATTERN'	=> $pattern,
				//'ENTRYTOKEN' => ... TODO started @ line ".$dientry['LINENR']."; textposition = ".$dientry['TXTPOS'] save debuginfo inside a tokenlist.
			);

			$errorMsg = "Exit of $pattern not found: '$expected' expected but '$found' found @$this->_textPosition (line $this->_currentLineNumber).";
			$this->_log->addError($errorMsg, $dbginf);
			throw new Exception($errorMsg);
		}

		#out($m);
		$token = $this->_getNamedToken($m);
		
		if ($token->getTextLength() == 0 && $this->_lastNode->getName() == $token->getName()) {
			$errorMsg = "Textpointer has not moved for pattern '".$token->getName()."'. Try the NO_RESTORE flag.";
			$this->_log->addError($errorMsg);
			throw new Exception($errorMsg);
		}

		$this->_temptxt = substr($this->_temptxt, $token->getTextLength());

		$debugInfo = array(
			"LINENR"       => $this->_currentLineNumber,
			"LASTNODE"     => $this->_lastNode,
			"PARENT"       => $parent,
			"TOKEN"        => $token,
			"TXTPOS"       => $this->_textPosition,
			"PARENTSTACK"  => $this->_parentStack,
		);
		$this->_log->addDebug($this->_logFormat("TOKEN FOUND", "$token @$this->_textPosition"), $debugInfo);

		// eof reached...
		if ($token->getName() == Token::EOF) {
			$this->_log->addInfo("FINISHED: @$this->_textPosition (line $this->_currentLineNumber)");
			return null;
		}
		
		$this->_textPosition += $token->getTextLength();
		
		return $token;
	}

	public function connectTo($name, $to) {
		$pattern = $this->_patternTable->get($name);
		
		if ($pattern->isAbstract()) {
			$logText = "CONNECTTO: Can't connect two ABSTRACT Nodes! '$name->$to' failed!";
			$this->_log->addWarning($logText);
			throw new Exception($logText);
		}

		if ($pattern->getConnectTo() !== null) {
			$this->_log->addInfo("CONNECTTO for '$name->".$this->_patternTable->get($name)->getConnectTo()."' already set in patterntable! Will be altered to '$name->$to'!");
		}

		$pattern->setConnectTo($to);
		$this->_patternTable->add($to, new Pattern($to, Pattern::TYPE_ABSTRACT), Collection::UPDATE);
		
		$this->_log->addInfo($this->_logFormat("CONNECTTO", "'$name->$to' connected."));
	}

	public function addWordPattern($name, $entryPattern, $flags = 0) {
		$newPattern = new Pattern($name, Pattern::TYPE_WORD, $entryPattern);
		$this->addPattern($newPattern);
	}

	public function addLinePattern($name, $entrypattern, $exitpattern='\n', $flags = 0) {
		$newPattern = new Pattern($name, Pattern::TYPE_LINE, $entrypattern, $exitpattern);
		$this->addPattern($newPattern);
	}
	
	public function addSectionPattern($name, $entrypattern, $exitpattern, $flags = 0) {
		$newPattern = new Pattern($name, Pattern::TYPE_SECTION, $entrypattern, $exitpattern);
		$this->addPattern($newPattern);
	}
	
	
	public function addPattern($pattern) {
		if (!($pattern instanceof Pattern)) {
			throw new InvalidArgumentException("Invalid pattern object given!");
		}
			
		switch ($pattern->getType()) {
			case Pattern::TYPE_WORD:
				$restore = "";
				if (substr($pattern->getEntry(), -2) == '\n') {
					$restore = "\n";
				}
				$pattern->setRestore($restore);
			break;
			case Pattern::TYPE_LINE:
				$restore = "\n";
				$exitpattern = $pattern->getExit();
				$entrypattern = $pattern->getEntry();
				if (substr($exitpattern, -2) != '\n') {
					$exitpattern .= '\n';
				}
				
				$lookaheadexit = substr($exitpattern, 0, -2);
				if ($pattern->getFlags() & self::TEXTNOTEMPTY) {
					$entrypattern .= '(?=[^\n]+'.$lookaheadexit.'\n)';
				} else {
					$entrypattern .= '(?=[^\n]*'.$lookaheadexit.'\n)';
				}
				
				// Linepattern m?ssen immer mit einer Newline anfangen...
				if (substr($entrypattern, 0, 2) != '\n') {
					$entrypattern = '\n'.$entrypattern;
				}
				
				// Alle .* ersetzen mit [^\n]* --> damit der exitpattern in derselben Zeile bleiben muss.
				// @TODO: was noch????
				$entrypattern = str_replace('.*', '[^\n]*', $entrypattern);
				
				$pattern->setEntry($entrypattern);
				$pattern->setExit($exitpattern);
				$pattern->setRestore($restore);
			break;
			case Pattern::TYPE_SECTION:
				$restore = "";
				if (substr($pattern->getExit(), -2) == '\n') {
					$restore = "\n";
				}
				
				$pattern->setRestore($restore);
			break;
			default:
				throw new InvalidArgumentException("Pattern object must be of the following types: TYPE_SECTION, TYPE_LINE or TYPE_WORD!");
		}
		$this->_patternTable->add($pattern->getName(), $pattern);
		$this->_log->addInfo($this->_logFormat("ADD PATTERN", $pattern));
	}

	
	public function setAllowedModes($name, $modes) {
		if (! is_array($modes) or ! is_string($name)) {
			throw new InvalidArgumentException("Modename must be string and modes must be an array!");
		}
		$pattern2Add = $this->_patternTable->get($name);
		foreach ($modes as $mode) {
			$pattern = $this->_patternTable->get($mode);
			$pattern->addMode($pattern2Add);
		}
	}

	public function getPatternTable() {
		$this->_patternTable->sort();
		return $this->_patternTable;
	}

	public function getPatternTableAsString() {
		$ptable = $this->getPatternTable();
		$out = "";
		foreach ($ptable as $i => $p) {
			$out .= "[$i]$p\n";
		}
		return $out;
	}

	/**
	 * 
	 * @param string $name
	 * @return boolean
	 */
	private function _addAbstractNode($name) {

		$pattern = $this->_patternTable->get($name);
		$connectToName = $pattern->getConnectTo(); // TODO getConnectTo must return a Pattern!
		$parent = $this->_getParentFromStack();
		if ($connectToName === null || $parent->getName() == $connectToName)
			return false;

		$node = new Node($connectToName);
		$parent->addChild($node);
		$this->_lastNode = $node;

		$this->_parentStackAdd($node);

		$this->_log->addDebug($this->_logFormat("ADD #ABSTRACT", "$node @$this->_textPosition"), array(
			"LASTNODE"	   => $this->_lastNode,
			"PARENT"       => $parent,
			"PARENTSTACK"  => $this->_parentStack
		));
	}
	
	private function _parentStackAdd($node) {
		if (end($this->_parentStack) === $node) {
			throw new Exception("The node '$node' already exists in parent stack!");
		}
		$this->_parentStack[] = $node;
	}

	private function _parentStackRemove() {
		array_pop($this->_parentStack);
	}

	
	/**
	 * 
	 * @return Ambigous <NULL, mixed>
	 */
	private function _getParentFromStack() {
		$parent = end($this->_parentStack);
		return ($parent == false ? $this->_rootNode : $parent);
	}

	/**
	 * 
	 * @param $token
	 * @return boolean
	 */
	private function _addNodeOnOpen($token) {
		// Exit-token found. Do nothing!
		if ($token->isExit()) {
			return;
		}

		$this->_addTextNode($token, false, false, true);

		$pattern = $this->_patternTable->get($token->getName());
		$parent = $this->_getParentFromStack();
		$parentPattern = $this->_patternTable->get($parent->getName());
		
		if ($pattern->hasConnectTo()) {
			if ($parentPattern->isAbstract() && $parent->getName() != $pattern->getConnectTo()) {
				$this->_parentStackRemove();
			}
			$this->_addAbstractNode($token->getName());
			$parent = $this->_getParentFromStack();
		} else {
			if ($parentPattern->isAbstract()) {
				$this->_parentStackRemove();
			}
		}

		$node = new Node($token->getName(), $token->getConfig());
		$parent->addChild($node);
		$this->_lastNode = $node;
		$this->_log->addDebug($this->_logFormat("ADD NODE", "$node to $parent"));

		// WORD-pattern werden gleich nach dem ?ffnen wieder geschlossen.
		// Keine parentID wird in den Stapel aufgenommen.
		$pattern = $this->_patternTable->get($token->getName());
		if ($pattern->getType() == Pattern::TYPE_WORD) {
			// Backstep... Manche Matches m?ssen nach der Erkennung eines Exit-Tags
			// f?r den n?chsten Entry-Tag bewahrt bleiben.
			if ($pattern->getRestore() != "") {
				$this->_textPosition -= strlen($token->getTextFull());
				$this->_temptxt = $token->getTextFull().$this->_temptxt;
			}
		} else {
			$this->_parentStackAdd($node);
			
		}
	}
	
	
	/**
	 * F?gt ein Textknoten hinzu und h?ngt ein neues "child" beim "PARENT" an.
	 * TODO: sollte ?ber eine public function ?nderbar sein!!!!
	 *
	 * @param Token    	      $token       Einfachen String oder Token-Array (enth?lt Value und Length)
	 * @param boolean  		  $addemptystring   TRUE, wenn leere Textknoten erstellt werden sollen.
	 * @param boolean         $addnewlines      TRUE, wenn reine Newlines '\n' (ohne Leerzeichen) aufgenommen werden sollen.
	 * @param boolean         $addspacelines    TRUE, wenn Leerzeilen = Leerzeichen+Newline aufgenommen werden sollen.
	 *
	 * @return boolean  TRUE on success
	 */
	private function _addTextNode($token, $addemptystring = false, $addnewlines = false, $addspacelines = false) {

		if (!($token instanceof Token)) {
			throw new InvalidArgumentException("First parameter must be an instance of Token!");
		}

		// Erzeuge einen Textknoten aus den neuen und alten Textfragmenten,
		// damit nicht zu viele Textknoten in der selben Hierarchie erzeugt
		// werden. L?sche danach den Textbuffer.
		$text = $this->_remtext.$token->getTextString();
		$length = strlen($text);
		$this->_rememberText(false);

		if ($length == 0 and $addemptystring == false) {
			return;
		}

		$trimtext = trim($text, ' ');

		// Nur Newline entdeckt...
		if ($trimtext == "\n" and $addnewlines == false) {
			return;
		}

		// Nur Leerzeichen entdeckt...
		if ($trimtext == "" and $addspacelines == false) {
			return;
		}

		// Textknoten sind in abstrakten Knoten nicht erlaubt...
		// Abstrakte Knoten werden zuerst geschlossen.
		$parent = $this->_getParentFromStack();
		$pattern = $this->_patternTable->get($parent->getName());
		if ($pattern->isAbstract()) {
			$this->_parentStackRemove();
			$parent = $parent->getParent();
		}

		$node = new Node(Token::TXT, $text);
		#$node = new Token(Token::TXT, $text);
		$parent->addChild($node);
		$this->_lastNode = $node;
		$this->_log->addDebug($this->_logFormat("ADD #TEXT", $node));
	}

	private function _rememberText($text) {
		if ($text === false) {
			$this->_remtext = "";
			return true;
		}

		if (! is_string($text))
			return false;

		$this->_remtext .= $text;
		return true;
	}


	private function _addNodeOnClose($token) {

		// Entry-token found? Do nothing!
		if ($token->isEntry()) {
			return;
		}

		// Backstep... Manche Matches m?ssen nach der Erkennung eines Exit-Tags
		// f?r den n?chsten Entry-Tag bewahrt bleiben.
		$pattern = $this->_patternTable->get($token->getName());
		if ($pattern->getRestore() != "") {
			$this->_textPosition -= strlen($pattern->getRestore());
			$this->_temptxt = $pattern->getRestore().$this->_temptxt;
		}

		$this->_addTextNode($token, false, false, true);

		// Close abstract parent-nodes, if a new mode has to be started!
		$connectToName = $pattern->getConnectTo();
		$parent = $this->_getParentFromStack();
		if ($connectToName === null or $connectToName != $parent->getName()) {
			$parentPattern = $this->_patternTable->get($parent->getName());
			if ($parentPattern->isAbstract()) {
				$this->_parentStackRemove();
			}
		}
		
		$this->_parentStackRemove();
		$this->_log->addDebug($this->_logFormat("CLOSE NODE", $parent));
	}

	private function _updateTextPosition() {

		$this->_aftermatch = $this->_temptxt;

		// Length Match-String and Rest
		$lenmarest = strlen($this->_aftermatch.$this->_currentLine);
		$beforeMatch = substr($this->_textInput, 0, strlen($this->_textInput) - $lenmarest);

		preg_match_all("#\n#", $beforeMatch.$this->_currentLine, $lines);
		$lines = $lines[0];
		$this->_currentLineNumber = count($lines);
		if (substr($beforeMatch.$this->_currentLine, -1) == "\n") {
			$this->_currentLineNumber--;
		}
	}
	
	private function _logFormat($command, $info) {
		return sprintf("[%3d][%-15s]%s", $this->_cycle, $command, $info); 
	}
	
	public function getLog() {
		return $this->_log;
	}

}

?>