<?php
abstract class WikiPlugin {
	
	private $_data = array();
	
	abstract public function runBefore();
	abstract public function runAfter();
	
	public function setData($id, $data) {
		
	}
	
	public function getData($id) {
		
	}
}

?>