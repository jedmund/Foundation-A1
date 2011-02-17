<?php
	require_once('../../lib/init/config.php');

	// This array handles content we need to return back to the front-end.
	// It should have a type, ERROR or SUCCESS, and supplementary text to
	// display in a modal dialog.
	$return = array();
	$return['type'] = ''; 
	$return['text'] = ''	;
	$return['fields'] = array();

	$targ_w = $_POST['cw'];
	$targ_h = $_POST['ch'];
	$jpeg_quality = 100;
	
	$src = PUBLIC_PATH.$_POST['src'];
	list($width, $height) = getimagesize($src);


	//echo "(" . $_POST['x'] . "), (" . $_POST['y'] . "), ow,oh: (" . $_POST['w'] . ", " . $_POST['h'] . "), tw, th: (" . $targ_w . ", " . $targ_h . ") ratio: " . $ratio . "\n";	
	
	$cx = $_POST['x'];
	$cy = $_POST['y'];
	$cw = $_POST['w'];
	$ch = $_POST['h'];
	
	$cx2 = $cx + $cw;
	$cy2 = $cy + $ch;
	
	//echo "(" . $cx . "->" . $cx2 . "), (" . $cy . "->" . $cy2 . "), cw,ch: (" . $cw . ", " . $ch . "), tw, th: (" . $targ_w . ", " . $targ_h . ") ratio: " . $ratio . "\n";
	
	//echo $width . "/" . $targ_w . "=" . $ratio;

	$filename = explode('.', basename($src));
	$filename = $filename[0];
	$dest = str_replace(basename($src), $filename . ".png", $src);
	
	$img_r = imagecreatefromext($src);
	$dst_r = imagecreatetruecolor($targ_w, $targ_h);
	
	imagecopyresampled($dst_r, $img_r, 0, 0, $cx, $cy, $targ_w, $targ_h, $cw, $ch);
	
	imagepng($dst_r, $dest);
	
	$dest = substr($dest, strpos($dest, "/content"));
	echo json_encode($dest);
	
	// Do the processing to make the internal thumbnail.
	// Set the system thumbnail width, height, and filename.
	$sys_w = 282;
	$sys_h = 130;
	$filename = "system_thumb.png";
	$dest = PUBLIC_PATH.DS.'content'.DS.'temp'.DS.$filename;
	
	if ($targ_w > $sys_w || $targ_h > $sys_h) {
		// If the user-defined thumbnail dimensions are larger than the
		// system's hard-set dimensions, then we automatically crop the
		// thumbnail to our size.
		
		// Take the delta x and y coordinates by subtracting
		// the user-defined dimensions (larger) from the system's 
		// dimensions (small).
		$dx = $targ_w - $sys_w;
		$dy = $targ_h - $sys_h;
		
		// We then create new (x->x2) coordinates by adding half of
		// the delta to the origin (x), and subtracting half of the 
		// delta from the endpoint (x2).
		$nx  = $cx  + ($dx/2);
		$nx2 = $cx2 - ($dx/2); 
		
		// Repeat on the y axis.
		$ny  = $cy  + ($dy/2);
		$ny2 = $cy2 - ($dy/2);
		
		// We can calculate the new (scaled) width and height by 
		// subtracting origin (n) from endpoint (n2).
		$nw = $nx2 - $nx;
		$nh = $ny2 - $ny;

	} else {
		// This is the reverse operation as the previous conditional 
		// statement. Wherever we add, we subtract, and we flip
		// order in the initial statement to get accurate deltas.
		$dx = $sys_w - $targ_w;
		$dy = $sys_h - $targ_h;	
		
		$nx  = $cx  - ($dx/2);
		$nx2 = $cx2 + ($dx/2); 
		
		$ny  = $cy  - ($dy/2);
		$ny2 = $cy2 + ($dy/2);
		
		// We can calculate the new (scaled) width and height by 
		// subtracting origin (n) from endpoint (n2).
		$nw = $nx2 - $nx;
		$nh = $ny2 - $ny;
	}
	
	// This visual representation of the numbers for both the
	// original crop and system crop should help out if you
	// get into a pinch.
	/*
	 * echo "\n";
	 * echo "(" . $cx . "->" . $cx2 . "), (" . $cy . "->" . $cy2 . "), (" . $cw . ", " . $ch . ")\n";
	 * echo "(" . $nx . "->" . $nx2 . "), (" . $ny . "->" . $ny2 . "), (" . $nw . ", " . $nh . ")\n";
	 *
	 */
	
	$dst_r = imagecreatetruecolor($sys_w, $sys_h);
	
	imagecopyresampled($dst_r, $img_r, 0, 0, $nx, $ny, $sys_w, $sys_h, $nw, $nh);
	imagepng($dst_r, $dest); 
