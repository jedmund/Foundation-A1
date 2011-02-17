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
		if (empty($_POST['id'])) {
			$project = new Project;
		} else {
			$project = Project::find_by_id($database->escape_value($_POST['id']));
			$project->set_foundation();
		}

		$foundation = Foundation::find_by_name($database->escape_value($_POST['foundation'])); 
		$user = User::find_by_id($_SESSION['uid']);
		
		$project->uid							 = $user->id;
		$project->fid 				 	 	 = $foundation->id;
		$project->title 			 		 = $database->escape_value($_POST['title']);
		$project->blurb 			 		 = $database->escape_value($_POST['blurb']);
		$project->slug						 = strtolower(str_replace(" ", "_", $project->title));
		
		// mysql_real_escape_string is breaking nl2br
		// addslashes seems to work better.
		$project->description			 = addslashes($_POST['desc']);
		$project->client					 = $database->escape_value($_POST['client']);
		$project->path 						 = "content".DS.$user->username.DS.to_filename($project->title).DS;
		
		$data = json_decode($_POST['fdata']);
		for ($i = 0; $i < count($data); $i++) {
			// Decode the object and make some convenience variables.
			//
			// $field is our generic Field object
			// $name is the name of the given field, for insertion into the project. 
			// $values holds the values of that field.
			$object = json_decode($data[$i]);
			$field = Field::find_by_name($object->field);
			$name = $object->field;
			$values = $object->value;
			
			// Prepare the standard input values for storage.
			if ($field->type == 'fstinput') {
				$vid = array();
				// Get the ID numbers of the structured data values so we can
				// create the JSON string to put in the database.
				for ($j = 0; $j < count($values); $j++) {
					$value = trim($values[$j]);
					if ($vdata = $field->get_data_by_val($value)) {
						$vid[] = $vdata['id'];
					} else {
						$vid[] = $field->add_data($value);
					}
				}
				$values = $vid;
				$project->$name = json_encode($values);
			} else {
				$project->$name = $values;
			}
		}
		
		
		
		// Check to see if we are properly saving the project.
		// If we aren't, throw an error. If we are, we may continue.
		if (!$project->save()) {
			message(ERROR, "There was an error saving your project.<br>Please try again.");
		} else {
			if (!is_dir(PUBLIC_PATH.$project->path)) {
				if (@!$project->create_folder()) {
					$notice = new Notice;
					$notice->type  = 1;
					$notice->text  = "There was a problem creating the folder for the project '" . $project->title . "'. ";
					$notice->text .= "Please check the permissions of the 'content' folder and try again.";
					$notice->save();
				}
			}
			message(SUCCESS);
		}
	}
		