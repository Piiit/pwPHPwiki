<?php
class LogEntry {
	private $timestamp;
	private $level;
	private $description;
	private $data;
	private $debugBackTrace;
	
	public function __construct($timestamp, $level, $description, $data, $debugBackTrace) {
		$this->timestamp = $timestamp;
		$this->level = $level;
		$this->description = $description;
		$this->data = $data;
		$this->debugBackTrace = $debugBackTrace;
	}
	
	public function getTimestamp() {
		return $this->timestamp;
	}

	public function getLevel() {
		return $this->level;
	}

	public function getDescription() {
		return $this->description;
	}

	public function getData() {
		return $this->data;
	}

	public function getDebugBackTrace() {
		return $this->debugBackTrace;
	}
}

?>