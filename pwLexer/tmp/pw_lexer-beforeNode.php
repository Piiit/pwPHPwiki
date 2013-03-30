<?php

/**
 * This is the LEXER file. Inspired by docuwiki.
 * @author Peter Moser
 * @package pwLexer
 */


#if(!defined('LEXER_INC')) {
#  define ('LEXER_INC', "../");
#}

if (!defined('INC_PATH')) {
	define ('INC_PATH', '.');
}



#require_once LEXER_INC."pw_debug.php";
#require_once LEXER_INC."timer.php";
#require_once LEXER_INC."../bin/encoding.php";
#require_once LEXER_INC."lexerutils.php";
require_once INC_PATH."/pw_debug.php";
require_once INC_PATH."/timer.php";
require_once INC_PATH."/encoding.php";
require_once INC_PATH."/Node.php";

#require_once LEXER_INC."lexer_testtext.php";

#pw_debug_init(true);

#define("SECTION",       1);  // Eine "section" kann ?ber mehrere Zeilen laufen, z.B. <nowiki>...</nowiki> oder **fett**
#define("LINE",          2);  // Eine "line" ist genau eine Zeile lang (enter und leave state muss in der selben zeile sein), z.B. = ?berschrift =
#define("WORD",          4);  // Ein "word" ist eine einfache Ersetzung von Zeichenketten, z.B. smiley :)
define("NO_SHIFT",      1);
define("TEXTEMPTY",     8);  // Im Token darf kein #Text vorkommen
define("TEXTNOTEMPTY", 16);  // Im Token muss #Text vorkommen (NOT EMPTY)
define("ONENTRY", 0);
define("ONEXIT", 1);

/**
 * pwLexer :: Creates a DOM-Tree out of a wiki-textfile. See pwWiki for syntax!
 * @author Peter Moser
 * @version 0.42d
 * @package pwLexer
*/
class pwLexer {

	protected $fulltxt = "";
	protected $fulltxtlength = 0;
	protected $patternorder = 2;  // Matching-Array: 0 => Gesamter String (f?r RESTORE), 1 => Text (content)
	protected $patterntable = array( "#DOCUMENT" => array("NAME" => "#DOCUMENT", "TYPE" => "TYPE_BASE", "MODES" => array()),
			"#TEXT"     => array("NAME" => "#TEXT",     "TYPE" => "TYPE_BASE")
	);
	protected $beforematch = "";
	protected $aftermatch = "";
	protected $currentline = "";
	protected $temptxt = "";
	protected $remtext = "";
	protected $parentstack = array();
	protected $executiontime = 0;
	protected $logbook = array();
	protected $error = "";
	protected $sectiontable = array();
	protected $linenr = 0;
	protected $debug = false;
	protected $parsed = false;
	private $dbginf = array();
	public static $version = "0.42d - PIWO";
	public $AST = array();  // AST: Abstract Syntax Tree

	/**
	 * Returns the sectiontable which was set by the registerSection-function.
	*/
	public function getSectionTable() {
		return $this->sectiontable;
	}

	public function printSectionTable() {
		if( !pw_debug($this->sectiontable, "SectionTable") )
			$this->addLog("WARNING", "Can not print tables if pw_debug mode set to 'off'");
	}

	public function registerSection($name, $type = "NORMAL") {
		if (!is_string($name) or strlen($name) == 0 or array_key_exists($name, $this->sectiontable) or !in_array($type, $this->sectiontypes))
			return false;

		$this->sectiontable[$name] = $type;
		return true;

	}

	public function __construct($fulltxt, $debug = false) {

		if (!is_string($fulltxt)) {
			$this->addLog("FATAL", "No Text to parse given!");
		}

		$this->addLog("INFO", "START pwLexer v".$this->getVersion()."\nDocumenttext following:\n".$fulltxt);
		$this->reset($fulltxt, $debug);

	}

	public function parse($cdata = true) {

		$timer = new timer();
		if ($cdata) {
			$this->replaceCDATA();
		}
		$this->cycle = 0;
		$this->parsed = false;

		do {
			$this->cycle++;
			$tag = $this->getToken($this->temptxt);
				
			if ($tag) {
				$this->currentline = $tag["RESTORE"];
				$this->updateDomTree($tag, $this->temptxt);
			}
			$this->executiontime = $timer->measure_elapsed(4);
				
			if ($this->error == "FATAL")  return false;

			#} while($this->cycle <= 15);
		} while($tag);

		#echo "</table>";

		$this->parsed = true;
		var_dump($this->AST);

	}

	protected function replaceCDATA() {
		if (preg_match_all("/<!\[CDATA\[(.*?)\]\]>/s", $this->fulltxt, $m)) {
			$this->AST["_CDATA_"] = $m[1];
			$n = 0;
			$this->temptxt = preg_replace("/(?!<\\\\)<!\[CDATA\[.*?\]\]>/se", "'#~-CDATA:'.(\$n++).'-~#'", $this->fulltxt);
			//out($this->temptxt);
		}
	}

	public function getCDATA() {
		if (!isset($this->AST["_CDATA_"]))
			return false;

		return $this->AST["_CDATA_"];
	}

	public function getExecutionTime() {
		if ($this->error == "FATAL") {
			return "[FATALERROR: No executiontime avaiable. See Logfile!]";
		}

		return $this->executiontime;
	}

	public function printExecutionTime() {
		echo "Execution Time: $this->executiontime sec.";
	}

	public function printSource($showlines = false) {
		$text = $this->getSource($showlines);
		echo "<pre>".htmlentities($text)."</pre>";
	}

	public function getDOM() {
		return $this->AST;
	}

	public function printDOM() {
		if( !pw_debug($this->AST, "DOMTREE") )
			$this->addLog("WARNING", "Can not print tables if pw_debug mode set to 'off'");
	}

	public function getSource($showlines = false) {

		if (!$showlines)
			return $this->fulltxt;

		$text = explode("\n", $this->fulltxt);

		// Ende und Anfang werden intern hinzugef?gt...
		// also auch nicht ausgegeben!
		#unset($text[0]);
		#unset($text[count($text)]);

		$t = "";
		foreach($text as $k => $line) {
			$t .= sprintf("%5d %s\n", $k, $line);
		}

		return $t;
	}

	public function reset($fulltxt, $debug = false) {

		if (!is_string($fulltxt)) {
			throw new Exception("First argument must be string!");
		}

		$fulltxt = "\n".str_replace("\r\n","\n",$fulltxt)."\n";
		$this->fulltxt = $this->temptxt = $this->aftermatch = $fulltxt;
		$this->fulltxtlength = strlen($this->fulltxt);

		$this->AST = array();
		$this->AST[0] = array("NAME" => "#DOCUMENT", "ID" => 0, "PARENT" => null);
		$this->parentstack = array(0);

		$this->debug = $debug;
	}


	public static function getVersion() {
		return self::$version;
	}


	function __tostring() {
		return "pwLexer - Version: ".$this->getVersion();
	}

	protected function getType($name) {
		if (!array_key_exists($name, $this->sectiontable))
			return false;

		return $this->sectiontable[$name];
	}


