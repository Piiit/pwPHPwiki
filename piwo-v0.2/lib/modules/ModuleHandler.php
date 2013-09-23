<?php
interface ModuleHandler {
	public function getName();
	public function getVersion();
	public function permissionGranted($userData);
	public function getMenuText();
	public function getMenuAvailability($mode);
	public function execute();
}

?>