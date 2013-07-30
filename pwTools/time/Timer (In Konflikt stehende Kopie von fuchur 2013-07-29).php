<?php
//Measure executing time
//TODO Refactor, code conventions!!

class Timer {
	
	private $_time;
	private $_lastTime;

	//Constructor
	public function __construct ($start = true) {
		$this->start();
	}

	//Return the time
	private function _getTime() {
		return $this->_time;
	}

	//Return the last time
	private function _getLastTime() {
		return $this->_lastTime;
	}

	//Start timer and set time
	private function start() {
		$this->_time = $this->_lastTime = $this->_getCurrentTime();
		return true;
	}

	//Return the current time in microseconds
	private function _getCurrentTime() {
		$mtime = explode(" ",microtime());
		return $mtime[1] + $mtime[0];
	}

	//Measure total time elapsed since last time
	public function getIntermediateTime ($round = 3) {
		$time = round ($this->_getCurrentTime() - $this->_getLastTime(), $round);
		$this->_lastTime = $this->_getCurrentTime();
		return $time;
	}

	//Measure total time
	public function getElapsedTime ($round = 3) {
		return round ($this->_getCurrentTime() - $this->_getTime(), $round);
	}
}