<?php 
	require_once('lib/init/config.php');

	if (!empty($_GET) && !empty($_GET['p']) && is_numeric($_GET['p'])) {
		$project = Project::find_by_id($database->escape_value($_GET['p']));
	} else if (!empty($_GET) && !empty($_GET['p']) && !is_numeric($_GET['p'])) {
		$slug = $database->escape_value($_GET['p']);
		$project = Project::find_by_slug($slug);
	} else {
		echo "That is not a valid project.";
	}

	if (($project && !$project->archived) || 
			($project && !empty($_GET['preview']) && $_GET['preview'] == substr(sha1($project->title), 0, 5))) { 
			
		// Set the Foundation and get the appropriate images.
		$project->set_foundation();
		$images = Image::find_by_pid($project->id);

		// On error, we should render a 404, since we won't have conditionals. 
		$setting = Setting::find_by_name('layout');
		$layout = $setting->get_value();
				
		// If a layout file exists titled after the project slug, we should
		// use that instead of the default project template.
		if (file_exists(PUBLIC_PATH.DS.'layout'.DS.$layout.DS.$project->slug.'.htm')) {
			$page = $project->slug;
		} else {
			$page = array_shift(explode(".", basename(__FILE__)));
		}
		
		$parsel = new Parsel($layout, $page, $project->id, $_SERVER['REQUEST_URI']);		
		$parsel->render();	

	} else {
		$layout = "default";
		$parsel = new Parsel($layout);
		$parsel->error("404");
	}	