<?php
	require_once('../lib/init/config.php');

	if ($session->is_logged_in()) {
		$user = User::find_by_id($_SESSION['uid']);
		$str  = "Logged in as " . $user->username;
		$nav  = "<li><a href='createproject.php'>Create Project</a></li>";
		$nav .= "<li><a href='viewprojects.php'>Manage Projects</a></li>";
		$nav .= "<li><a href='logout.php'>Logout</a></li>";
			
		$loader = new Twig_Loader_Filesystem('system/templates');
		$twig = new Twig_Environment($loader, false);
		
		$vars = array("title" => "Home", "user" => $user);
		$template = $twig->loadTemplate('home.html');
		echo $template->render($vars);
	} else {
		redirect_to('login.php');
	}
?>