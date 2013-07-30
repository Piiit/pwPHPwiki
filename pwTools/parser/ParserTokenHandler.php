<?php
interface ParserTokenHandler {
	public function onEntry();
	public function onExit();
	public function doRecursion();
	public function getName(); //TODO CHeck, possible to get the name from class name (lowercase) ????
	public function getPattern();
	public function getAllowedModes();
}

?>