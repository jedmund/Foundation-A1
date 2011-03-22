<?php
	require_once('../../lib/init/config.php');
	
	// Make a web-safe filename.
	$exploded = explode('.', $_GET['qqfile']);
	$ext = array_pop($exploded);
	$file = to_filename(implode('.', $exploded)) . '.' . $ext;
	
	// The uploader looks for the index in the GET superglobal, so
	// we have to reset it with the cleaned filename.
	$_GET['qqfile'] = $file;

	// Accepts: JPG, JPEG, PNG, GIF
	// Max Filesize: 4MB
	$allowed_extensions = array("jpg", "jpeg", "png", "gif");
	$size_limit = 4 * 1024 * 1024;

	// Clean the project ID to prevent tampering. 
	$id = (!empty($_GET['pid']) && is_numeric($_GET['pid'])) ? $database->escape_value($_GET['pid']) : "";

	$path = "";
	if (!empty($id)) {
		$project = Project::find_by_id($id);
		
		// Get rid of the beginning slash in the project path.
		$path = substr($project->path, 1);
	} else {
		$path = 'content'.DS.'temp'.DS;
	}
	
	// Set an absolute path.
	$absolute = PUBLIC_PATH.DS.$path;
	
	$uploader = new qqFileUploader($allowed_extensions, $size_limit);
	if ($result = $uploader->handleUpload($absolute, TRUE) && empty($result['error'])) {
		// If the file is successfully uploaded, change the file's
		// permissions so we can continue to modify it in the future.
		chmod($absolute.$file, 0777);

		// Make a new Image object and populate its data.
		// First, we make absolute paths so that we can scale the image.
		$image = new Image();
		$image->pid = (!empty($id)) ? $id : 0;
		$image->full = $path . $file;
		
		// We only generate sizes if the project exists. Otherwise we'll 
		// delegate it to a background task.
		if (!empty($project)) {
			$image->generate_sizes();
		}
				
		// Then we make local paths to store in the database.
		if (!empty($project)) {
			$image->full = $project->path . $file;
		}

		$image->save();
		
		$image->id = $database->insert_id();
		
		// To pass data through iframe, all HTML tags must be encoded.
		$return = array("id"=>$image->id, "path"=>$image->full);
		echo htmlspecialchars(json_encode($return), ENT_NOQUOTES);
	} else {
		message(ERROR, "There was an error uploading your image. Please try again.");
	}