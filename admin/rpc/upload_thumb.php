<?php
	require_once('../../lib/init/config.php');

	// If we get a project ID then we should get the project path.
	if (!empty($_GET['pid']) && is_numeric($_GET['pid'])) {
		$project = Project::find_by_id($database->escape_value($_GET['pid']));
		$path = $project->path;
	} else {
		$path = DS.'content'.DS.'temp'.DS;
	}
	
	// This array handles content we need to return back to the front-end.
	// It should have a type, ERROR or SUCCESS, and supplementary text to
	// display in a modal dialog.
	$return = array();
	$return['type'] = ''; 
	$return['text'] = ''	;
	$return['fields'] = array();

	// Switch to lowercase so that it doesn't break on uppercase files
	$_GET['qqfile'] = strtolower($_GET['qqfile']);

	// list of valid extensions, ex. array("jpeg", "xml", "bmp")
	$allowed_extensions = array('jpeg', 'jpg', 'png', 'gif');
	// max file size in bytes
	$size_limit = 4 * 1024 * 1024;
	
	// Get the file's extension
	$ext = array_pop(explode('.', $_GET['qqfile']));
	
	$uploader = new qqFileUploader($allowed_extensions, $size_limit);
	$result = $uploader->handleUpload(PUBLIC_PATH.$path, TRUE);
	
	if (rename(PUBLIC_PATH.$path.$_GET['qqfile'], PUBLIC_PATH.$path.'thumb.'.$ext)) {
		$setting = Setting::find_by_name('thumbnail_width');
		$targ_w = $setting->get_value();
			
		$setting = Setting::find_by_name('thumbnail_height');
		$targ_h = $setting->get_value();
		
		$src  = PUBLIC_PATH.$path.'thumb.'.$ext;
		$dest = PUBLIC_PATH.$path.'system_thumb.png';
	
		make_system_thumb(0, 0, $targ_w, $targ_h, $targ_w, $targ_h, $src, $dest);
		
		$result['path'] = $path.'thumb.'.$ext;
				
		// to pass data through iframe you will need to encode all html tags
		echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
	}
	
	die();
	
	