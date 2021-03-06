<?php

class PluginWiki implements WikiPluginHandler {
	
	public function getPluginName() {
		return "wiki";
	}

	public function runBefore(Parser $parser, Lexer $lexer) {
		/*
		 * User information passed to the wiki parser to be accessed from
		 * syntax handlers.
		 */
		$parser->setUserInfo('wiki.file.type', $lexer->getOrigTextFileFormat()->getString());
		$parser->setUserInfo('wiki.lexer.performance', $lexer->getExecutionTime());
		$parser->setUserInfo('wiki.version', WikiConfig::PIWOVERSION);
		
		/*
		 * Create an index table to be used for headers and table of content
		 * plugin.
		 */
		$parser->setUserInfo(
				'indextable',
				WikiTocTools::createIndexTable($parser, $lexer->getRootNode())
		);
	}

	public function runAfter(Parser $parser, Lexer $lexer) {
	}

	public function run(Parser $parser, Node $node, $categories, $parameters) {
	    
		if ($categories == null) {
			return nop("Plugin '".$this->getPluginName()."': No default command specified.");
		}
		
		$out = null;
	    switch ($categories[0]) {
	    	
	    	/*
	    	 * Wiki and Parser functions.
	    	 */
	      	case "version": 
	      		$out = $parser->getUserInfo('wiki.version'); 
	      	break;
			case 'page':
				$id = WikiTools::getCurrentID();
				$out = pw_url2u($id->getPage()); 
			break;
			case 'wrongid':
				try { 
					$wrongId = WikiTools::getSessionInfo('wrongid');
					$out = pw_url2u($wrongId->getID());
				} catch (Exception $e) {
					$out = "";
				} 
			break;
			case 'id':
				$id = WikiTools::getCurrentID(); 
				$out = pw_url2u($id->getID()); 
			break;
			case 'startpage': 
				$out = ':'.pw_url2u(WikiConfig::WIKINSDEFAULTPAGE); 
			break;
			
			/*
			 * The following methods build HTML to be shown, we do not want to
			 * encode htmlentities. All local methods beginning with 'category'
			 * are handlers for specific subcategories given by $categories[1-n].
			 */
			default:
				$objectMethod = 'category'.ucfirst($categories[0]);
				if(method_exists($this, $objectMethod)) {
					$subCategories = array_slice($categories, 1);

					$out = call_user_func(
						array($this, $objectMethod), 
						$subCategories, 
						$node, 
						$parser
					);
					
					if($out !== null) {
						return $out;
					}
				} 
	    }
	    
	    if ($out === null) {
	    	return nop("Plugin '".$this->getPluginName()."': No method '".implode(".", $categories)."' found.");
	    }
	    
	    $out = pw_s2e($out);
	    return $out;

	}
	
	function categoryTrace ($cat, Node $node, Parser $parser) {
		$sep = ' &raquo; ';
	
		$id = WikiTools::getCurrentID();
	
		if($id->getID() == ":".WikiConfig::WIKINSDEFAULTPAGE || $id->getID() == ":") {
			$out = "Home";
		} else {
			$out = "<a href='?id=".pw_s2url(":".WikiConfig::WIKINSDEFAULTPAGE)."'>Home</a>";
		}
	
		$current_namespace = "";
		foreach ($id->getFullNSAsArray() as $index => $namespace) {
			if($id->isNS() && $index == sizeof($id->getFullNSAsArray()) - 1) {
				$out .= $sep.pw_s2e(utf8_ucfirst($namespace));
			} else {
				$current_namespace .= $namespace.":";
				$out .= $sep."<a href='?id=:$current_namespace'>".pw_s2e(utf8_ucfirst($namespace))."</a>";
			}
		}
	
		if($id->isNS() || $id->getPage() == WikiConfig::WIKINSDEFAULTPAGE) {
			return $out;
		}
	
	
		return $out.$sep.utf8_ucfirst($id->getPage());
	}
	
	function categoryToc($cat, Node $node, Parser $parser) {
		$indextable = $parser->getUserInfo('indextable');
		if ($indextable instanceof IndexTable) {
			$out	= '<div class="toc" id="__toc">';
			$lastlvl = 0;
			foreach($indextable->getAsArray() as $item) {
				if ($lastlvl < $item->getLevel()) {
					$diff = $item->getLevel() - $lastlvl;
					for ($i = 0; $i < $diff; $i++)
						$out .= '<ul>';
				} elseif ($lastlvl > $item->getLevel()) {
					$diff = $lastlvl - $item->getLevel();
					for ($i = 0; $i < $diff; $i++)
						$out .= '</ul>';
				}
				$out .= '<li>';
				$out .= '<a href="#header_'.$item->getId().'">'.$item->getId().' '.pw_s2e($item->getText()).'</a>';
				$out .= '</li>';
				$lastlvl = $item->getLevel();
			}
			$out .= '</ul>';
			$out .= '</div>';
		}
		return $out;
	}
	
	public function categoryLexer($cat, Node $node, Parser $parser) {
		switch ($cat[0]) {
			case 'version': 
				return Lexer::getVersion();
			case 'performance':
				return $parser->getUserInfo("wiki.lexer.performance");
		}
		return null;
	}
	
