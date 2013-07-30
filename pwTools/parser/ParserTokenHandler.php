<?php
interface ParserTokenHandler {
	public function onEntry();
	public function onExit();
	public function doRecursion();
	public function getName();
}

?>