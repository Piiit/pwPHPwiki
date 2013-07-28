<?php

interface TreeWalkerConfig {
	public function callBefore($node);
	public function callAfter($node);
	public function getResult();
	public function doRecursion(Node $node = null);
}

?>