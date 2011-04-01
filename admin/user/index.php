<?php
	require_once('../../lib/init/config.php');
	
	if ($session->is_logged_in()) {
		$notices = Notice::check();
		if (!empty($notices)) {
			foreach ($notices as $notice) {
				if ($notice->type < 9) { 
					$notice->viewed = 1;
					$notice->save();
				}
			}
		}
		
		$user = User::find_by_id($_SESSION['uid']);
		$user->email = $user->get_email();
		$user->social = json_decode($user->social);
		$user->status = clean($user->status);
		$user->bio = clean($user->bio);
		
		$vars['title'] 	 = "User";
		$vars['user']		 = $user;
		$vars['social']  = $user->social;
	 	$vars['notices'] = $notices;
	 	
		$template = $twig->loadTemplate('user.html');
		echo $template->render($vars);
	} else {
		redirect_to('../login');
	}
	
?>