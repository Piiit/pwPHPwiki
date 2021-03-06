<?php

class PluginText implements WikiPluginHandler {
	
	public function getPluginName() {
		return "text";
	}

	public function runBefore(Parser $parser, Lexer $lexer) {
	}

	public function runAfter(Parser $parser, Lexer $lexer) {
	}

	public function run(Parser $parser, Node $node, $categories, $parameters) {
	    $token = new ParserRule($node, $parser);
	    $text = pw_e2u($token->getText());
	
	    switch ($categories[0]) {
	      	case "ucfirst":
		        $out = utf8_ucfirst($text);
	      	break;
		    case "ucwords":
		      	$out = utf8_ucwords($text);
		    break;
		    case "toupper":
		        $out = utf8_strtoupper($text);
	      	break;
	      	case "tolower":
	        	$out = utf8_strtolower($text);
	      	break;
	      	case "comma":
	        	$out = str_replace(".", ",", $text);
	      	break;
	    	default: 
	    		return nop("Plugin 'text': No method '".implode(".", $categories)."' found.");
	    }
	
	    $out = pw_s2e($out);
	    return $out;

	}

}