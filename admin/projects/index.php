<?php
	require_once('../../lib/init/config.php');
	
	if ($session->is_logged_in()) {
		$user = User::find_by_id($_SESSION['uid']);
		
		// Get the active notices and then dismiss them.
		$notices = Notice::check();
		if (!empty($notices)) {
			foreach ($notices as $notice) {
				if ($notice->type < 9) { 
					$notice->viewed = 1;
					$notice->save();
				}
			}
		}
				
		$projects = Project::find_by_uid($user->id);
		$archives = Project::find_archived($user->id);
		
		foreach ($projects as $project) {
			$project->title = stripslashes($project->title);
			$project->make_thumb_path('system');
		}
		
		foreach ($archives as $archive) {
			$archive->title = stripslashes($archive->title);
			$archive->make_thumb_path('system');
		}
		
		$vars['title'] =	'Projects';
		$vars['projects'] = $projects;
	 	$vars['archives']	= $archives;
	 	$vars['notices'] = $notices;
								  
		$template = $twig->loadTemplate('projects.html');
		echo $template->render($vars);
	} else {
		redirect_to('../login');
	}