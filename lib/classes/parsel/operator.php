<?php
	/**
	 * Parsel A1
	 * build 110
	 *
	 * operator.php
	 * Operator performs operations in tags. 
	 * Another way to think about it is routing calls in tags by operation.
	 *
	 */
	 
	class Operator {
		// The set of social objects used.
		private static $social  		= array("facebook" , "twitter"	 , "youtube",	  
																			  "vimeo"	  , "github"	 , "dribbble", 
																			  "tumblr"	  , "posterous", "lastfm",
																			  "linkedin" , "gowalla"	 , "foursquare",
																		    "email"	  , "skype"		 , "spotify",	  
																		    "zootool"  ,	"flickr"	 , "ffffound", 
																		    "delicious", "pinboard" , "visually");

		/**
		 * This function executes the `include` operation on the object.
		 *
		 * @param				$system				array			The system vars.
  	 * @param				$parts				array			The parts of the tag.
		 * @return										mixed			The result of the operation.
		 *
		 */
  	public static function pinclude($system, $parts) {
  		$builder = new Builder();
  		return $builder->build_include($system, $parts);
  	}
  	
		/**
		 * This function executes the `import` operation on the object.
		 *
		 * @param				$system				array			The system vars.
  	 * @param				$parts				array			The parts of the tag.
		 * @return										mixed			The result of the operation.
		 *
		 */
  	public static function pimport($system, $parts) {
  		$builder = new Builder();
 			return $builder->build_import($system, $parts);
   	}
  	
  	/**
		 * This function executes the `show` operation on the object.
		 *
		 * @param				$system				array			The system vars.
  	 * @param				$parts				array			The parts of the tag.
		 * @return										mixed			The result of the operation.
		 *
		 */
  	public static function pshow($system, $parts) {
  		// Initialize the $contents var.
 			$contents = "";
 			
			// Create a Builder object.
  		$builder = new Builder();
  		
  		// Get the class name (singular) and function name (plural) from 
	 		// the object map.
	 		$object_map = Parsel::get_object_map();
			if (array_key_exists($parts['object'], $object_map)) {
				$notes['function_obj'] = $object_map[$parts['object']];
				$notes['class_name'] = ucwords($parts['object']);
			} else if (in_array($parts['object'], $object_map)) {
				$notes['function_obj'] = $parts['object'];
				$notes['class_name'] = ucwords(array_search($parts['object'], $object_map));
			}
	 			
			// Offload execution to helper functions depending on what object
			// we are operating on.
			if ($parts['object'] == "site") {
				$contents = self::show_site($builder, $notes, $system, $parts);
			} else if ($parts['object'] == "user") {
				$contents = self::show_user($builder, $notes, $system, $parts);
			} else {
				$contents = self::show_generic($builder, $notes, $system, $parts);
			}
			
			return $contents;
		}
		
  	/**
  	 * Helper function that executes the `show` operation on non-pages.
  	 *
		 * @param				$builder			Builder		The Builder object.
		 * @param				$notes				array			Notes from the Operator.
		 * @param				$system				array			The system vars.
  	 * @param				$parts				array			The parts of the tag.
		 * @return										mixed			The result of the operation.
  	 *
  	 */
  	public static function show_generic($builder, $notes, $system, $parts) {			
			// Make the function call name.
			$function = "build_" . $notes['function_obj'];
  	
  		if (empty($parts['parameters'])) {
 	  		$contents = $builder->{$function}($parts, $system['obj_id']);
  		} else {
				$contents = $builder->build_parameter($notes, $parts, $system);
  		}
  		
  		return $contents;
  	}
  	
  	/**
  	 * Helper function that executes the `show` operation on users.
  	 *
		 * @param				$builder			Builder		The Builder object.
		 * @param				$notes				array			Notes from the Operator.
		 * @param				$system				array			The system vars.
  	 * @param				$parts				array			The parts of the tag.
		 * @return										mixed			The result of the operation.
  	 *
  	 */
  	public static function show_user($builder, $notes, $system, $parts) {
 			if ($parts['parameters'] == "name") {
 				// If the parameter is asking for the user's name, create the
 				// full name from the parts.
 				$user = User::find_by_id(10);
 				$contents = $user->first_name . " "  . $user->last_name;
 			} else if ($parts['parameters'] == "email") {
 				// If the parameter is asking for the user's email, then use the
 				// special accessor for email.
 				$user = User::find_by_id(10);
 				$contents = $user->get_email();
 			} else if (in_array($parts['parameters'], self::$social)) {
 				// If the parameter is inside the array of supported social services,
 				// route that request to the Builder's special social() function.
	 			$contents = $builder->build_social($parts['parameters'], $parts['mode'], 10);
 			} else {
 				// Otherwise, use the normal parameter function.
 				$system['obj_id'] = 10;
		 		$contents = $builder->build_parameter($notes, $parts, $system);
		 	}
		 	
		 	return $contents;
		}

		/**
  	 * Helper function that executes the `show` operation on pages.
		 *
		 * @param				$builder			Builder		The Builder object.
		 * @param				$notes				array			Notes from the Operator.
		 * @param				$system				array			The system vars.
  	 * @param				$parts				array			The parts of the tag.
		 * @return										mixed			The result of the operation.
  	 *
  	 */	
  	public static function show_site($builder, $notes, $system, $parts) {
	  	$contents = "";
	  	if (!empty($parts['parameters'])) {
	  		if ($parts['parameters'] == "pagename") {
 					// If we have an object ID and the system request URI is 
 					// project.htm, then we are on a project page. We should get 
 					// the project's ID and create the page's name.
 					if (!empty($system['obj_id']) && 
 							strpos($system['request_uri'], "projects/") &&
 							strlen($system['request_uri']) > strlen("projects/")) {
 						// If we have an object ID, and we're sure that the request URI
 						// indicates that we are on a project page, then we return the 
 						// project's title.
 						$project = Project::find_by_id($system['obj_id']);
 						$contents = $project->title;
 					} else if (empty($system['obj_id']) &&
 										 strpos($system['request_uri'], "projects") &&
 										 strlen($system['request_uri'] <= strlen("projects/"))) {
 						// If we don't have an object ID, and we're sure that the 
 						// request URI indicates that we are on the page for all  
 						// projects, then we return the a string.
 						$contents = "Projects";
 					} else if (strpos($system['request_uri'], "about")) {
 						// If we don't have an object ID, and we're sure that the 
 						// request URI indicates that we are on the about page, then we 
 						// return the a string. 					
 						$contents = "About";
 					}
 				} else if ($parts['parameters'] == "about") {
 					$contents = "/about";
 				} else if ($parts['parameters'] == "admin") {
		 			$contents = "Built on <a href='http://getfoundation.com/'>Foundation</a> &bull; <a href='/admin'>admin</a>";
 				} else {
 					$contents = false;
 					if ($setting = Setting::find_by_name($parts['parameters'])) {
	 					$contents = $setting->get_value();
	 				}
 				}
 			}
  		
  		return $contents;
  	}
  	
  	/**
		 * This function executes the `generate` operation on the object.
		 *
		 * @param				$system				array			The system vars.
  	 * @param				$parts				array			The parts of the tag.
		 * @return										mixed			The result of the operation.
		 *
		 */
  	public static function pgenerate($system, $parts) {
			// Create a Builder object.
  		$generator = new Generator();
  	
  		// Get the class name (singular) and function name (plural) from 
	 		// the object map.
	 		$object_map = Parsel::get_object_map();
			if (array_key_exists($parts['object'], $object_map)) {
				$notes['function_obj'] = $object_map[$parts['object']];
				$notes['class_name'] = ucwords($parts['object']);
			} else if (in_array($parts['object'], $object_map)) {
				$notes['function_obj'] = $parts['object'];
				$notes['class_name'] = ucwords(array_search($parts['object'], $object_map));
			}
			
			// Make the function call name.
			$function = "generate_" . $parts['object'];
  	
  		$contents = $generator->{$function}($parts, $system);
  		
  		return $contents;
  	}
  }