	/**
	 * Erzeugt ein Standard-Node f?r die DOM
	 * Alle speziellen Felder werden unter CONFIG gespeichert.
	 * Diese k?nnen dann ?ber eine aufgerufene user_function verarbeitet werden.
	 *
	 * @param array $m Matches
	 */
	protected function getNamedToken($m) {

		#pw_debug($m);

		$id = $this->getTokenName($m);

		if ($id == false) {
			return false;
		}

		$m = $this->cleanupArray($m);

		#pw_debug($m);

		// Die ersten beiden Felder sind Standard-Felder (RESTORE & TEXT)
		// Mit dem letzten Feld wird im Text weitergelesen...
		// Verarbeitete Bereiche werden abgeschnitten (siehe $this->temptxt)
		$conf = array_slice($m, 2, -1);
		#pw_debug($id);
		$txtlength = strlen($m[1]);

		return array ( "NAME"       => $id,
				"RESTORE"    => $m[0],
				"TEXT"       => $m[1],
				"TEXTLENGTH" => $txtlength,
				"TOKEN"      => substr($m[0], $txtlength),
				"CONFIG"     => $conf
		);

	}

	protected function getTokenName($m) {

		if (!is_array($m) or count($m) == 0) {
			$this->addLog("WARNING", "Wrong value for first parameter!");
			return false;
		}

		foreach ($this->ids as $key => $id) {
			#pw_debug("$key; $id");
			if ($m[$key][1] != -1) {
				return $id;
			}
		}

		$this->addLog("FATAL", "ID not found in patternorder-table!");
		return false;

	}

	protected function cleanupArray($m) {
		$out = array();

		foreach ($m as $i) {
			if ($i[1] != -1) {
				$out[] = $i[0];
			}
		}
		return $out;
	}

	protected function getToken(&$txt) {

		// eof erreicht... nichts zu tun!
		if (strlen($txt) == 0 /*or $txt == "\n"*/) {
			$this->addLog("INFO", "__EOF__ @ line {$this->linenr}. Debugmode ".($this->debug?"ON":"OFF").".");
			return;
		}

		// Find Entrance or Exit of a Section...
		$pid = $this->getParentID();
		$type = $this->getPatternInfo($this->AST[$pid]["NAME"], "TYPE");

		if ($type == "TYPE_ABSTRACT") {
			$pid = $this->AST[$pid]["PARENT"];
		}

		$mode = $this->getPatternInfo($this->AST[$pid]["NAME"], "NAME");

		$regex = $this->getPatternString($mode);
		if (! $regex) {
			pw_debug($this->AST);
			$this->addLog("FATAL", "Empty Regular Expression found for '$mode' (PID=$pid).");
			return false;
		}
		if (! preg_match($regex, $txt, $m, PREG_OFFSET_CAPTURE)) {
			$this->addLog("WARNING", "Nothing matched! MODE = $mode; REGEXP = ".$regex."; TEXT = '".str_replace("\n", '\n', $txt)."'");

			$expected = stripslashes($this->getPatternInfo($mode, "EXIT"));
			$expected = htmlspecialchars(str_replace("\n", '\n', $expected));
			$found = substr($txt, 0, strlen($expected));
			$found = htmlspecialchars(str_replace("\n", '\n', $found));


			// @TODO: getDebugInfo liefert keine Zeile etc.... falls debug=false ::: BUG!!!
			$di = $this->getDebugInfo();

			if ($di) {

				$di = end($di);

				// @TODO: Suche innerhalb von dbginf als Funktion ausprogrammieren. evtl. ges. Debug als externes Objekt einbinden!
				// @TODO: Syntaxfehler (und andere) nicht als reinen Text speichern, sondern ARRAY mit Informationen dazugeben (f?r i18n und Ausgabe im aufrufenden Programm).
				$entry = null;
				$entryid = array_pop($di['PARENTSTACK']);
				foreach ($this->dbginf as $i => $dbg) {
					if ($dbg['ID'] == $entryid) {
						$entry = $i;
						continue;
					}
				}
				if (isset($this->dbginf[$entry])) {
					$dientry = $this->dbginf[$entry];
					$data = array('TYPE' => 'Syntax', 'LINENR' => $dientry['LINENR'], 'TXTPOS' => $dientry['TXTPOS'], 'TEXT' => "Der Modus '$mode' wurde hier gestartet, aber nie beendet.");
					$this->addLog("FATAL", "Syntax-Error @ line ".$di['LINENR']."; textposition = ".$di['TXTPOS'].". '$mode' exittoken not found (started @ line ".$dientry['LINENR']."; textposition = ".$dientry['TXTPOS'].") '$expected' expected but '$found' found.", $data);
				}

			} else {
				$this->addLog("WARNING", "DebugInfo requested, but debug set to FALSE! Can't return Debug Information...");
			}

			$this->addLog("FATAL", "Syntax-Error! '$mode' exittoken not found: '$expected' expected but '$found' found. DebugInfo not avaiable...");

			return false;
		}

		$tag = $this->getNamedToken($m);
		#pw_debug($tag);


		$flag = $this->getPatternInfo($tag['NAME'], "FLAGS");
		#pw_debug($flag xor NO_SHIFT, $tag['NAME']);

		if ($tag !== false) {

			$pos = end($m);
			$pos = $pos[1];

			$this->textposition += $pos;

			if ($this->textposition == $pos) {
				$last = end($this->AST);
				if ($last['NAME'] == $tag['NAME']) {
					pw_debug($tag, $this->textposition.":".$pos);
					pw_debug(end($this->AST), "LAST");
					$this->addLog("FATAL", "Textpointer has not moved for pattern '".$tag['NAME']."'. Try the NO_RESTORE flag.");
				}
			}

			#if ($flag & NO_SHIFT) {
			#} else {
			$txt = substr($txt, $pos);
			#}
		}

		return $tag;

	}

	protected function countPatternLevel($pattern) {
		// Alle (...) z?hlen, au?er lookarounds (?...)!
		// @TODO: ERROR-Handling + Nesting-Level (...(...)...) + OR-Operator
		preg_match_all('/\([^\?][^\)]*\)|\(\)/', $pattern, $cox);
		#pw_debug($cox[0]);
		#pw_debug($pattern);
		return count($cox[0]);
	}

	protected function updatePatternOrder($pattern, $name, $isexit = false) {

		if (!is_bool($isexit) or !is_string($pattern) or !is_string($name)) {
			$this->addLog("FATAL", "Wrong datatype!");
		}

		$origname = $name;
		if ($isexit) {
			$name = "__exit__$name";
		}

		$this->ids[$this->patternorder] = $name;
		$this->patternorder += $this->patterntable[$origname]["LEVEL"];

	}

	public function connectTo($name, $to) {
		#pw_debug($this->patterntable);
		if (! array_key_exists($name, $this->patterntable)) {
			$this->addLog("WARNING", "Key '$name' doesn't exist in patterntable!");
			return false;
		}

		if ($this->patterntable[$name]['TYPE'] == "TYPE_ABSTRACT") {
			$this->addLog("WARNING", "CONNECTTO: Can't connect two ABSTRACT Nodes! '$name->$to' failed!");
			return false;
		}

		if ($this->patterntable[$name]['CONNECTTO'] != false) {
			$this->addLog("INFO", "CONNECTTO for '$name->".$this->patterntable[$name]['CONNECTTO']."' already set in patterntable! Will be altered to '$name->$to'!");
		}

		$this->patterntable[$name]['CONNECTTO'] = $to;
		$this->patterntable[$to] = array("NAME" => $to, "TYPE" => "TYPE_ABSTRACT");
		$this->addLog("INFO", "CONNECTTO: '$name->$to' connected.");
		return true;
	}

	protected function checkFlag($flags, $setto) {
		if ($flags & $setto) {
			return true;
		}
		return false;

	}

