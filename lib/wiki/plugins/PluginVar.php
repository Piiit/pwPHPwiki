<?php

/*
 * TODO: varvalues > array syntax for values
 * TODO: varnames  > error handling for illegal variable names (define clear regexp for varnames
 * TODO: varvalues > multiline value support
 */
class PluginVar implements WikiPluginHandler {
	
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
				
				$matches = array();
				
				/*
				 * Creating or updating variables.
				 * FIXME This is a bad regexp... what is allowed exactly for varnames and values
				 */
				if (preg_match("#([\w]+) *= *(.*)#i", $text, $matches)) {
					$varname = utf8_strtolower(utf8_trim($matches[1]));
					$value = utf8_trim($matches[2]);
					self::$variables[$varname] = $value;
					TestingTools::debug("Variable: adding '$varname' with value '$value'");
					
					/*
					 * Nothing to output, just store variables and values...
					 */
					return;
				}

				/*
				 * Returning existing variable values.
				 */
				$varname = utf8_strtolower($text);
				if (isset(self::$variables[$varname])) {
    				return self::$variables[$varname];
				} 
				
				/*
				 * Nothing found, check for illegal variable names.
				 * FIXME This should be done at the beginning, according to a unique clear regexp.
				 */
				if (preg_match("#(.*) *= *(.*)#i", $text, $matches)) {
					$varname = utf8_trim($matches[1]);
					return nop("VARIABLE '$varname' contains illegal characters.");
				}
			}
		}
		
		/*
		 * Legal variable name, but not found in variable collection.
		 */
		return nop("VARIABLE '$varname' is undefined.");
	}

}