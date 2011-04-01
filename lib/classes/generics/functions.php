<?php

	/**
	 * This function redirects the user to the specified path.
	 *
	 * @param				$path						The path to redirect to.
	 *
	 */
	function redirect_to($path) {
		header('Location: ' . $path);
	}
	
	/** 
	 * This function converts a given filename with invalid characters
	 * to a valid filename for the web.
	 *
	 * @param				$filename				The original filename.
	 * @return			$result					The new filename.
	 */
	function to_filename($filename) {
    $temp = $filename;
 
    // Lower case
    $temp = strtolower($temp);
 
    // Replace spaces with a '_'
    $temp = str_replace(" ", "_", $temp);
 
    // Loop through string
    $result = '';
    for ($i=0; $i<strlen($temp); $i++) {
        if (preg_match('([0-9]|[a-z]|_)', $temp[$i])) {
            $result = $result . $temp[$i];
        }    
    }
 
    // Return filename
    return $result;
	}
	
	/** 
	 * This function takes an array of required values and an array of 
	 * values and checks whether the required values appear in the fieldset
	 * and are not empty.
	 *
	 * @param				$required				The required fields.
	 * @param				$fields					The actual fieldset.
	 * @return			$missing				The missing fields.
	 *
	 */
	function required($required, $fields) {
		$missing = array();
		
		foreach ($required as $required_field) {
			// If the array key doesn't exist in our fieldset,
			// or the value is empty, add the key to our missing
			// fields array.
			if (!array_key_exists($required_field, $fields) ||
					empty($fields[$required_field])) {
				$missing[] = $required_field;
			}
		}
		
		return $missing;
	}
	
	/** 
	 * This function recursively removes a directory and all of the
	 * files inside. 
	 *
	 * Taken from the PHP.net documentation for rmdir()
	 *
	 * @param				$dir						The directory to remove.
	 *
	 */	
	function rrmdir($dir) { 
		if (is_dir($dir)) { 
			$objects = scandir($dir); 
			foreach ($objects as $object) { 
				if ($object != "." && $object != "..") { 
					if (filetype($dir."/".$object) == "dir") {
						rrmdir($dir."/".$object); 
					} else { 
						unlink($dir."/".$object);
					}
				} 
			} 
			reset($objects); 
			rmdir($dir); 
		} 
	}
	
	/**
	 * This function converts multiple new lines to <p> tags with an
	 * optional class.
	 *
	 * @param				$string				The string to convert.
	 * @param				$class				The class to attach to the <p> tags.
	 * @return			string				The converted string.
	 *
	 */
	function nl2p($content, $class='') {
		if (!empty($class)) {
			$separator = '</p><p class="' . $class . '">';
			$html = '<p class="' . $class . '">';
			$html .= str_replace("\n", $separator, $content);
			$html .= '</p>';
		} else {
			$html = '<p>' . str_replace("\n", "</p><p>", $content) . '</p>';
		}
		
		return $html;
	} 
	
	/** 
	 * This function takes an image file and creates the image in memory 
	 * using the appropriate function according to the image filetype.
	 *
	 * If the file provided does not have a supported filetype, we return
	 * false.
	 *
	 * @param				$file						The image file.
	 * @return			image						The image in memory.
	 *
	 */
	function imagecreatefromext($file) {
		$exploded = explode('.', $file);
		$ext = array_pop($exploded);
		
		switch($ext) {
			case "jpg":
				$image = imagecreatefromjpeg($file);
			break;
			
			case "jpeg":
				$image = imagecreatefromjpeg($file);
			break;
			
			case "png":
				$image = imagecreatefrompng($file);
			break;
			
			case "gif":
				$image = imagecreatefromgif($file);
			break;
			
			default: 
				$image = false;
			break;
		}
		imagealphablending($image, true);
		return $image;
	}

	function depluralize($string) {	
		if (substr($string, count($string)-2) == 's') {
			$string = substr($string, 0, count($string)-2);
		}
		
		return $string;
	}
	
	// This array handles content we need to return back to the front-end.
	// It should have a type, ERROR or SUCCESS, and supplementary text to
	// display in a modal dialog.
	$return = array();
	define("ERROR", 0);
	define("SUCCESS", 1);
	define("RELAY", 2);
	define("TEST", 3);

	function message($type, $text="", $fields=array()) {
		$return['type'] = $type;
		$return['text']  = $text;
		
		if (!empty($fields)) {
		
			for ($i = 0; $i < count($fields); $i++) {
				if ($i == 0) {
					$return['text'] .= "<em class='highlight'>" . $fields[$i] . "</em>";
				} else if ($i == count($fields)-1) {
					$return['text'] .= " and <em class='highlight'>" . $fields[$i] . "</em>.";
				} else {
				 	$return['text'] .= ", <em class='highlight'>" . $fields[$i] . "</em>";
				}
			}
		}
		
		echo json_encode($return);
	}
	
	// This function converts a month string to the corresponding int 
	// value.
	function month_to_digit($string) {
		$int = 0;
		
		switch($string) {
			case "January":		$int = 01; break;
			case "February":	$int = 02; break;
			case "March":			$int = 03; break;
			case "April":			$int = 04; break;
			case "May":				$int = 05; break;
			case "June":			$int = 06; break;
			case "July": 			$int = 07; break;
			case "August":		$int = 08; break;
			case "September":	$int = 09; break;
			case "October":		$int = 10; break;
			case "November":	$int = 11; break;
			case "December":	$int = 12; break;
			default:					$int = 00; break;
		}
		
		return $int;
	}
	
	// This function extracts a month string from a date string.
	function month_from_date($date) {
		$date = explode("-", $date);
		$month = $date[1];
		
		$string = '';
		switch($month) {
			case 01: $string = "January"; 	break;
			case 02: $string = "February"; 	break;
			case 03: $string = "March"; 		break;
			case 04: $string = "April";		  break;
			case 05: $string = "May"; 			break;
			case 06: $string = "June"; 			break;
			case 07: $string = "July"; 			break;
			case  8: $string = "August";	 	break;
			case  9: $string = "September"; break;
			case 10: $string = "October"; 	break; 
			case 11: $string = "November"; 	break;
			case 12: $string = "December"; 	break;
			default: $string = ""; 					break;
		}

		return ($string != '') ? $string : false;
	}
	
	// This function extracts a year int from a date string.
	function year_from_date($date) {
		$date = explode("-", $date);
		return $date[0];	
	}
	
	// This function formats an image for use in the system.
	function make_system_thumb($cx, $cy, $cx2, $cy2, $targ_w, $targ_h, $src, $path) {
		// Do the processing to make the internal thumbnail.
		// Set the system thumbnail width, height, and filename.
		$sys_w = 282;
		$sys_h = 130;
		
		$img_r = imagecreatefromext($src);
		$dst_r = imagecreatetruecolor($targ_w, $targ_h);
				
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
		imagepng($dst_r, $path); 
	}

	/**
	 *  This function implements all the strn*pos functions, which return the $nth occurrence of $needle
	 *  in $haystack, or false if it doesn't exist / when illegal parameters have been supplied.
	 *
	 *  @param  string  $haystack       the string to search in.
	 *  @param  MIXED   $needle         the string or the ASCII value of the character to search for.
	 *  @param  integer $nth            the number of the occurrence to look for.
	 *  @param  integer $offset         the position in $haystack to start looking for $needle.
	 *  @param  bool    $insensitive    should the function be case insensitive?
	 *  @param  bool    $reverse        should the function work its way backwards in the haystack?
	 *  @return MIXED   integer         either the position of the $nth occurrence of $needle in $haystack,
	 *               or boolean         false if it can't be found.
	 */
	function strnripos_generic($haystack, $needle, $nth, $offset, $insensitive, $reverse) {
	    //  If needle is not a string, it is converted to an integer and applied as the ordinal value of a character.
	    if (!is_string($needle)) {
	        $needle = chr((int) $needle);
	    }
	
	    //  Are the supplied values valid / reasonable?
	    $len = strlen( $needle );
	    if  (1 > $nth || 0 === $len) {
	        return false;
	    }
	
	    if ($insensitive) {
	        $haystack = strtolower($haystack);
	        $needle   = strtolower($needle);
	    }
	
	    if ($reverse) {
	        $haystack = strrev($haystack);
	        $needle   = strrev($needle);
	    }
	
	    //  $offset is incremented in the call to strpos, so make sure that the first
	    //  call starts at the right position by initially decreasing $offset by $len.
	    $offset -= $len;
	    do {
	        $offset = strpos($haystack, $needle, $offset + $len);
	    } while(--$nth && false !== $offset);
	
	    return false === $offset || ! $reverse ? $offset : strlen($haystack) - $offset;
	}
	
	/**
	 *  @see    strnripos_generic
	 */
	function strnpos($haystack, $needle, $nth, $offset = 0) {
	    return strnripos_generic( $haystack, $needle, $nth, $offset, false, false);
	}
	
	/**
	 *  @see    strnripos_generic
	 */
	function strnipos($haystack, $needle, $nth, $offset = 0) {
	    return strnripos_generic($haystack, $needle, $nth, $offset, true, false);
	}
	
	/**
	 *  @see    strnripos_generic
	 */
	function strnrpos($haystack, $needle, $nth, $offset = 0) {
	    return strnripos_generic($haystack, $needle, $nth, $offset, false, true);
	}
	
	/**
	 *  @see    strnripos_generic
	 */
	function strnripos($haystack, $needle, $nth, $offset = 0) {
	    return strnripos_generic($haystack, $needle, $nth, $offset, true, true);
	}

	function is_windows() {
		$windows = false;
		if (PHP_OS == 'WINNT' || PHP_OS == 'WIN32') {
		  $windows = true;
		}
		return $windows;
	}
	
	function exec_enabled() {
	  $disabled = explode(', ', ini_get('disable_functions'));
	  return !in_array('exec', $disabled);
	}

	function is_empty_dir($dir) { 
     return (($files = @scandir($dir)) && count($files) <= 2); 
	}
	
	function nicetime($date) {
    if (empty($date)) {
        return "No date provided";
    }
   
    date_default_timezone_set("US/Eastern");
    $periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths         = array("60","60","24","7","4.35","12","10");
   
    $now             = time();
    $unix_date       = strtotime($date);
   
       // check validity of date
    if(empty($unix_date)) {   
        return "Bad date";
    }

    // is it future date or past date
    if($now > $unix_date) {   
        $difference     = $now - $unix_date;
        $tense         = "ago";
       
    } else {
        $difference     = $unix_date - $now;
        $tense         = "from now";
    }
   
    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $difference /= $lengths[$j];
    }
   
    $difference = round($difference);
   
    if($difference != 1) {
        $periods[$j].= "s";
    }
   	if ($difference == 0) { return "just now."; }
    return "$difference $periods[$j] {$tense}";
	}
	
	function strpos_array($haystack, $needles) {
		if (is_array($needles)) {
			foreach ($needles as $str) {
				if (is_array($str)) {
					$pos = strpos_array($haystack, $str);
				} else {
					$pos = strpos($haystack, $str);
				}
				
				if ($pos !== FALSE) {
					return $pos;
				}
			}
		} else {
			return strpos($haystack, $needles);
		}
	}
	
	/**
	 * Strips slashes and replaces newlines.
	 *
	 * @param			$text			string			The string of text to clean.
	 * @return							string			The cleaned string.
	 */
	function clean($text) {
		$replace = array('\r', '\n', '%0a', '%0d');
		$newline = "\n";
		
		return stripslashes(str_replace($replace, $newline, $text));
	}