	public function addWordPattern($name, $pattern, $flags = 0) {

		if (strlen($pattern) == 0) {
			$this->addLog("WARNING", "Patternstringlength == 0! Pattern not added. name='$name', pattern='$pattern'");
			return false;
		}

		$restore = "";
		if (substr($pattern, -2) == '\n') {
			#if ($this->checkFlag($flags, NO_SHIFT)) {
			$restore = "\n";
			#}
		}


		$cpl = $this->countPatternLevel($pattern);
		if ($cpl == 0) {
			$pattern .= '()';
			$cpl = 1;
		}

		if (isset($this->patterntable[$name])) {
			$this->addLog("WARNING", "Old word pattern '$name' already exists. It will be changed!");
		}

		// falls bereits Mode-Eintr?ge existieren aufnehmen...
		$oldmodes = null;
		if (isset($this->patterntable[$name]["MODES"])) {
			$oldmodes = $this->patterntable[$name]["MODES"];
		}
		$this->patterntable[$name] = array( "NAME"      => $name,
				"ENTRY"     => $pattern,
				"TYPE"      => "TYPE_WORD",
				"RESTORE"   => $restore,
				"CONNECTTO" => false,
				"LEVEL"     => $cpl,
				"FLAGS"     => $flags,
				"MODES"     => $oldmodes
		);

		$this->addLog("INFO", "New word pattern added: name='$name', pattern='$pattern', flags='".sprintf("%06b", $flags)."'");
		return true;


	}

	public function addLinePattern($name, $entrypattern, $exitpattern='\n', $flags = 0) {

		// @TODO: check f?r bereits existierende pattern!
		// DONE: exitpattern greifen erst, wenn sie bereits ge?ffnet wurden...

		if (strlen($entrypattern) == 0) {
			$this->addLog("WARNING", "Patternstringlength == 0! Pattern not added. name='$name', entry='$entrypattern', exit='$exitpattern', type='$type'");
			return false;
		}

		$restore = "\n";
		if (substr($exitpattern, -2) != '\n') {
			$exitpattern .= '\n';
		}

		// TEXTEMPTY: Falls ein Textknoten im Modus vorkommen darf, muss der lookahead Zeichen enthalten,
		// ansonsten muss gleich nach dem entrypattern der exitpattern gefunden werden. --> addWordPattern
		// Bsp. bei NEWLINE wird dem entrypattern '\n *' '(?=[\n]+) hinzugef?gt, neue Regexp -> '\n *(?=[\n]+)'
		#if ($flags & TEXTEMPTY) {
		#  $entrypattern .= '(?=['.$exitpattern.']+)';
		#} else {
		$lookaheadexit = substr($exitpattern, 0, -2);
		if ($flags & TEXTNOTEMPTY) {
			$entrypattern .= '(?=[^\n]+'.$lookaheadexit.'\n)';
		} else {
			$entrypattern .= '(?=[^\n]*'.$lookaheadexit.'\n)';
		}
		#}

		// Linepattern m?ssen immer mit einer Newline anfangen...
		if (substr($entrypattern, 0, 2) != '\n') {
			$entrypattern = '\n'.$entrypattern;
		}

		// Alle .* ersetzen mit [^\n]* --> damit der exitpattern in derselben Zeile bleiben muss.
		// @TODO: was noch????
		$entrypattern = str_replace('.*', '[^\n]*', $entrypattern);

		$cpl = $this->countPatternLevel($entrypattern);
		if ($cpl == 0) {
			$entrypattern .= '()';
			$cpl = 1;
		}

		if (isset($this->patterntable[$name])) {
			$this->addLog("WARNING", "Old line pattern '$name' already exists. It will be changed!");
		}

		// falls bereits Mode-Eintr?ge existieren aufnehmen...
		$oldmodes = null;
		if (isset($this->patterntable[$name]["MODES"])) {
			$oldmodes = $this->patterntable[$name]["MODES"];
		}

		$this->patterntable[$name] = array( "NAME"      => $name,
				"ENTRY"     => $entrypattern,
				"EXIT"      => $exitpattern,
				"TYPE"      => "TYPE_LINE",
				"RESTORE"   => $restore,
				"CONNECTTO" => false,
				"LEVEL"     => $cpl,
				"FLAGS"     => $flags,
				"MODES"     => $oldmodes
		);

		$this->addLog("INFO", "New line pattern added: name='$name', entry='$entrypattern', exit='$exitpattern', flags='".sprintf("%06b", $flags)."'");
		return true;

	}

	public function setAllowedModes($name, $modes, $initself = false) {
		if (! is_array($modes) or ! is_string($name)) {
			$this->addLog("WARNING", "Wrong datatype!");
			return false;
		}
		if (! array_key_exists($name, $this->patterntable)) {
			$this->addLog("WARNING", "Name '$name' does not exist in patterntable!");
			return false;
		}

		foreach ($modes as $mode) {

			if (! array_key_exists($mode, $this->patterntable)) {
				$this->addLog("WARNING", "setAllowedModes for '$name': Mode '$mode' does not exist in patterntable!");
				continue;
			}

			if (! isset($this->patterntable[$mode]["MODES"])) {
				$this->patterntable[$mode]["MODES"] = array();
			}

			// M?glicher Deadlock! Sich selbst aufzunehmen ist nicht erlaubt...
			if ($mode != $name or $initself) {
				array_push($this->patterntable[$mode]["MODES"], $name);
			}
		}

		return true;
	}

	protected function addPattern($name, $entrypattern, $exitpattern, $flags = 0) {
	}

	public function addSectionPattern($name, $entrypattern, $exitpattern, $flags = 0) {

		// @TODO: check f?r bereits existierende pattern!
		// DONE: exitpattern greifen erst, wenn sie bereits ge?ffnet wurden...

		if (strlen($entrypattern) == 0 or strlen($exitpattern) == 0) {
			$this->addLog("WARNING", "Patternstringlength == 0! Pattern not added. name='$name', entry='$entrypattern', exit='$exitpattern', type='$type'");
			return false;
		}

		$restore = "";
		if (substr($exitpattern, -2) == '\n') {
			$restore = "\n";
		}

		$cpl = $this->countPatternLevel($entrypattern);
		if ($cpl == 0) {
			$entrypattern .= '()';
			$cpl = 1;
		}

		if (isset($this->patterntable[$name])) {
			$this->addLog("WARNING", "Old section pattern '$name' already exists. It will be changed!");
		}

		// falls bereits Mode-Eintr?ge existieren aufnehmen...
		$oldmodes = null;
		if (isset($this->patterntable[$name]["MODES"])) {
			$oldmodes = $this->patterntable[$name]["MODES"];
		}

		$this->patterntable[$name] = array( "NAME"      => $name,
				"ENTRY"     => $entrypattern,
				"EXIT"      => $exitpattern,
				"TYPE"      => "TYPE_SECTION",
				"RESTORE"   => $restore,
				"CONNECTTO" => false,
				"LEVEL"     => $cpl,
				"FLAGS"     => $flags,
				"MODES"     => $oldmodes
		);

		$this->addLog("INFO", "New section pattern added: name='$name', entry='$entrypattern', exit='$exitpattern', flags='".sprintf("%06b", $flags)."'");
		return true;
	}

	public function getPatternTable() {
		ksort($this->patterntable);
		return $this->patterntable;
	}

