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
		
		if (!empty($_GET) && !empty($_GET['p']) && is_numeric($_GET['p'])) {			
			$project = Project::find_by_id($database->escape_value($_GET['p']));
			$project->set_foundation();
			$project->make_thumb_path();
			$project->title = stripslashes($project->title);
			$project->client = stripslashes($project->client);
			$project->blurb = stripslashes($project->blurb);
			$project->description = stripslashes(str_replace(array('\r', '\n', '%0a', '%0d'), "\n", $project->description));
			$project->hash = substr(sha1($project->title), 0, 5);
			$project->month = month_from_date($project->completed);
			$project->year = year_from_date($project->completed); 
			
			$foundation = Foundation::find_by_id($project->fid);
			$fields = $foundation->get_fields();
			$content = $project->get_field_values($fields);
			
			$foundations = Foundation::find_all();
			
			// Get the Image information for the project.
			$images = Image::find_by_pid($project->id);

			// For each image, we need to strip slashes from captions and coords
			foreach ($images as $image) {
				$image->check_path('full');
				$image->caption = stripslashes($image->caption);
				$image->coords = stripslashes($image->coords);
			}
			
			// Get image-related settings.
			$setting = Setting::find_by_name('thumbnail_width');
			$crop->w = $setting->get_value();
				
			$setting = Setting::find_by_name('thumbnail_height');
			$crop->h = $setting->get_value();

	
			
			// Stick everything in an array to pass to the template.
			$vars['title']			 = "Edit Project \"" . $project->title . "\"";
			$vars['project']	   = $project;
			$vars['foundation']  = $foundation;
			$vars['foundations'] = $foundations;
			$vars['fields'] 		 = $content;
			$vars['images'] 		 = (!empty($images)) ? $images : "";
			$vars['crop']			 	 = $crop;
			$vars['mode']				 = "edit";
		 	$vars['notices']		 = $notices;
									   	
			$template = $twig->loadTemplate('create.html');
			echo $template->render($vars);
		} else {
			// Error
		}
	} else {
		redirect_to('../login');
	}