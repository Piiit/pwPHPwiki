<?php


class Parser {
	
	const ONENTRY = 0;
	const ONEXIT = 1;
	
	public static function callFunction(Node $node, $type) {
		$prefix = 'e';
		if ($type == self::ONENTRY) {
			$prefix = 's';
		}
	
		if ($node->getName() == Token::TXT) {
			return $node->getData();
		}
	
		if (function_exists($prefix.$node->getName())) {
			return call_user_func($prefix.$node->getName(), $node, $this);
		}
	}
		
	public static function getText2(Node $node) {
	
		$text = "";
		for ($node = $node->getNextSibling(); $node != null; $node = $node->getNextSibling()) {
			$ret = $node->getText();
			if (!$ret) {
				$ret = $this->callFunction($node, self::ONENTRY);
				$ret .= $this->callFunction($node, self::ONEXIT);
			}
			$ret = pw_s2e($ret);
			$text .= $ret;
		}
	
		return $text;
	}
	
	
	
}

?>