	public function printPatternTable() {
		$ptable = $this->getPatternTable();

		echo "<table class='lexertable stripes'>";
		echo "<tr><th>Name</th><th>Type</th><th>Flags</th><th>Entrypattern</th><th>Exitpattern</th><th>Restore</th><th>ConnectTo</th><th>Level</th><th>Allowed Modes</th><th>cached Regexp</th></tr>";

		foreach ($ptable as $p) {

			$modes = "";
			if (count($p['MODES']) > 0) {
				$modes = "";
				foreach ($p['MODES'] as $lvl => $mode) {
					$modes .= "[$lvl]&nbsp;$mode<br />";
				}
			}

			echo "<tr>".
					"<td>".$p['NAME']."</td>".
					"<td>".$p['TYPE']."</td>".
					"<td>".$p['FLAGS']."</td>".
					"<td><pre>".htmlentities($p['ENTRY'])."</pre></td>".
					"<td><pre>".htmlentities($p['EXIT'])."</pre></td>".
					"<td><pre>".$this->showEntities($p['RESTORE'])."</pre></td>".
					"<td>".$p['CONNECTTO']."</td>".
					"<td>".$p['LEVEL']."</td>".
					"<td>".$modes."</td>".
					"<td><pre>".htmlentities($p['CACHED_REGEXP'])."</pre></td>".
					"</tr>";
		}
		echo "</table>";
	}

	protected function resetPatternOrder() {
		$this->ids = array();
		$this->patternorder = 2;
	}

	/**
	 * Gibt den regul?ren Ausdruck f?r den aktuellen Status zur?ck.
	 * Erlaubte line- und section_start pattern inkl. des section_end patterns des
	 * aktuellen Status' wird zur?ckgeliefert.
	 * Der Patternstring wird in "CACHED_REGEXP" gespeichert und nur berechnet, wenn
	 * er fehlt oder "RENEW_CHACHED_REGEXP" auf true gesetzt wurde (neuer MODE hinzugef?gt
	 * oder gel?scht wird).
	 * PERFORMANCE: nicht verwendete Tokens werden gar nicht berechnet!
	 * @TODO: Manche Tokens m?ssen trotzdem gesucht werden f?r ERROR_REPORTING... Bsp. Math-Syntax 1++2 => ERROR (siehe antlr)
	 *
	 * @param   string          $statusname    Name des aktuellen Status (Bsp. quoted string), default = #DOCUMENT
	 * @param   boolean         $keysensitive  'i'-Parameter f?r die regex, default = false
	 * @return  string/boolean  regexp-string oder false bei einem Fehler
	 */
	public function getPatternString($statusname = "#DOCUMENT", $keysensitive = false) {

		if (! is_string($statusname)) {
			$this->addLog("FATAL", "'\$statusname has to be string. ".strtoupper(gettype($statusname))." given!");
			return false;
		}

		if (! is_bool($keysensitive)) {
			$this->addLog("FATAL", "'\$keysensitive has to be boolean.");
			return false;
		}


		// Falls der Patternstring bereits gecached wurde und keine neuen Tokens hinzugef?gt oder gel?scht wurden,
		// gespeicherten Patternstring zur?ckgeben.
		if (isset($this->patterntable[$statusname]["CACHED_REGEXP_RENEW"]) and $this->patterntable[$statusname]["CACHED_REGEXP_RENEW"] === false) {
			$this->ids = $this->patterntable[$statusname]["MODES"];
			return $this->patterntable[$statusname]["CACHED_REGEXP"];
		}

		$regex_start = '/(.*?)(?:';
		$regex_end   = ')()/';
		$regex_param = 'msS';

		if ($keysensitive === false) {
			$regex_param .= 'i';
		}

		$pattern = "";
		$newmodeorder = array();
		$this->resetPatternOrder();

		if (count($this->patterntable[$statusname]["MODES"]) > 0) {
			foreach($this->patterntable[$statusname]["MODES"] as $mode) {
				$entrypattern = $this->patterntable[$mode]["ENTRY"];
				$newmodeorder[$this->patternorder] = $mode;
				$this->updatePatternOrder($entrypattern, $mode);
				$pattern .= $entrypattern.'|';
			}
		}

		$this->patterntable[$statusname]["MODES"] = $newmodeorder;

		$exitpattern = NULL;
		if (isset($this->patterntable[$statusname]["EXIT"])) {
			$exitpattern = $this->patterntable[$statusname]["EXIT"];
		}
		if ($exitpattern !== NULL) {
			$this->patterntable[$statusname]["MODES"][$this->patternorder] =  "__exit__$statusname";
			$this->updatePatternOrder($exitpattern, $statusname, true);
			$pattern .= $exitpattern;
		} else {
			$pattern = rtrim($pattern, '|');
		}

		$pattern = $regex_start.$pattern.$regex_end.$regex_param;

		$this->patterntable[$statusname]["CACHED_REGEXP"] = $pattern;
		$this->patterntable[$statusname]["CACHED_REGEXP_RENEW"] = false;

		// Falls keine Modes f?r den aktuellen status angegeben wurden, kann der Lexer nicht weitersuchen.
		// Wahrscheinlich wurde setAllowedModes noch nicht aufgerufen.
		if (! is_array($this->patterntable[$statusname]["MODES"]) or count($this->patterntable[$statusname]["MODES"]) == 0) {
			$this->addLog("FATAL", "No Modes for '$statusname' given! See setAllowedModes for further information!");
			return false;
		}


		return $pattern;

	}

	public function getNodeCount() {
		return count($this->AST);
	}

	public function getPatternInfo($name, $key) {

		if ($this->stripExitTagName($name) === NULL)
			return false;

		if (!array_key_exists($name, $this->patterntable) or !array_key_exists($key, $this->patterntable[$name])) {
			$this->addLog("WARNING", "Info for $name:$key doesn't exist!");
			return false;
		}

		return $this->patterntable[$name][$key];
	}

	protected function addAbstractNode($name) {

		$nodename = $this->getPatternInfo($name, "CONNECTTO");
		$pid = $this->getParentID();
		if ($nodename == false or $this->AST[$pid]["NAME"] == $nodename)
			return false;

		#pw_debug($this->AST[$parentid], "addABSTRACTNODE: $name; parentid = $parentid");

		$tag = array( "NAME"   => $nodename,
		"ID"     => count($this->AST),
		"PARENT" => $pid
		);

		array_push($this->AST, $tag);


		$id = $this->addNewChild();
		$this->updateParentStack($id);

		if (/*$this->debug and*/ $tag) {
			$this->dbginf[$this->cycle] = array_merge(
					array(
							"OLDLINENR"    => $this->oldlinenr,
							"LINENR"       => $this->linenr,
							"ID"           => $id,
							"PARENTID"     => $pid,
							#"EXIT"         => $exit,
							"CYCLE"        => $this->cycle,
							"TXTPOS"       => strlen($this->beforematch),
							"PARENTSTACK"  => $this->parentstack,
					), $tag
			);
			$this->cycle++;
		}

		return true;
	}

	protected function removeParentFromStack() {
		array_pop($this->parentstack);
		$parentid = $this->getParentID();

		// RESET, wenn ein Fehler aufgetreten ist.
		// Alle Waisenkinder werden an den #DOCUMENT-Knoten (ID = 0) geh?ngt!
		if ($parentid === false) {
			$this->parentstack = array(0);
			return 0;
		}

		return $parentid;

	}

	// TODO: erweitern... wann wird eine parentid hinzugef?gt, wann entfernt, wann werden mehrere entfernt?
	protected function updateParentStack($id = NULL) {

		if (!is_integer($id) and $id !== NULL) {
			$this->addLog("WARNING", "\$id has to be integer or NULL! '$id' given.");
			return false;
		}

		// Keine ID gegeben: update + gib die entsprechende parent-ID zur?ck
		if ($id === NULL) {
			#pw_debug($this->parentstack, "updateParentStack: id = $id; parentid = $parentid");
			$parentid = $this->removeParentFromStack();
			return $parentid;
		}

		// ID gegeben: f?ge eine neue parent-ID hinzu...
		if (in_array($id, $this->parentstack)) {
			$this->addLog("WARNING", "$id already exists in parentstack!");
			return false;
		}
		$this->parentstack[] = $id;

		#pw_debug($this->parentstack, "updateParentStack: id = $id; parentid = $id");
		return true;

	}

