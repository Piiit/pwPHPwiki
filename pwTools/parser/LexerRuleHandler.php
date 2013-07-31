<?php
interface LexerRuleHandler {
	public function getName(); 
	public function getPattern();
	public function getAllowedModes();
}

?>