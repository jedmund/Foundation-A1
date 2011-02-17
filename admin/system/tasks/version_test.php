<?php
	// DO NOT MODIFY THIS FILE
	// This file checks the official Foundation website for updates to
	// the software.

	require_once('../../lib/init/config.php');
	
	$version_check = "http://getfoundation.com/v/";
	
	$res = PUBLIC_PATH.DS.'admin'.DS.'system'.DS.'tasks'.DS."res.txt";
	$fh = fopen($res, 'a');
	$string = strftime("%Y-%m-%d %H:%M:%S", strtotime("now")) . "\nversion check running... ";
	fwrite($fh, $string);

	function get_http_response_code($url) {
		$headers = get_headers($url);
		return substr($headers[0], 9, 3);
	}
	
	if (get_http_response_code($version_check) != "404") {
		$version = file_get_contents($version_check);
		if ($version == VERSION) {
			$string = "Up to date at " . $version . "!\n";
			$string .= "------------------------------------------\n";
			fwrite($fh, $string);
			
			// Schedule a new task to execute in three days.
			$time = 60 * 60 * 24 * 3;
			$execute = strftime("%Y-%m-%d %H:%M:%S", strtotime("+3 days"));
			$task = new Systask;
			$task->schedule("version_test", $execute, "", 1);
			$task->save();
		} else if ($version > VERSION) {
			$string = "New version ".$version." available!\n";
			$string .= "------------------------------------------\n";
			fwrite($fh, $string);
			
			// Make a notice stating that there is an update available.
			$notice = new Notice;
			$notice->type = 9;
			$notice->text = "There is a new update available! http://getfoundation.com/ to get the update!";
			$notice->date = strftime("%Y-%m-%d %H:%M:%S", strtotime("now"));
			$notice->save();
		}
	} else {
		// Make a notice stating that there is a problem with the
		// version checker and to check the Foundation website.
		$string = "Error checking for new version!\n";
		$string .= "------------------------------------------\n";
		fwrite($fh, $string);
	}
	
	fclose($fh);
