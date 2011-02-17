<?php
	require_once('../../lib/init/config.php');
	
	if ($session->is_logged_in()) {
		if (!empty($_POST['submit'])) {
			if (empty($_POST['uid'])) {
				$user = User::find_by_id($_SESSION['uid']);
			} else {
				$user = User::find_by_id($database->escape_value($_POST['uid']));
			}
			
			$social = array();
			$keys = array_keys($_POST);
			foreach ($keys as $key) {
				if (strstr($key, "social")) {
					$parts = explode("_", $key);
					$service = $parts[1];
					$social[$service] = $_POST[$key];
				}
			}
			
			$user->first_name = $database->escape_value($_POST['first_name']);
			$user->last_name  = $database->escape_value($_POST['last_name']);
			$user->status 	  = addslashes($_POST['status']);
			$user->bio 			  = addslashes($_POST['bio']);
			$user->photo 			= $database->escape_value($_POST['photo_value']);
			$user->social 		= json_encode($social);
			$user->save();
			
			redirect_to("index.php");
		}
	}