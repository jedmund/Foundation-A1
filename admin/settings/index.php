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
		$site_settings = Setting::find_all();

		$settings = array();
		foreach ($site_settings as $setting) {
			$settings[$setting->get_name()] = $setting->get_value();
		}
		
		// When did we last check for a new version?
		if (empty($settings['last_version_check'])) {
			$settings['last_version_check'] = "Never";
		} else {
			$settings['last_version_check'] = nicetime($settings['last_version_check']);		
		}
		
		// Get the version and build constants.
		$settings['version'] = VERSION;
		$settings['build'] = BUILD;
		
		// Get layouts
		$layout_path = PUBLIC_PATH.DS.'layout';
		$layouts = array();
		foreach (scandir($layout_path) as $item) {
			if (!is_dir($item)) {
				$layouts[] = array('name'=>$item);
			}
		}
		
		// Temporary
		$start = array(array('name'=>'index'), array('name'=>'about'), array('name'=>'first project'));
		$transitions = array(array("name" => "None", 			  			"value" => "none"),
												 array("name" => "Fade", 			  			"value" => "fade"), 
												 array("name" => "Scroll Up",   			"value" => "scrollUp"),
												 array("name" => "Scroll Down", 			"value" => "scrollDown"),
												 array("name" => "Scroll Left", 			"value"	=> "scrollLeft"),
												 array("name" => "Scroll Right", 			"value" => "scrollRight"),
												 array("name" => "Scroll Horizontal", "value" => "scrollHorz"),
												 array("name" => "Scroll Vertical", 	"value" => "scrollVert"),
												 array("name" => "Cover", 						"value" => "cover"),
												 array("name" => "Uncover", 					"value" => "uncover")
									 );
												 
		$vars['title']		 	 = 'Settings';
	 	$vars['settings'] 	 = $settings;
	 	$vars['start']	  	 = $start;
	 	$vars['transitions'] = $transitions;
	 	$vars['notices'] 		 = $notices;
	 	$vars['layouts']		 = $layouts;
								  
		$template = $twig->loadTemplate('settings.html');
		echo $template->render($vars);
	} else {
		redirect_to('../login');
	}
	
?>