	protected function getParentID() {
		if (! is_array($this->parentstack))
			return false;

		$id = end($this->parentstack);

		if (! is_integer($id) or $id < 0)
			return false;

		return $id;
	}

	protected function addNodeOnOpen($tag) {

		#pw_debug($tag, "ONOPEN");

		// R?ckgabe: FALSE => OpenTag gefunden
		if ($this->stripExitTagName($tag["NAME"]) !== false) {
			return false;
		}

		// Text-Knoten erzeugen...
		$this->addTextNode($tag, false, false, true);

		// Erzeuge einen abstrakten Knoten, falls der Parameter CONNECTTO gesetzt wurde!
		$anodename = $this->getPatternInfo($tag["NAME"], "CONNECTTO");
		if ($anodename !== false) {
			// Falls andere abstrakte Knoten noch offen sind schlie?en...
			$pid = $this->getParentID();
			if ($this->getPatternInfo($this->AST[$pid]['NAME'], "TYPE") == "TYPE_ABSTRACT" and $this->AST[$pid]['NAME'] != $anodename) {
				$this->updateParentStack();
			}
			$abstractnode = $this->addAbstractNode($tag["NAME"]);
		} else {
			// Schlie?e abstrakte Eltern-Knoten, falls ein neuer Modus ge?ffnet wird!
			$pid = $this->getParentID();
			if ($this->getPatternInfo($this->AST[$pid]['NAME'], "TYPE") == "TYPE_ABSTRACT") {
				$this->updateParentStack();
				#$this->addTextNode($tag, false, false, true);
			}
		}

		// Text-Knoten erzeugen...
		#$this->addTextNode($tag, false, false, true);
		#if (trim($tag['TEXT']) == "Diese Seite") {
		#  pw_debug(trim($tag['TEXT']));
		#  pw_debug($tag);
		#  $pid = $this->getParentID();
		#  pw_debug($pid);
		#  $anodename = $this->getPatternInfo($tag["NAME"], "CONNECTTO");
		#  pw_debug($anodename);
		#}


		#pw_debug($tag, "ONOPEN - before add: id = $id; parentid = $parentid ");
		#if ($tag['TOKEN'] == false) {
		#  $this->addLog("FATAL", print_r($tag, true));
		#  return false;
		#}

		array_push($this->AST, array( "NAME"    => $tag["NAME"],
		"ID"      => count($this->AST),
		"CONFIG"  => $tag["CONFIG"],
		"PARENT"  => $this->getParentID()
		));
		#$x = count($this->AST);
		#pw_debug($x);
		#$this->AST[$x] =  array( "NAME"    => $tag["NAME"],
		#                              "ID"      => $x,
		#                              "CONFIG"  => $tag["CONFIG"],
		#                              "PARENT"  => $this->getParentID()
		#                            );
		$id = $this->addNewChild();

		// WORD-pattern werden gleich nach dem ?ffnen wieder geschlossen.
		// Keine parentID wird in den Stapel aufgenommen.
		if ($this->getPatternInfo($tag["NAME"], "TYPE") != "TYPE_WORD") {
			$this->updateParentStack($id);
		} else {
			// Backstep... Manche Matches m?ssen nach der Erkennung eines Exit-Tags
			// f?r den n?chsten Entry-Tag bewahrt bleiben.
			$restore = $this->getPatternInfo($tag["NAME"], "RESTORE");
			if ($restore !== false) {
				$this->textposition -= strlen($restore);
				$this->temptxt = $restore.$this->temptxt;
			}

		}

		#pw_debug($this->parentstack, "PARENT");
		#pw_debug($tag, "ONOPEN - added: id = $id; parentid = $parentid ");
		#pw_debug($this->AST, "DOM");
		#echo "<pre>".htmlentities($this->temptxt)."</pre><hr>";

	}

	protected function addNewChild() {
		$childid = count($this->AST)-1; // Aktuelle ID
		$parentid = $this->AST[$childid]["PARENT"];
		$this->AST[$parentid]["CHILDREN"][] = $childid;
		return $childid;
	}

	/**
	 * F?gt ein Textknoten hinzu und h?ngt ein neues "child" beim "PARENT" an.
	 * TODO: sollte ?ber eine public function ?nderbar sein!!!!
	 *
	 * @param string|array    $txtortag         Einfachen String oder Tag-Array (enth?lt Value und Length)
	 * @param boolen          $addemptystring   TRUE, wenn leere Textknoten erstellt werden sollen.
	 * @param boolen          $addnewlines      TRUE, wenn reine Newlines '\n' (ohne Leerzeichen) aufgenommen werden sollen.
	 * @param boolen          $addspacelines    TRUE, wenn Leerzeilen = Leerzeichen+Newline aufgenommen werden sollen.
	 *
	 * @return boolean  TRUE on success
	 */
	protected function addTextNode($txtortag, $addemptystring = false, $addnewlines = false, $addspacelines = false) {

		$parentid = $this->getParentID();

		if ($parentid === false) {
			$this->addLog("FATAL", "ParentID not found!");
			return false;
		}

		if (is_array($txtortag)) {
			$text = $txtortag["TEXT"];
			$length = $txtortag["TEXTLENGTH"];
		} elseif (is_string($txtortag)) {
			$text = $txtortag;
			$length = strlen($txtortag);
		} else {
			$this->addLog("FATAL", "First parameter must be either a string or a tagarray");
			return false;
		}

		// Erzeuge einen Textknoten aus den neuen und alten Textfragmenten,
		// damit nicht zu viele Textknoten in der selben Hierarchie erzeugt
		// werden. L?sche danach den Textbuffer.
		$text = $this->remtext.$text;
		$this->rememberText(false);

		if ($length == 0 and $addemptystring == false)
			return false;

		#if ($txtortag['NAME'] == "tableheader")    pw_debug($txtortag);

		$trimtext = trim($text, ' ');

		// Nur Newline entdeckt...
		if ($trimtext == "\n" and $addnewlines == false) {
			return false;
		}

		// Nur Leerzeichen entdeckt...
		if ($trimtext == "" and $addspacelines == false) {
			return false;
		}

		// Textknoten sind in abstrakten Knoten nicht erlaubt...
		// Abstrakte Knoten werden zuerst geschlossen.
		if ($this->getPatternInfo($this->AST[$parentid]['NAME'], "TYPE") == "TYPE_ABSTRACT") {
			$this->updateParentStack();
			$parentid = $this->AST[$parentid]['PARENT'];
		}

		array_push($this->AST, array( "NAME"    => "#TEXT",
		"ID"      => count($this->AST),
		"VALUE"   => $text,
		"LENGTH"  => $length,
		"PARENT"  => $parentid,
		));
		$this->addNewChild();

		return true;
	}

	protected function rememberText($text) {
		if ($text === false) {
			$this->remtext = "";
			return true;
		}

		if (! is_string($text))
			return false;

		$this->remtext .= $text;
		return true;
	}

	protected function stripExitTagName(&$name) {

		if (! is_string($name)) {
			$this->addLog("WARNING", "Wrong datatype. \$name must be string!");
			return NULL;
		}

		if (substr($name, 0, 8) == "__exit__") {
			$name = substr($name, 8);
			return true;
		}

		return false;

	}

