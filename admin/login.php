<?php
	require_once('../lib/init/config.php');
	
	if (!empty($_POST) && !empty($_POST['submit'])) {	
		// Check for missing fields in the submitted form.
		$missing = required(array('username', 'password'), $_POST);

		// If nothing is missing, proceed with logging in.
		if (empty($missing)) {
			// Try to find the user to login with the provided credentials,
			// and toggle whether or not this is a permanent login.
			$user = User::authenticate($_POST['username'], $_POST['password']);

			$permanent = (!empty($_POST['remember']) && $_POST['remember'] == 'on') ? true : false;
	
			if ($user) {
				$session->login($user, $permanent);	
			} else {
				// Handle invalid user error
				echo "Error";
				die();
			}
		} else {
			// Handle missing field error
			$error = "You are missing the following fields: ";
			for ($i = 0; $i < count($missing); $i++) {
				$error .= $missing[$i];
			}
			$vars['error'] = $error; 
		}
	}
	
	
	if (!$session->is_logged_in()) {
		$vars = array("title" => "Login");
		
		$template = $twig->loadTemplate('login.html');
		echo $template->render($vars);	
	} else {
		redirect_to('index.php');
	}
	
?>