<?php
	require_once('../lib/init/config.php');

	// This array handles content we need to return back to the front-end.
	// It should have a type, ERROR or SUCCESS, and supplementary text to
	// display in a modal dialog.
	$return = array();
	$return['type'] = ''; 
	$return['text'] = ''	;
	$return['fields'] = array();
	
	// If we have our "mode" set, we can set up a switch.
	if ((!empty($_POST) && !empty($_POST['mode'])) || (!empty($_GET) && !empty($_GET['mode']))) {
		if (!empty($_POST['mode'])) {
			$mode = $_POST['mode'];
		} else {
			$mode = $_GET['mode'];
		}

		switch($mode) {
		// When we create a project, it will go through this case, which 
		// conditionally performs operations based on the stage of submission.
			case "create_project":
			// Add error handling later.
				if ($_POST['step'] == "information") {
					if (empty($_POST['title']) || empty($_POST['foundation']) || empty($_POST['desc'])) {
						// A project must have a title, foundation, and description.
						if (empty($_POST['title']) || $_POST['title'] == "Project Title") $return['fields'][] = "Title";
						if (empty($_POST['foundation'])) $return['fields'][] = "Foundation";
						if (empty($_POST['description'])) $return['fields'][] = "Description";

						$return['type'] = ERROR;
						$return['text']  = "You must add a Title, Foundation, and Description before continuing.<br><br>";
						$return['text'] .= "You're missing "; 
						for ($i = 0; $i < count($return['fields']); $i++) {
							if ($i == 0) {
								$return['text'] .= "<em class='highlight'>" . $return['fields'][$i] . "</em>";
							} else if ($i == count($return['fields'])-1) {
								$return['text'] .= " and <em class='highlight'>" . $return['fields'][$i] . "</em>.";
							} else {
							 	$return['text'] .= ", <em class='highlight'>" . $return['fields'][$i] . "</em>";
							}
						}
						
						// Can the echo back be saved until the end?
						echo json_encode($return);
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
						$project->path 						 = DS."content".DS.$user->username.DS.to_filename($project->title).DS;
						
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
							$return['type'] = ERROR;
							$return['value'] = "There was an error saving your project. Please try again.";
						} else {
							$pid = $project->id;
							
							if (!is_dir(PUBLIC_PATH.$project->path)) {
								if (!$project->create_folder()) {
									$return['type'] = ERROR;
									$return['value'] = "There was a problem creating the folder for this project.";
								}
							}

							if (!empty($_POST['thumbnail_value'])) {
								/*
								$temp = $database->escape_value($_POST['thumbnail_value']);
								$filetype = explode('.', $temp);
								$filetype = $filetype[count($filetype)-1];
								$filename = 'thumb.'.$filetype;

								$user = User::find_by_id($_SESSION['uid']);
								
								//echo PUBLIC_PATH.$temp."\n";
								//echo PUBLIC_PATH.$project->path.$filename."\n";
								
								if (rename(PUBLIC_PATH.$temp, PUBLIC_PATH.$project->path.$filename)) {
									//chmod(PUBLIC_PATH.$project->path.$filename, 0777);
									$project->thumb = $project->path.'thumb.'.$filetype;
									$project->save();
									
									$return['type'] = SUCCESS;
									$return['pid'] = $pid;
								}
								*/
							} else {
								$return['type'] = SUCCESS;
								$return['pid'] = $pid;
							}
						}
						echo json_encode($return);
					}
				} else if ($_POST['step'] == 'content') {
					// If the project ID isn't transmitted.
					if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
						$return['type'] = ERROR;
						$return['text'] .= "There was a problem saving your changes.<br>";
						$return['text'] .= "Please go to the Projects page and edit your project from there.";
					} else {
						$return['type'] = SUCCESS;
						$return['pid'] = $_POST['id'];					
					}
					
					echo json_encode($return);
				} else {
					// Throw error
				}
			break;
			
		// Delete Project
			case "delete_project":
				if (is_numeric($_POST['id'])) {
					$project = Project::find_by_id($database->escape_value($_POST['id']));
									
					// Recursively remove the directory and all of its contents.
					rrmdir(PUBLIC_PATH . $project->path);
					
					// Delete the project and alert the front-end of the result.
					$result = $project->delete();
					if ($result) {
						$return['type'] = SUCCESS;
					} else {
						$return['type'] = ERROR;
						$return['text'] .= "There was a problem deleting your projects.<br>";
						$return['text'] .= "Please return to the Projects page and try again.<br>";
					}
				}
			break;
			
		// Delete Content
			case "delete_content":
				if (is_numeric($_POST['id'])) {
					if ($_POST['type'] == "image") {		
						$image = Image::find_by_id($database->escape_value($_POST['id']));
						$image->delete();
						if ($image->full)  unlink($image->full);
						if ($image->med)   unlink($image->med);
						if ($image->sm)		 unlink($image->sm);
						if ($image->thumb) unlink($image->thumb);
					} else if ($_POST['type'] == "video") {
						// Remove video
					}
				}	else {
					// Error
				}
			break;
			
		// Copy thumbnail
			case "thumbnail":
				// list of valid extensions, ex. array("jpeg", "xml", "bmp")
				$allowed_extensions = array('jpeg', 'jpg', 'png', 'gif');
				// max file size in bytes
				$size_limit = 1 * 1024 * 1024;
				
				$uploader = new qqFileUploader($allowed_extensions, $size_limit);
				$result = $uploader->handleUpload(PUBLIC_PATH.DS.'content/temp/');
				// to pass data through iframe you will need to encode all html tags
				echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
				die();
			break;
			
		// User photo
			case "user_photo":
				// Get the user
				$user = User::find_by_id($_GET['id']);
				
				// Make a web-safe filename.
				$exploded = explode('.', $_GET['qqfile']);
				$ext = array_pop($exploded);
				$file = 'photo.' . $ext;
				
				// The uploader looks for the index in the GET superglobal, so
				// we have to reset it with the cleaned filename.
				$_GET['qqfile'] = $file;
				
				// list of valid extensions, ex. array("jpeg", "xml", "bmp")
				$allowed_extensions = array('jpeg', 'jpg', 'png', 'gif');
				// max file size in bytes
				$size_limit = 1 * 1024 * 1024;
				
				$uploader = new qqFileUploader($allowed_extensions, $size_limit);
				$result = $uploader->handleUpload(PUBLIC_PATH.DS.'content'.DS.$user->username.DS);
				
				// to pass data through iframe you will need to encode all html tags
				$result = json_encode(DS.'content'.DS.$user->username.DS.$file);
				echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
			break;

			
		// Add Photo
			case "add_photo":
				// Get the image sizes from settings.
				$setting = Setting::find_by_name('image_sm_width');
				$sizes['sm'] = $setting->get_value();
				
				$setting = Setting::find_by_name('image_md_width');
				$sizes['md'] = $setting->get_value();
				
				$setting = Setting::find_by_name('image_lg_width');
				$sizes['lg'] = $setting->get_value();
				
				$setting = Setting::find_by_name('image_xl_width');
				$sizes['xl'] = $setting->get_value();
			
				// Make a web-safe filename.
				$exploded = explode('.', $_GET['qqfile']);
				$ext = array_pop($exploded);
				$file = to_filename(implode('.', $exploded)) . '.' . $ext;
				
				// The uploader looks for the index in the GET superglobal, so
				// we have to reset it with the cleaned filename.
				$_GET['qqfile'] = $file;
				
				// Clean the project ID to prevent tampering. If it's valid, 
				// then continue.
				$id = (is_numeric($_GET['id'])) ? $database->escape_value($_GET['id']) : "";
				if (!empty($id)) {
					$project = Project::find_by_id($id);
					
					// Accepts: JPG, JPEG, PNG, GIF
					// Max Filesize: 4MB
					$allowed_extensions = array("jpg", "jpeg", "png", "gif");
					$size_limit = 4 * 1024 * 1024;
					
					// Create the path and an uploader object.
					$path =  PUBLIC_PATH.$project->path;
					$uploader = new qqFileUploader($allowed_extensions, $size_limit);
					
					if ($result = $uploader->handleUpload($path) && empty($result['error'])) {
						// If the file is successfully uploaded, change the file's
						// permissions so we can continue to modify it in the future.
						chmod($path . $file, 0777);
						
						// Make a new Image object and populate its data.
						// First, we make absolute paths so that we can scale the image.
						$image = new Image();
						$image->pid = $id;
						$image->full = $path . $file;
						
						list($width, $height) = getimagesize($image->full);
						
						if ($width > $sizes['sm']) {
							$image->small = $image->scale('sm', $sizes['sm']);
							$image->small = substr($image->small, strpos($image->small, '/content'));
						}
						
						if ($width > $sizes['md']) {
							$image->medium = $image->scale('md', $sizes['md']);
							$image->medium = substr($image->medium, strpos($image->medium, '/content'));
						}
						
						if ($width > $sizes['lg']) {
							$image->large = $image->scale('lg', $sizes['lg']);
							$image->large = substr($image->large, strpos($image->large, '/content'));
						}
						
						if ($width > $sizes['xl']) {
							$image->xlarge = $image->scale('xl', $sizes['xl']);
							$image->xlarge = substr($image->xlarge, strpos($image->xlarge, '/content'));
						}
						
						// Then we make local paths to store in the database.
						$image->full = $project->path . $file;

						$image->save();
						
						$image->id = $database->insert_id();
						
						// To pass data through iframe, all HTML tags must be encoded.
						$return = array("id"=>$image->id, "path"=>$image->small);
						echo htmlspecialchars(json_encode($return), ENT_NOQUOTES);
					} else {
						echo json_encode("There was a problem.");
					}
				}
			break;

		// Get Foundation Fields
			case "get_fields":
				$foundation = Foundation::find_by_name($database->escape_value($_POST['foundation']));
				$field_ids = $foundation->get_props();

				$fields = array();
				foreach ($field_ids as $field_id) {
					$field = Field::find_by_id($field_id);
					if ($field->type == "fstinput") {
						$field->getData();
					}
					
					$fields[] = $field;
				}
				
				echo json_encode($fields);	
			break;
			
		// Get Content for Project
		// !! Photos only for now
			case "get_content":
				if (!empty($_POST) && 
						!empty($_POST['id']) &&
						is_numeric($_POST['id'])) {
					$pid = $database->escape_value($_POST['id']);
					$images = Image::find_by_pid($pid);

					$smalls = array();
					if (!empty($images)) {
						foreach ($images as $image) {
							$smalls[] = array('id'=>$image->id, 'path'=>$image->small);
						}
					}
					echo json_encode($smalls);
				}
			break;
		
		// Crop thumbnail for project
			case "crop_thumbnail":
				$targ_w = 262;
				$targ_h = 122;
				$jpeg_quality = 100;
				
				$src = PUBLIC_PATH.$_POST['src'];
				list($width, $height) = getimagesize($src);

				$ratio = $width/$_POST['img_w'];

				$cx = $_POST['x'] * $ratio;
				$cy = $_POST['y'] * $ratio;
				$cw = $_POST['w'] * $ratio;
				$ch = $_POST['h'] * $ratio;
								
				$filename = explode('.', basename($src));
				$filename = $filename[0];
				$dest = str_replace(basename($src), $filename . "_th.jpg", $src);
				
				$img_r = imagecreatefromext($src);
				$dst_r = ImageCreateTrueColor($targ_w, $targ_h);
				
				imagecopyresampled($dst_r, $img_r, 0, 0, $cx, $cy, $targ_w, $targ_h, $cw, $ch);
				
				imagejpeg($dst_r, $dest, $jpeg_quality);
				
				$dest = substr($dest, strpos($dest, "/content"));
				echo json_encode($dest);
			break;
			
		// Reorder projects
			case "reorder_projects":
				$data = json_decode($_POST['projects']);
				foreach ($data as $id => $array) {
					if (is_numeric($id) && is_array($array)) {
						$project = Project::find_by_id($database->escape_value($id));
						
						echo $project->title . ": " . $array[0] . "\n";
						if ($array[0] == 'active') {
							$project->archived = false;
						} else if ($array[0] == 'archived') {
							$project->archived = true;
						}
						
						if (is_numeric($array[1])) {
							$project->sequence = $database->escape_value($array[1]);
						}
						
						$project->save();
					}
				}
			break;
			
		// Reorder content
			case "reorder_content":
				$data = json_decode($_POST['data']);
				print_r($data);
				foreach ($data as $id => $sequence) {
					if (is_numeric($id)) {
						$image = Image::find_by_id($database->escape_value($id));
												
						if (is_numeric($sequence)) {
							$image->sequence = $database->escape_value($sequence);
						}
						
						$image->save();
					}
				}
			break;
			
		// Get fields
			case "field_list":
				$foundation = Foundation::find_by_name($database->escape_value($_POST['foundation']));
				$indices = json_decode($foundation->props);

				$sql = "SELECT id, name, label, type FROM fields WHERE id NOT IN ("; 
				$sql .= implode(", ", $indices);
				$sql .= ")";		
				
				$fields = Field::find_by_sql($sql);
				echo json_encode($fields);
			break;
			
		// Save Image
			case "save_image":
				$image = Image::find_by_id($database->escape_value($_POST['id']));
				$image->caption = $database->escape_value($_POST['caption']);
				$image->link = $database->escape_value($_POST['link']);
				$image->coords = $database->escape_value($_POST['coords']);
				$image->make_thumbnail();
				
				// Crop the image.
				$image->save();
			break;

		}
	}