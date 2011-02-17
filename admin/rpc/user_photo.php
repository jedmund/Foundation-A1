<?php
	require_once('../../lib/init/config.php');

	// This array handles content we need to return back to the front-end.
	// It should have a type, ERROR or SUCCESS, and supplementary text to
	// display in a modal dialog.
	$return = array();
	$return['type'] = ''; 
	$return['text'] = ''	;
	$return['fields'] = array();

	// Get the user
	$user = User::find_by_id($_GET['pid']);
	
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
	$result = $uploader->handleUpload(PUBLIC_PATH.DS.'content'.DS.$user->username.DS, TRUE);
	chmod(PUBLIC_PATH.DS.'content'.DS.$user->username.DS.$file, 0777);
	
	$user->photo = DS.'content'.DS.$user->username.DS.$file;
	$user->save();
	
	// to pass data through iframe you will need to encode all html tags
	$result = json_encode(DS.'content'.DS.$user->username.DS.$file);
	echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
