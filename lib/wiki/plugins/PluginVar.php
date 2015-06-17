<?php

class PluginVar implements WikiPluginHandler {
	
	public function getPluginName() {
		return "var";
	}

	public function runBefore(Parser $parser, Lexer $lexer) {
	}

	public function runAfter(Parser $parser, Lexer $lexer) {
	}

	public function run(Parser $parser, Node $node, $categories, $parameters) {
	
		if ($parameters == null) {
			return nop("Plugin '".$this->getPluginName()."': No variable name specified.");
		}
		
		$out = null;
		foreach ($node->getChildren() as $parameterNode) {
			if(strcasecmp("pluginparameter", $parameterNode->getName()) == 0) {
				$token = new ParserRule($parameterNode, $parser);
				$text = $token->getText();
				
				//TODO do this with quoted string tokens...
				//TODO add multiline values support
				$matches = array();
				if (preg_match("#([\w]+) *= *(.*)#i", $text, $matches)) {
					$varname = utf8_strtolower(utf8_trim($matches[1]));
					$value = utf8_trim($matches[2]);
					Variable::$variables[$varname] = $value;
					TestingTools::debug("Variable: adding '$varname' with value '$value'");
				} else {
					$varname = utf8_strtolower($text);
					if (isset(Variable::$variables[$varname])) {
						$out = Variable::$variables[$varname];
						$out = self::unescape($out);
					} elseif (preg_match("#(.*) *= *(.*)#i", $text, $matches)) {
						$varname = utf8_trim($matches[1]);
						return nop("VARIABLE '$varname' contains illegal characters.");
					} else {
						$_SESSION['pw_wiki']['error'] = true;
						return nop("VARIABLE '$varname' undefined.");
					}
				}
			}
		}
		
	    $out = pw_s2e($out);
	    return $out;

	}
	
	private static function unescape($txt) {
		return str_replace(array('\"', '\>'), array('"', '&gt;'), $txt);
	}

}