	public function categoryFile($cat, Node $node, Parser $parser) {
		switch ($cat[0]) {
			case 'type':
				return $parser->getUserInfo("wiki.file.type");
			case 'path':
				return 'http://'.$_SERVER['SERVER_NAME'].FileTools::dirname($_SERVER['PHP_SELF']);
		}
		return null;
	}
	
	/**
	 * Process namespace categories:
	 * @param null|array $cat 
	 * <li> null = Default handler: Return NS name</li>
	 * <li> array = categories and subcategores, last entry is the method</li>
	 * @param Node $node
	 * @param Parser $parser
	 */
	public function categoryNs($cat, Node $node, Parser $parser) {
		
		/*
		 * Default handling, no subcategory set: Return the namespace name.
		 */
		if(! isset($cat[0])) {
			$id = WikiTools::getCurrentID();
			$out = pw_url2u($id->getNS());
			$out = pw_s2e($out);
			return $out;
		}

		/*
		 * Subcategory handling.
		 */
		switch ($cat[0]) {
			case 'countsubs':
				/*
				 * Count all wikipages within the current namespace.
				 */
				$path = PW_WIKI_PATH.WikiConfig::WIKISTORAGE.WikiTools::getCurrentID()->getFullNSPath();
				return count(glob($path."/*".WikiConfig::WIKIFILEEXT));
			break;
			case 'full':
				$id = WikiTools::getCurrentID();
				$out = pw_url2u($id->getFullNS());
				$out = pw_s2e($out);
				return $out;
			break;
			case 'toc':
				return PluginWiki::buildNamespaceToc($parser, $node);
			break;
		}
		return null;
	}
	
	static function buildNamespaceToc(Parser $parser, Node $node) {
	
		/*
		 * Parse plugin parameters.
		 * FIXME Plugin parameters parsing: loop and parse one after the other, stop on error 
		 */ 
		try {
			$token = new ParserRule($node, $parser);
			$cont = $token->getArrayFromNode($node);
		} catch (Exception $e) {
			return nop("Syntax error: ".$e->getMessage());
		}
		$curID = WikiTools::getCurrentID();
	
		//TODO errors should bubble up
		//TODO Title should be of the form TITLE=string...
		try {
			$id = new WikiID(isset($cont[1]) && WikiID::isValidAndAbsolute($cont[1]) ? $cont[1] : $curID->getFullNS());
		} catch (Exception $e) {
			return nop($e->getMessage());
		}
	
		// Parameter TITLE: Print Title
		$titeltxt = "";
		//TODO do not use position 0 and 1 of the array (1 is the title string)
		if (in_array("TITLE", $cont)) {
			$titeltxt = utf8_ucwords(str_replace(":", " &raquo; ", $id->isRootNS() ? "[root]" : trim($id->getFullNS(), ":")));
			$titeltxt = "Content of namespace <i>\"$titeltxt\"</i>: ";
		}
	
		// Parameter NOERR: Do not show error messages!
		$error = in_array("NOERR", $cont);
	
		$path = WikiConfig::WIKISTORAGE.$id->getFullNSPath();
	
		$wikiFiles = array_merge(glob($path."*/", GLOB_ONLYDIR), glob($path."*".WikiConfig::WIKIFILEEXT));
		sort($wikiFiles);
	
		// Titel werden nur ausgegeben, wenn Fehlermeldungen auch ausgegeben werden dürfen!
		// ...sonst kann es zu alleinstehenden Titeln kommen.
		$out = "";
		if (utf8_strlen($titeltxt) > 0) {
			$out .= $titeltxt;
		}
		if($error && empty($wikiFiles)) {
			return $out."<br />".nop("There are no pages in the namespace '".pw_s2e($id->getFullNS())."'.", false);
		}
	
		$out .= "<ul>";
		$uniqueWikiLinks = array();
		foreach($wikiFiles as $file) {
			$curId = WikiID::fromPath($file, WikiConfig::WIKISTORAGE, WikiConfig::WIKIFILEEXT);
			if($curId->getPage() == WikiConfig::WIKINSDEFAULTPAGE) {
				continue;
			}
	
			if($curId->isNS()) {
				$url = $curId->getFullNSAsUrl();
				$name = $curId->getNS();
			} else {
				$url = $curId->getIDAsUrl();
				$name = $curId->getPage();
			}
	
			if(!in_array($name, $uniqueWikiLinks)) {
				$out .= "<li><a href='?id=".$url."'>".pw_s2e(utf8_ucfirst($name))."</a></li>";
				$uniqueWikiLinks[] = $name;
			}
		}
		$out .= "</ul>";
	
		return $out;
	}
	
	function categoryFootnotes($cat, Node $node, Parser $parser) {
		 
		$footnoteList = $parser->getUserInfo('footnotelist');
		 
		$o = '<div class="footnotes">';
		$o .= '<ol>';
		$i = 0;
		foreach($footnoteList as $ftn) {
			$i++;
			$o .= '<li><a class="footnote_t" id="fn__'.$i.'" href="#fnt__'.$i.'">&uarr;</a> ';
			$o .= $ftn;
			$o .= '</li>';
		}
		$o .= '</ol>';
		if ($i == 0) {
			$o .= 'There are no footnotes in this article.';
		}
		$o .= '</div>';
		return $o;
	}
	
	
}