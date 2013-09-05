<?php
interface Module {
	public function getName();
	public function getVersion();
	public function activateIf();
	public function availableFor();
	
	public function getMenuText();
	public function getMenuAvailability($mode);
	
	public function getDialog();
}

?>