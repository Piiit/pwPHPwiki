<?php
//Measure executing time
//TODO Refactor, code conventions!!
class timer
{
	private $time;
	private $last_time;

	//Constructor
	public function __construct ($start = true)
	{
		$this->start();
	}


	//Return the time
	private function get_time()
	{
		return $this->time;
	}


	//Return the last time
	private function get_last_time()
	{
		return $this->last_time;
	}


	//Start timer and set time
	private function start()
	{
		$this->time = $this->last_time = $this->get_current_time();
		return true;
	}


	//Return the current time in microseconds
	private function get_current_time()
	{
		//Return time
		$mtime = explode(" ",microtime());
		return $mtime[1] + $mtime[0];
	}

	//Measure total time elapsed since last time
	public function measure_intermediate ($round = 3)
	{
		$time = round ($this->get_current_time() - $this->get_last_time(), $round);
		$this->last_time = $this->get_current_time();
		return $time;
	}


	//Measure total time
	public function measure_elapsed ($round = 3)
	{
		return round ($this->get_current_time() - $this->get_time(), $round);
	}
}