<?php
	require_once('../../lib/init/config.php');

	// A project must have a title, foundation, and description. 
	// We test to make sure it has those things and if we're missing 
	// anything, generate an error.
	
	$fields = array();
	if (empty($_POST['title']) || empty($_POST['foundation']) || empty($_POST['desc'])) {
		if (empty($_POST['title']) || $_POST['title'] == "Project Title") $fields[] = "Title";
		if (empty($_POST['foundation'])) $fields[] = "Foundation";
		if (empty($_POST['description'])) $fields[] = "Description";

		$text = "You must add a Title, Foundation, and Description before continuing.<br><br>You're missing "; 
		message(ERROR, $text, $fields);
		
	// A project must have a unique title. We test to make sure it does, 
	// and if it doesn't, we generate an error.
	} else if ($project = Project::find_by_title($database->escape_value($_POST['title'])) && empty($_POST['id'])) {
		$text = "A project with this title already exists.<br>Please choose a different title.";
		message(ERROR, $text);
		
	// Otherwise, resume adding the project.
	} else {
		$project = (empty($_POST['id'])) ? new Project : Project::find_by_id($database->escape_value($_POST['id']));
		$foundation = Foundation::find_by_name($database->escape_value($_POST['foundation'])); 
		$user = User::find_by_id($_SESSION['uid']);
		
		// Check if the title changed.
		if ($project->title != $database->escape_value($_POST['title'])) {
			$old_path = $project->path;
		} else {
			$old_path = false;
		}
		
		$project->uid							 = $user->id;
		$project->fid 				 	 	 = $foundation->id;
		$project->title 			 		 = $database->escape_value($_POST['title']);
		$project->blurb 			 		 = $database->escape_value($_POST['blurb']);
		$project->slug						 = Project::create_slug($project->title);
		
		// mysql_real_escape_string is breaking nl2br
		// addslashes seems to work better.
		$project->description			 = addslashes($_POST['desc']);
		$project->client					 = $database->escape_value($_POST['client']);
		$project->path 						 = DS."content".DS.$user->username.DS.to_filename($project->slug).DS;
		
		// Convert dates to digits.
		$month = month_to_digit($database->escape_value($_POST['month']));
		$year = (is_numeric($_POST['year'])) ? $database->escape_value($_POST['year']) : "";
		$project->completed				 = $year . "-" . $month . "-00 00:00:00";
		
		// Set the Foundation so that we properly save Foundation data.
		$project->set_foundation($project->fid);
		
		// Decode the data.
		$data = json_decode($_POST['fdata']);
		
		for ($i = 0; $i < count($data); $i++) {
			// The object containing our Field information.
			$object = json_decode($data[$i]);
			
			// The generic Field object.
			$field = Field::find_by_name($object->field);
			
			// The name of the given field to insert as an instance variable
			// of the project.
			$name = $object->field;
			
			// The values of the given field.
			$values = $object->value;
			
			if ($field->type == 'fstinput') {
				$field = Field::find_by_name($name);
				$project->$name = json_encode($field->clean_fstinput_vals($values));
			} else {
				$project->$name = $values;
			}
		}		
		
		// Save the image order now that the user has saved.
		$order = json_decode(stripslashes($_POST['image_order']));
		foreach ($order as $obj) {
			if (!empty($obj->iid) && !empty($obj->seq)) {
				$image = Image::find_by_id($obj->iid);
				$image->sequence = $obj->seq;
				$image->save();	
			}
		}
		
		// Check to see if we are able to save the project.
		if (!$project->save()) {
			message(ERROR, "There was an error saving your project.<br>Please try again.");
		} else {
			// If the name has changed, the path should change, and we need to 
			// move the old files.
			if ($old_path) {
				rename(PUBLIC_PATH.$old_path, PUBLIC_PATH.DS."content".DS.$user->username.DS.to_filename($project->slug).DS);
			}

			if (!is_dir(PUBLIC_PATH.$project->path)) {
				$project->id = ($project->id < 1) ? $database->insert_id() : $project->id;

				if (@!$project->create_folder()) {
					$notice = new Notice;
					$notice->type  = 1;
					$notice->text  = "There was a problem creating the folder for the project '" . $project->title . "'. ";
					$notice->text .= "Please check the permissions of the 'content' folder and try again.";
					$notice->save();
				} else {
					// Move all the temp images into the newly created folder.
					// Save their image objects with this PID.
					$images = Image::find_by_pid(0);
					foreach ($images as $image) {
						$filename = array_pop(explode('/', $image->full));
						rename(PUBLIC_PATH.DS.$image->full, PUBLIC_PATH.$project->path.$filename);
					
						$image->pid  = $project->id;
						$image->full = $project->path.$filename;
						$image->generate_sizes();
						$image->save();
					}
					
					// Move the thumbnail and system thumbnail into the newly created
					// folder.
					$temp 			= PUBLIC_PATH.DS.'content'.DS.'temp';
					$temp_thumb = $temp.DS.'thumb.png';
					$temp_sys   = $temp.DS.'system_thumb.png';
					$dest 			= PUBLIC_PATH.$project->path;

					if (file_exists($temp_thumb)) rename($temp_thumb, $dest.'thumb.png');
					if (file_exists($temp_sys)) rename($temp_sys, $dest.'system_thumb.png');
					
					// If the temp dir is not empty, empty it.
					if (!is_empty_dir($temp)) { 
						foreach (glob($temp.DS.'*.*') as $v) {
					    unlink($v);
						}
					}
				}				
			} else {
				$project->save();
			}
			message(SUCCESS);
		}
	}
		