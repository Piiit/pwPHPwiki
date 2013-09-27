<?php
interface ModuleHandler {
	public function getName();
	public function getVersion();
	public function execute();
}

?>