<?php 
	require_once('lib/init/config.php');
	
	if (!empty($_GET['p'])) {
		if (!$project = Project::find_by_slug($_GET['p'])) {
			// Find a better way to get this.
			$user = User::find_by_id(10);
		
			// On error, we should render a 404, since we won't have conditionals. 
			$setting = Setting::find_by_name('layout');
			$layout = $setting->get_value();
			
			$page = $_GET['p'];
				
			$parsel = new Parsel($layout, $page, $user->id);		
			$parsel->render();
		} else {
			redirect_to("/projects/" . $_GET['p']);
		}
	}
?>