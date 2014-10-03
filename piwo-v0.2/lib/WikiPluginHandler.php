<?php

interface WikiPluginHandler {
	public function getName();
	public function runBefore();
	public function runOnTokenFound();
	public function runAfter();
}

?>