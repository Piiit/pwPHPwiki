<?php

class PluginVar implements WikiPluginHandler {
	
	/*
	 * Valid variable names:
	 *   1) case-insensitive
	 *   2) start with a _ or a letter
	 *   3) continue with letters, digits or _ (underscore)
	 */
	const VALID_VARNAME = "^[a-z_]+([a-z0-9_]*?)$";
	private static $variables = array();
	
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
		
		foreach ($node->getChildren() as $parameterNode) {
			if(strcasecmp("pluginparameter", $parameterNode->getName()) == 0) {
				$token = new ParserRule($parameterNode, $parser);
				$text = $token->getText();
				
				$matches = preg_split("# *= *#", $text, 2, PREG_SPLIT_NO_EMPTY);
				$varname = utf8_strtolower(utf8_trim($matches[0]));
				$value = isset($matches[1]) ? utf8_trim($matches[1]) : null;
				
				/*
				 * Check if an array index is specified
				 */
				$index = null;
				if(preg_match("#(.*)\[([0-9]+?)\]#is", $varname, $matches)) {
					$index = $matches[2];
					$varname = $matches[1];
				}
				
				/*
				 * Check for illegal variable names.
				 */
				if (! self::isValidVarname($varname)) {
					return nop("VARIABLE '$varname' is not a valid variable name.");
				}
				
				/*
				 * Return existing variable values, if no set operator
				 * is present (i.e., =).
				 */
				if ($value === null) {
					if (isset(self::$variables[$varname])) {
						$vars = self::$variables[$varname];
						
						/*
						 * Value is an array.
						 */
						if(is_array($vars)) {
							if(array_key_exists($index, $vars)) {
								return $vars[$index];
							}
							return nop("VARIABLE '$varname' is an array, but has no index=$index.");
						}
						
						/*
						 * Value is a simple datatype.
						 */
						return $vars;
					} 
					return nop("VARIABLE '$varname' is undefined.");					
				} 
				
				/*
				 * Create or update variables. 
				 * Note: strings with length 0 are allowed
				 */
				if(preg_match("#\[(.*)\]#is", $value, $matches)) {
					self::$variables[$varname] = explode(",", $matches[1]);
					TestingTools::debug("Variable: adding '$varname' with array '$matches[1]'");
				} else {
					self::$variables[$varname] = $value;
					TestingTools::debug("Variable: adding '$varname' with value '$value'");
				}
			}
		}
	}
	
	private static function isValidVarname($name) {
		return (strlen($name) > 0 && preg_match("#".self::VALID_VARNAME."#is", $name));
	}

}