<?php
class Parser {
	public function callFunction($node, $type) {
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
	
	
	public function getText2($node) {
	
		$text = "";
		for ($node = $node->getNextSibling(); $node != null; $node = $node->getNextSibling()) {
			$ret = $this->getText($node);
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