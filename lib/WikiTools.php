<?php
class WikiTools {
	
	static function getCurrentID() {
		$id = ":".WikiConfig::WIKINSDEFAULTPAGE;
		if(isset($_GET['id']) && $_GET['id'] != "") {
			$id = $_GET['id'];
		}
		return new WikiID($id);
	}
	
	static function getCurrentMode() {
		return isset($_GET['mode']) ? $_GET['mode'] : null;
	}
	

	static function getHtmlTitle($sep = " &laquo; ") {
		$id = WikiTools::getCurrentID();
		$title = "";
		foreach ($id->getFullNSAsArray() as $namespace) {
			$title = pw_s2e(utf8_ucfirst($namespace)).$sep.$title;
		}
	
		if(!$id->isNS() && $id->getPage() != WikiConfig::WIKINSDEFAULTPAGE) {
			$title = pw_s2e(utf8_ucfirst($id->getPage())).$sep.$title;
		}
	
		$title .= pw_s2e(utf8_ucfirst(WikiTools::getSessionInfo('wikititle')));
		return $title;
	}
	
	static function isSessionInfoLoaded() {
		return isset($_SESSION['pw_wiki']) && is_array($_SESSION['pw_wiki']);
	}
	
	static function getSessionInfo($what = "", $subcat = "") {
	
		if (!self::isSessionInfoLoaded()) {
			throw new Exception("Session pw_wiki is not loaded!");
		}
	
		if ($what == "")
			return $_SESSION['pw_wiki'];
	
		if (!array_key_exists($what, $_SESSION['pw_wiki'])) {
			throw new Exception("Session pw_wiki has no category '$what'!");
		}
	
		if ($subcat == "")
			return $_SESSION['pw_wiki'][$what];
	
		if (!array_key_exists($subcat, $_SESSION['pw_wiki'][$what])) {
			throw new Exception("Session pw_wiki has no sub-category '$subcat' within '$what'!");
		}
	
		return $_SESSION['pw_wiki'][$what][$subcat];
	}
	
	static function setSessionInfo($key, $value) {
		if (!self::isSessionInfoLoaded()) {
			throw new Exception("Session pw_wiki is not loaded!");
		}
		$_SESSION['pw_wiki'][$key] = $value;
	}
	
	static function unsetSessionInfo($key = "") {
		if($key == "") {
			unset($_SESSION['pw_wiki']);
		} else {
			unset($_SESSION['pw_wiki'][$key]);
		}
	}
	
	static function loadConfigToSession($config = null) {
		
		/*
		 * Set default config if $config is null, overwrite existing information
		 */
		if ($config == null) {
			$_SESSION['pw_wiki'] = WikiConfig::getDefault();
			return;
		}
		
		/*
		 * If wiki-session info exists, merge with $config, otherwise create a  
		 * new wiki-session from $config (only if it is an array).
		 */
		if (is_array($config)) {
			if (isset($_SESSION['pw_wiki']) && is_array($_SESSION['pw_wiki'])) {
				$_SESSION['pw_wiki'] = array_merge($_SESSION['pw_wiki'], $config);
			} else {
				$_SESSION['pw_wiki'] = $config;
			}
		}
		
		throw new Exception("Can not load session config! '$config' given.");
	}
	