	protected function addNodeOnClose($tag) {

		// Keinen Exit-Tag gefunden... NULL = Fehler, false = Entry-Tag
		if ($this->stripExitTagName($tag["NAME"]) !== true) {
			return false;
		}

		// Backstep... Manche Matches m?ssen nach der Erkennung eines Exit-Tags
		// f?r den n?chsten Entry-Tag bewahrt bleiben.
		$restore = $this->getPatternInfo($tag["NAME"], "RESTORE");
		if ($restore !== false) {
			$this->textposition -= strlen($restore);
			$this->temptxt = $restore.$this->temptxt;
		}

		// Restlichen Text als Textknoten in das DOM h?ngen
		$this->addTextNode($tag, false, false, true);

		// Schlie?e Abstrakte Eltern-Knoten, falls ein neuer Modus gestartet wird!
		$cto = $this->getPatternInfo($tag['NAME'], 'CONNECTTO');
		$pid = $this->getParentID();
		if ($cto == false or $cto != $this->AST[$pid]['NAME']) {
			if ($this->getPatternInfo($this->AST[$pid]['NAME'], "TYPE") == "TYPE_ABSTRACT") {
				$this->updateParentStack();
			}
		}

		#if ($tag['NAME'] == "preformat") {
		#  pw_debug($tag);
		#  #pw_debug($pid);
		#  pw_debug($this->getPatternInfo($this->AST[$pid]['NAME'], "TYPE"), $pid);
		#  pw_debug($this->parentstack);
		#}

		// Restlichen Text als Textknoten in das DOM h?ngen
		#$this->addTextNode($tag, false, false, true);

		#if($this->AST[$pid]['NAME'] == 'comment') {
		#  pw_debug($pid);
		#  $this->AST[$pid]['IGNORE'] = true;
		#  array_pop($this->AST);
		#  #array_pop($this->AST);
		#}

		// Parent geschlossen: Parent-ID entfernen!
		$this->updateParentStack();

		#if ($this->AST[$pid]['IGNORE']) {
		#  pw_debug($tag, "CLOSING: ".$tag["NAME"]." ID=$pid");
		#  pw_debug($this->parentstack);
		#  array_pop($this->AST);
		#  array_pop($this->AST);
		#}


		return true;

		/** @TODO
		 if ($tag["NAME"] != $this->AST[$parentid]["NAME"]) {
		 $this->addLog("FATAL", "Tag Encapsulation Error! Opened = '".$this->AST[$parentid]["NAME"]."'; closing '$tag[NAME]'");
		 }
		 */
	}

	protected function updateTextPosition() {

		$this->aftermatch = $this->temptxt;

		// Length Match-String and Rest
		$lenmarest = strlen($this->aftermatch.$this->currentline);
		$this->beforematch = substr($this->fulltxt, 0, $this->fulltxtlength - $lenmarest);

		$this->oldlinenr = $this->linenr;
		preg_match_all("#\n#", $this->beforematch.$this->currentline, $lines);
		$lines = $lines[0];
		$this->linenr = count($lines);
		if (substr($this->beforematch.$this->currentline, -1) == "\n") {
			$this->linenr--;
		}

	}

	public function getDebugInfo() {
		return $this->dbginf;
	}

	public function printDebugInfo($linesbefore = -1, $linesafter = -1, $print = true) {
		$di = $this->getDebugInfo();

		if (is_array($di)) {
			$o = "<table class='lexertable stripes' style='border: 1px solid black; color: black; background-color: lightgray'>".
					"<tr style='background-color: lightgray; border-bottom: 3px solid gray'><td colspan=7 style='text-align:center'>";
			$o .= "<b>Legende:</b><br>".
					"<span style='background-color: lightblue'>Abschnitt vor und nach dem gefundenen Token.</span><br>".
					"<span style='background-color: cyan'>gefundener Text</span><br>".
					"<span style='background-color: yellow'>gefundenes Token (ohne lookarounds)</span>";
			$o .= "</td></tr>".
					"<tr>".
					"<th>Zyklus</th>".
					"<th>Textzeiger</th>".
					"<th>ID/PID</th>".
					"<th>Text</th>".
					"<th>Gefunden</th>".
					"<th>ParentStack</th>".
					"<th>Config</th>".
					"</tr>";

			foreach ($di as $d) {

				if (isset($d['CONFIG'])) {
					$config = "";
					foreach ($d['CONFIG'] as $k => $v) {
						$config .= "$k:[$v]<br />";
					}
				}

				$parentstack = "";
				foreach ($d['PARENTSTACK'] as $k => $v) {
					$parentstack .= "$v<br />";
				}

				if ($d['EXIT']) {
					$idtxt = "Closing:<br />".$d['PARENTID'];
				} else {
					$idtxt = $d['ID']."<br />"."[PID=".$d['PARENTID']."]";
				}



				$o .=  "<tr style='border: 1px solid black;' >".
						"<td>".sprintf("%03d",$d['CYCLE'])."</td>".
						"<td>Zeile: $d[LINENR]<br>Textposition: $d[TXTPOS]<br>Textl&auml;nge: $d[TEXTLENGTH]</td>".
						"<td>$idtxt</td>".
						"<td><pre>".
						"<span style='background-color: lightblue'>".$this->showEntities( $this->getBeforeMatch($d, $linesbefore) )."</span>".
						"<span style='background-color: cyan'>".$this->showEntities( $d["TEXT"] )."</span>".
						"<span style='background-color: yellow'>".$this->showEntities( $d["TOKEN"] )."</span>".
						"<span style='background-color: lightblue'>".$this->showEntities( $this->getAfterMatch($d, $linesafter) )."</span>".
						"</pre></td>".
						"<td>".(isset($d["ABSTRACTNODE"]) ? $d["ABSTRACTNODE"] : "").$d["NAME"]."<br>".$this->getPatternInfo( $d["NAME"], "TYPE" )."</td>".
						"<td><pre>".$parentstack."</pre></td>".
						"<td><pre>".$config."</pre></td>".
						"</tr>";

			}
			$o .= "</table>";
		}

		if ($print) {
			echo $o;
		}
		return $o;

	}

	protected function updateDomTree($tag, $txt) {

		#pw_debug($tag, "TAG");
		$pid = $this->getParentID();
		$this->updateTextPosition();
		$this->addNodeOnOpen($tag);
		$this->addNodeOnClose($tag);

		$current = end ($this->AST);
		$thisid = $current['ID'];

		#pw_debug($current);
		#pw_debug($tag);

		$n = $tag["NAME"];
		$exit = $this->stripExitTagName($n);
		if ($exit) {
			$thisid = NULL;
		}

		// @TODO: Weniger Speicherbedarf! Nur Textposition merken. Rest sollte in einer Funktion berechnet werden.
		if (/*$this->debug and */$tag) {
			$this->dbginf[$this->cycle] = array_merge(
					array(
							"OLDLINENR"    => $this->oldlinenr,
							"LINENR"       => $this->linenr,
							"ID"           => $thisid,
							"PARENTID"     => $pid,
							"EXIT"         => $exit,
							"CYCLE"        => $this->cycle,
							"TXTPOS"       => strlen($this->beforematch),
							"PARENTSTACK"  => $this->parentstack,
					), $tag
			);
		}

	}

	public function checkNode($node) {
		if ($this->parsed == false) {
			$this->addLog("WARNING", "Text not parsed yet! Can't give you a node! Use function parse() and check the logfile for errors.");
			return false;
		}

		// null = #DOCUMENT-Knoten
		if ($node === null) {
			return true;
		}

		if (! is_array($node)) {
			$this->addLog("WARNING", "First argument must be an array or NULL! '".gettype($node)."' given.");
			return false;
		}

		return true;
	}

