<?php
	require_once('../../lib/init/config.php');

	// This array handles content we need to return back to the front-end.
	// It should have a type, ERROR or SUCCESS, and supplementary text to
	// display in a modal dialog.
	$return = array();
	$return['type'] = ''; 
	$return['text'] = ''	;
	$return['fields'] = array();
	
	if (is_numeric($_POST['id'])) {
		$image = Image::find_by_id($database->escape_value($_POST['id']));
		$image->erase();
		
		$return['type'] = SUCCESS;
	}	else {
		$return['type'] = ERROR;
		$return['text'] = "Not a valid image ID";
	}
	
	echo json_encode($return);