	static function getParsedFile(WikiID $id, $forcedCacheUpdate = false) {
		$filename = WikiConfig::WIKISTORAGE.$id->getPath().WikiConfig::WIKIFILEEXT;
		$headerID = new WikiID(WikiConfig::WIKITEMPLATESNS."header");
		$footerID = new WikiID(WikiConfig::WIKITEMPLATESNS."footer");
		$headerFilename = WikiConfig::WIKISTORAGE."/".$headerID->getPath().WikiConfig::WIKIFILEEXT;;
		$footerFilename = WikiConfig::WIKISTORAGE."/".$footerID->getPath().WikiConfig::WIKIFILEEXT;;
	
		if (!is_file($filename)) {
			throw new Exception("File '$filename' does not exist!");
		}
		if (!is_file($headerFilename)) {
			throw new Exception("File '$headerFilename' does not exist!");
		}
		if (!is_file($footerFilename)) {
			throw new Exception("File '$footerFilename' does not exist!");
		}
	
		/*
		 * This is only executed with configuration CACHE ENABLED!
		 * If the cached file is still up-to-date do nothing, except forced
		 * overwrite is enabled.
		 */
		if(WikiTools::getSessionInfo('useCache') == true) {
			$cachedFilename = WikiConfig::WIKICACHE."/".$id->getPath().WikiConfig::WIKICACHEFILEEXT;
	
			if(! $forcedCacheUpdate && is_file($cachedFilename)) {
					
				$cachedFileModTime = filemtime($cachedFilename);
				if ($cachedFileModTime >= filemtime($filename)
						&& $cachedFileModTime >= filemtime($headerFilename)
						&& $cachedFileModTime >= filemtime($footerFilename)) {
	
							$data = file_get_contents($cachedFilename);
							if ($data === false) {
								throw new Exception("Unable to read data file '$cachedFilename'!");
							}
							TestingTools::inform("Using cached file :".$cachedFilename);
							return $data;
						}
			}
		}
	
		$data = file_get_contents($filename);
		if ($data === false) {
			throw new Exception("Unable to read data file '$filename'!");
		}
		$headerData = file_get_contents($headerFilename);
		if ($headerData === false) {
			throw new Exception("Unable to read template file '$headerFilename'!");
		}
		$footerData = file_get_contents($footerFilename);
		if ($footerFilename === false) {
			throw new Exception("Unable to read template file '$footerFilename'!");
		}
	
		$data = $headerData."\n".$data."\n".$footerData;
		$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));
	
		if (!utf8_check($data)) {
			throw new Exception("File '$filename' is not an UTF8-encoded file!");
		}
	
		$out = self::parse($data, WikiTools::getSessionInfo('debug'));
	
		/*
		 * Write parser results to a file if CACHING is enabled.
		*/
		if (WikiTools::getSessionInfo("useCache") == true) {
			FileTools::createFolderIfNotExist(dirname($cachedFilename));
			if (file_put_contents($cachedFilename, $out) === false) {
				throw new Exception("Unable to write file '$cachedFilename'!");
			}
		}
	
		return $out;
	}
	
	static function parse($text, $forse_debug = true) {
	
		// 	$pathToPlugins = INC_PATH."piwo-v0.2/lib/plugins";
	
		//FIXME This are not plugins, but additional user-defined tokens -> rename!
		$pathToPlugins = null;
	
		$debugCatchedException = false;
		$wikiParser = new WikiParser($pathToPlugins);
		$o = "";
		$es = null;
	
		try {
			$wikiParser->parse($text);
			$o = $wikiParser->getResult();
		} catch (Exception $e) {
			$debugCatchedException = true;
			TestingTools::error("Exception catched! ERROR MESSAGE: ".pw_s2e(print_r($e->getMessage(), true)));
			TestingTools::error("ERROR TRACE: \n".pw_s2e($e->getTraceAsString()));
			$es = $e;
		}
	
		if ($debugCatchedException || $forse_debug) {
			TestingTools::inform("LEXER: ".$wikiParser->getLexer(), TestingTools::NOTYPEINFO);
			TestingTools::debug ( "PATTERN TABLE: \n" . $wikiParser->getLexer ()->getPatternTableAsString () );
	
			$treePrinter = new TreeWalker ( $wikiParser->getLexer ()->getRootNode (), new TreePrinter () );
			TestingTools::inform ( "PARSE TREE: \n" . StringTools::showLineNumbers ( $treePrinter->getResult () ) );
			TestingTools::inform ( "SPEED: Text parsed in " . $wikiParser->getLexer()->getExecutionTime()." seconds!" );
			TestingTools::inform ( "SOURCE:\n" . StringTools::showLineNumbers ( pw_s2e($wikiParser->getSource())));
	
			//$debugString .= "<h3>Debug: Parser - Schritte (TODO: ADAPT TO NEW LEXER)</h3>";
			//$lexer->printDebugInfo(1,1);
	
			if ($debugCatchedException) {
				throw $es;
			}
		}
	
		return $o;
	}
	
	function cacheUpdate($forced = false) {
		$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(WikiConfig::WIKISTORAGE)
		);
		foreach($files as $filename) {
			if(substr($filename, (-1) * strlen(WikiConfig::WIKIFILEEXT)) == WikiConfig::WIKIFILEEXT) {
				try {
					pw_wiki_get_parsed_file(WikiID::fromPath($filename, WikiConfig::WIKISTORAGE, WikiConfig::WIKIFILEEXT), $forced);
				} catch (Exception $e) {
					echo "<pre>Exception: Skipping file '$filename': $e\n</pre>";
				}
			}
		}
	}
	

}

?>