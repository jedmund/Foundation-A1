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
		
		$foundations = Foundation::find_all();
		
		$setting = Setting::find_by_name('thumbnail_width');
		$crop->w = $setting->get_value();
			
		$setting = Setting::find_by_name('thumbnail_height');
		$crop->h = $setting->get_value();

		$vars['title'] 			 = 'Create a Project';
		$vars['foundations'] = $foundations;
		$vars['mode']				 = "create";
		$vars['crop']				 = $crop;
	 	$vars['notices'] 		 = $notices;
		 	
		$template = $twig->loadTemplate('create.html');
		echo $template->render($vars);
	} else {
		redirect_to('../login');
	}