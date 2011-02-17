<?php
	require_once('../../lib/init/config.php');
	
	if ($session->is_logged_in()) {
		$user = User::find_by_id($_SESSION['uid']);
				
		$vars = array('title'		 	 =>	'Ideas');
								  
		$template = $twig->loadTemplate('ideas.html');
		echo $template->render($vars);
	} else {
		redirect_to('../login');
	}
	
?>