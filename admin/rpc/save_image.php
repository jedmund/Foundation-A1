<?php
	require_once('../../lib/init/config.php');
	
	$image = Image::find_by_id($database->escape_value($_POST['id']));
	$image->caption = $database->escape_value($_POST['caption']);
	//$image->link = $database->escape_value($_POST['link']);
	// $image->coords = $database->escape_value($_POST['coords']);
	// $image->make_thumbnail();
	
	// Crop the image.
	$image->save();

?>