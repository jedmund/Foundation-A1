<?php
	require_once('../../lib/init/config.php');

	if ($session->is_logged_in()) {
		if (!empty($_GET) && !empty($_GET['i']) && is_numeric($_GET['i'])) {			
			$image = Image::find_by_id($_GET['i']);
			list($w, $h) = getimagesize(PUBLIC_PATH.DS.$image->full);
			
			$image->filename = array_pop(explode('/', $image->full));
			$image->w = $w;
			$image->h = $h;
			
			$display->w = (is_numeric($_GET['w'])) ? $_GET['w'] : '';
			$display->h = (is_numeric($_GET['h'])) ? $_GET['h'] : '';
			
			if (!empty($image->coords)) {
				$crop = json_decode($image->coords);
			}
			
			$setting = Setting::find_by_name('thumbnail_width');
			$crop->cw = $setting->get_value();
			
			$setting = Setting::find_by_name('thumbnail_height');
			$crop->ch = $setting->get_value();
			
			$vars['image'] 	 = $image;
			$vars['display'] = $display;
			$vars['crop'] 	 = $crop;
									   	
			$template = $twig->loadTemplate('view_image.html');
			echo $template->render($vars);
		} else {
			// Error
		}
	} else {
		redirect_to('../login');
	}