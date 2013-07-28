<?php
interface ParserTokenHandler {
	public function onEntry(Node $node);
	public function onExit(Node $node);
	public function doRecursion();
}

?>