<?php

require_once("PhpQuickProfiler.php");

class Profiler {
	
	private $profiler;
	private $db = '';
	
	public function __construct() {
		$this->profiler = new PhpQuickProfiler(PhpQuickProfiler::getMicroTime());
	}

	public function __destruct() {
		$this->profiler->display($this->db);
	}
	
}

?>