	public function firstChild($node = null) {

		if ($this->checkNode($node) === false) {
			return false;
		}

		// #DOCUMENT-Knoten (root)
		if ($node === null) {
			return $this->AST[0];
		}

		// Knoten mit Kindern: first child zur?ckgeben.
		if (isset($node['CHILDREN'][0])) {
			return $this->AST[$node['CHILDREN'][0]];
		}

		// Knoten ohne Kinder: null
		return null;

	}

	public function lastChild($node = null) {

		if ($this->checkNode($node) === false) {
			return false;
		}

		// #DOCUMENT-Knoten (root)
		if ($node === null) {
			return $this->AST[0];
		}


		// Knoten mit Kindern: first child zur?ckgeben.
		if (is_array($node['CHILDREN'])) {
			$lastchild = end($node['CHILDREN']);
			return $this->AST[$lastchild];
		}

		// Knoten ohne Kinder: null
		return null;

	}

	public function childNodes($node = null) {

		if ($this->checkNode($node) === false) {
			return false;
		}

		// #DOCUMENT-Knoten (root)
		if ($node === null) {
			return $this->AST[0];
		}

		// Knoten mit Kindern: first child zur?ckgeben.
		if (isset($node['CHILDREN'])) {
			foreach ($node['CHILDREN'] as $childid) {
				$children[$childid] = $this->AST[$childid];
			}
			return $children;
		}

		// Knoten ohne Kinder: null
		return null;

	}

	public function getNode($id = 0) {
		if ($this->parsed == false) {
			$this->addLog("WARNING", "Text not parsed yet! Can't give you a node! Use function parse() and check the logfile for errors.");
			return false;
		}

		if (! array_key_exists($id, $this->AST)) {
			$this->addLog("WARNING", "ID '$id' doesn't exist in AST.");
			return false;
		}

		return $this->AST[$id];
	}

	public function parentNode($node = null) {
		if ($this->checkNode($node) === false) {
			return false;
		}

		// #DOCUMENT-Knoten haben kein "parent"
		if ($node === null) {
			return null;
		}

		if (isset($node['PARENT']) and isset($this->AST[$node['PARENT']])) {
			return $this->AST[$node['PARENT']];
		}

		return null;
	}

	public function nextSibling($node = null) {
		if ($this->checkNode($node) === false) {
			return false;
		}

		// #DOCUMENT-Knoten (root)
		if ($node === null) {
			return $this->AST[0];
		}

		$pnode = $this->parentNode($node);

		// #DOCUMENT-Knoten hat kein "parent", kein Fehler im AST...
		if ($pnode === NULL)
			return;

		// Fehler im AST, Waisenkind entdeckt...
		if ($pnode === false) {
			$this->addLog("WARNING", "Orphan found: Corrupted AST @ ID '".$node['ID']."': parent not found!");
			return false;
		}

		// Parent gefunden, also muss der Kindknoten auch gefunden werden! Fehlercheck ?berfl?ssig!
		$cur = array_search($node['ID'], $pnode['CHILDREN']);

		if (isset($pnode['CHILDREN'][$cur+1]) and isset($this->AST[$pnode['CHILDREN'][$cur+1]])) {
			return $this->AST[$pnode['CHILDREN'][$cur+1]];
		}

		return false;
	}

	public function previousSibling($node = null) {
		if ($this->checkNode($node) === false) {
			return false;
		}

		// #DOCUMENT-Knoten (root)
		if ($node === null) {
			return $this->AST[0];
		}

		$pnode = $this->parentNode($node);
		if (! $pnode) {
			$this->addLog("WARNING", "ID '".$node['ID']."': parent not found!");
			return false;
		}

		// Parent gefunden, also muss der Kindknoten auch gefunden werden! Fehlercheck ?berfl?ssig!
		$cur = array_search($node['ID'], $pnode['CHILDREN']);

		if (isset($pnode['CHILDREN'][$cur-1]) and isset($this->AST[$pnode['CHILDREN'][$cur-1]])) {
			return $this->AST[$pnode['CHILDREN'][$cur-1]];
		}

		#return false;
	}

	public function hasChildNodes($node = null) {
		if ($this->checkNode($node) === false) {
			return false;
		}

		// #DOCUMENT-Knoten (root)
		if ($node === null) {
			$node = $this->AST[0];
		}

		if (isset($node['CHILDREN']) and is_array($node['CHILDREN'])) {
			return true;
		}

		return false;

	}

	public function hasAncestor($node, $ancestor = "#DOCUMENT") {
		if ($this->checkNode($node) === false) {
			return false;
		}

		for ($node; $node != null; $node = $this->parentNode($node)) {
			if ($node['NAME'] == $ancestor) {
				return true;
			}
		}
		return false;
	}

	public function childPosition($node) {
		if ($this->checkNode($node) === false) {
			return false;
		}

		$pn = $this->parentNode($node);
		foreach($pn['CHILDREN'] as $chid => $ch) {
			if ($node['ID'] == $ch)
				return $chid;
		}
	}

	public function nextSiblingSameChild($node, $child) {
		if ($this->checkNode($node) === false) {
			return false;
		}

		if (!$pn = $this->parentNode($node))
			return false;
		if (!$ns = $this->nextSibling($pn))
			return false;
		if (!isset($ns['CHILDREN'][$child]))
			return false;

		return $this->getNode($ns['CHILDREN'][$child]);
	}

	public function callFunction($node, $type) {
		$prefix = 'e';
		if ($type == ONENTRY) {
			$prefix = 's';
		}

		if ($node['NAME'] == "#TEXT") {
			return $node['VALUE'];
		}

		if (function_exists($prefix.$node['NAME'])) {
			return call_user_func($prefix.$node['NAME'], $node, $this);
		}
	}

	public function getArray($node, $arr = array()) {
		if (/*@TODO: Better Checks...Standardise!!!! !isset($node) || */$this->checkNode($node) === false) {
			$this->addLog("FATAL", "DATATYPE for node isn't of type ARRAY.");
		}

		//@TODO: BUG! no globals here: unbound the class from the rest of the world!!!
		global $norecursion;
		if (!is_array($norecursion)) {
			$this->addLog("FATAL", "DATATYPE for NoRecursion has to be Array.");
		}

		if (!$this->hasChildNodes($node) and isset($node['VALUE'])) {
			return array($node['VALUE']);
		}

		for ($node = $this->firstChild($node); $node != null; $node = $this->nextSibling($node)) {

			if ($node['NAME'] == "#TEXT") {
				#$arr[] = utf8_encode(htmlentities(utf8_decode($node['VALUE'])));
				$arr[] = pw_s2e($node['VALUE']);
			} else {

				$ret = $this->callFunction($node, ONENTRY);
				if ($ret !== null) {
					$arr[] = $ret;
				}

				if ($this->hasChildNodes($node) and !in_array($node['NAME'], $norecursion)) {
					$arr = $this->getArray($node, $arr);
				}

				$ret = $this->callFunction($node, ONEXIT);
				if ($ret !== null) {
					$arr[] = $ret;
				}

			}
		}

		return $arr;
	}

	public function getText($node, $sep = "") {
		$array = $this->getArray($node);
		if (!$array) {
			return false;
		}
		$text = implode($sep, $array);
		$text = rtrim($text, $sep);
		return $text;
	}

	public function getText2($startnode) {

		$text = "";
		for ($node = $this->nextSibling($startnode); $node != null; $node = $this->nextSibling($node)) {
			$ret = $this->getText($node);
			if (!$ret) {
				$ret = $this->callFunction($node, ONENTRY);
				$ret .= $this->callFunction($node, ONEXIT);

			}
			#$ret = utf8_encode(htmlentities(utf8_decode($ret)));
			$ret = pw_s2e($ret);
			$text .= $ret;
		}

		return $text;
	}


