<?php

interface WikiPluginHandler {
	public function getPluginName();
	public function runBefore(Parser $parser);
	public function runOnTokenFound();
	public function runAfter();
}

?>