	public function getBeforeMatch($dbginf, $linesbefore = -1) {
		if ($linesbefore == 0) {
			return "";
		}

		$txt = substr($this->fulltxt, 0, $dbginf['TXTPOS']);
		if ($linesbefore < 0) {
			return $txt;
		}

		$lines = explode("\n", $txt);
		$pos = -1*$linesbefore;
		$lines = array_slice($lines, $pos, $linesbefore);
		$txt = implode($lines,'\n');
		return $txt;
	}

	public function getAfterMatch($dbginf, $linesafter = -1) {
		if ($linesafter == 0) {
			return "";
		}

		$txt = substr($this->fulltxt, $dbginf['TXTPOS']+strlen($dbginf['RESTORE']));
		if ($linesafter < 0) {
			return $txt;
		}

		$lines = explode("\n", $txt);
		$lines = array_slice($lines, 0, $linesafter);
		$txt = implode($lines,'\n').'\n';
		return $txt;

	}

	public function showEntities($text) {
		#return str_replace("\n", "&crarr;<br />", htmlentities(utf8_decode($text)));
		#return str_replace("\n", '\n<br />', utf8_encode(htmlentities(utf8_decode($text))));
		return str_replace("\n", '\n<br />', pw_s2e($text));
	}

	protected function getASTrec ($node, $z, $txt = "") {

		for ($node = $this->firstChild($node); $node != null; $node = $this->nextSibling($node)) {
			if ($node['NAME'] == "#TEXT") {
				$txt .= $z.$node['NAME']." (".$node['ID'].") {'".str_replace("\n", '\n', pw_s2e($node['VALUE']))."'}<br>";
			} else {
				$txt .= $z.$node['NAME']."[".@implode("|", str_replace("\n", '\n', $node['CONFIG']))."] (".$node['ID']."){";
				if ($this->hasChildNodes($node)) {
					$txt .= "<br>";
					$txt = $this->getASTrec($node, $z."  ", $txt);
					$txt .= $z;
				}
				$txt .= "}<br>";
			}
		}
		return $txt;
	}


	public function printAST() {
		if (! $this->parsed) {
			$this->addLog("WARNING", "No AST avaiable. Use parse().");
			echo "No AST avaiable. See Logfile for details!";
		}

		echo "<pre>";
		echo $this->getASTrec(null, "");
		echo "</pre>";
	}

	public function getAST() {
		return $this->getASTrec(null, "");
	}


	/**
	 * LOGBOOK-SYSTEM
	 **/
	public function addLog($type, $text, $data = null) {


		// get Debuginfo as array(1)!
		$dbginfo = pw_debug_get_info("", 1);

		$this->logbook[] = array ( 'TIME' => date("Y/m/d h:i:s", time()),
				'TYPE' => $type,
				'FILE' => $dbginfo['FILE'],
				'LINE' => $dbginfo["LINE"],
				'FUNC' => $dbginfo["FUNC"],
				'TEXT' => $text,
				'DATA' => $data
		);

		if ($type == "FATAL") {
			#out2(array_pop($this->logbook));
			#$this->logbook = ;
			throw new Exception($this->getLogText(false));
		}

		$this->error = $type;

	}

	public function printLog($info = true) {
		echo "<pre>";
		echo $this->getLogText($info);
		echo "</pre>";
	}

	public function getLogText($info = true) {
		$logbook = $this->getLog($info);
		$out = "";
		foreach ($logbook as $line) {
			$out .= sprintf( "%19s | %-7s | %s: %s\n", $line['TIME'], $line['TYPE'], $line["FILE"].":".$line["LINE"]." [".$line["FUNC"]."]", $line['TEXT']);
		}
		return $out;
	}

	public function getLog($info = true) {

		$out = array();
		$logbook = $this->logbook;
		foreach ($logbook as $line) {
			if ($line['TYPE'] != 'INFO' or $info) {
				$out[] = $line;
			}
		}

		return $out;
	}

}

/*


echo "<pre>".htmlentities($lexer->getPatternString())."</pre>";

$lexer->parse();

$lexer->printText(true);

$lexer->printDOM();
$di = $lexer->getDebugInfo();
#pw_debug( $di );

echo  "<table border=1 style='width: 100%'><tr>".
"<th width=80>Line #</th>".
"<th>Text</th>".
"<th>Tag-Name</th>".
"<th>ParentStack</th>".
"<th>Config</th>".
"</tr>";

foreach ($di as $d) {
echo  "<tr valign=top style='background-color: $color'><td align=center>PID=$d[PARENTID]<hr>ID=$d[ID]<hr>Zyklus:<br>$d[CYCLE]<hr>Zeile:<br>$d[LINENR]<br>Textpos.:$d[TXTPOS]</td>".
"<td><pre>".
"<span style='background-color: lightblue'>".$lexer->showEntities( $lexer->getBeforeMatch($d) )."</span>".
"<span style='background-color: cyan'>".$lexer->showEntities( $d["TEXT"] )."</span>".
"<span style='background-color: yellow'>".$lexer->showEntities( $d["TOKEN"] )."</span>".
"<span style='background-color: lightblue'>".$lexer->showEntities( $lexer->getAfterMatch($d) )."</span>".
"</pre></td>".
"<td>".$d["ABSTRACTNODE"].$d["NAME"]."[".$lexer->getPatternInfo( $d["NAME"], "TYPE" )."]</td>".
"<td><pre>".print_r($d['PARENTSTACK'], true)."</pre></td>".
"<td><pre>".print_r($d["CONFIG"], true)."</pre></td>".
"</tr>";

}
echo "</table>";

/*


$node = $lexer->firstChild();
pw_debug($node, "firstChild: null");

$node = $lexer->parentNode($node);
pw_debug($node, "parentNode: #DOCUMENT");

$node = $lexer->getNode(1);
pw_debug($node, "getNode");

$node = $lexer->parentNode($node);
pw_debug($node, "parentNode");

pw_debug($lexer->childNodes($node), "childNodes");

$l = $lexer->lastChild($node);
pw_debug($l, "lastChild");

$node = $lexer->firstChild($node);
pw_debug($node, "firstChild");

$node = $lexer->nextSibling($node);
pw_debug($node, "nextSibling");

$node = $lexer->getNode(2);
pw_debug($node, "getNode");

pw_debug( $lexer->hasChildNodes($node) , "hasChildNodes");

$lexer->printLog();

$node = $lexer->getNode(0);

function schleife ($lexer, $node, $z) {

for ($node = $lexer->firstChild($node); $node != null; $node = $lexer->nextSibling($node)) {
if ($node['NAME'] == "#TEXT") {
echo $z.$node['NAME']." (".$node['ID'].") {'".$node['VALUE']."'}<br>";
} else {
echo $z.$node['NAME']."[".@implode("|", $node['CONFIG'])."] (".$node['ID']."){";
if ($lexer->hasChildNodes($node)) {
echo "<br>";
schleife($lexer, $node, $z."  ");
echo $z;
}
echo "}<br>";

}

}
return $str;
}

echo "<pre>";
schleife($lexer, null, "");
echo "</pre>";

$lexer->printPatternTable();

$_SESSION["timer"][] = $lexer->getExecutionTime();

pw_debug($_SESSION["timer"]);

/*
TODO:
abstractnodes in debuginfos aufnehmen...
abstractnodes in patterntable aufnehmen...
jeder knoten muss connectto haben (default=null)...
bessere debug infos trace damit nicht nur "getPatternInfo" steht...
AST-Schleife und debuginfo-tabelle in separate datei...


*/
?>