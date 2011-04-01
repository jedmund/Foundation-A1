<?php
	/**
	 * Parsel R2
	 *
	 * builder.php
	 * Builder builds content.
	 *
	 */
 
 	require_once("parsel.php");
 	
 	class Builder {
 		public $pointer; // The pointer in the current loop.
 										 // Nested loops shouldn't be common, but if they are, 
 										 // we can create a new Builder object to handle it.
 		public $current; // The current page.
 	
 		/**
		 * Builds inclusions.
		 *
		 * @param				$system				array			The system vars.
  	 * @param				$parts				array			The parts of the tag.
		 * @return										mixed			The result of the operation.
		 *
		 */
  	public function build_include($system, $parts) {
  		// Create a new Parsel object.
  		$parsel = new Parsel($system['layout'], $parts['object'], $system['obj_id'], false);

 			// Return the results.
			return $parsel->get_contents();
  	}
 	
 		/**
		 * Builds imports.
		 *
		 * @param				$system				array			The system vars.
  	 * @param				$parts				array			The parts of the tag.
		 * @return										mixed			The result of the operation.
		 *
		 */
  	public function build_import($system, $parts) {
 			$import = "";
 			
 			// Create the path for the file.
 			$path = DS."layout".DS.$system['layout'].DS;
 			
 			// If the file to be imported is on the internet, then we want to
 			// unset the path and filter group so that we create a proper path.
 			// We put the DS inside of filter group otherwise to prevent a 
 			// leading slash on the web-sourced files.
 			if ($parts['filter_group'] == "web" ||
 					$parts['filter_group'] == "internet") {
 				$path = "";
 				$parts['filter_group'] = "";
 			} else {
 				$parts['filter_group'] .= DS;
 			}
 			
 			if (!empty($parts['mode'])) {
 				// If we're importing a CSS document...
 				if ($parts['mode'] == "css") {
 					// Why do we only import the path if object is empty?
 					if (empty($parts['object'])) {
 						$import = '<link href="' . $path . '" rel="stylesheet" type="text/css">';
 					} else {
 						$import = '<link href="' . $path . $parts['filter_group'] . $parts['object'] . '" rel="stylesheet" type="text/css">';
 					}
 				// If we're important a Javascript document...
 				} else if ($parts['mode'] == "js" || $parts['mode'] == "javascript") {
 					if (empty($parts['object'])) {
 						$import = '<script src="' . $path . '" type="text/javascript"></script>';
 					} else {
 						$import = '<script src="' . $path . $parts['filter_group'] . $parts['object'] . '" type="text/javascript"></script>';
 					}
 				// If we're importing an image...
 				} else if ($parts['mode'] == "img" || $parts['mode'] == "image") {
 					if (empty($parts['object'])) {
 						$import = '<img src="' . $path . '">';
 					} else {
 						$import = '<img src="' . $path . $parts['filter_group'] . $parts['object'] . '">';
 					}
 				}
 			} else {
 				$import = $path . $parts['filter_group'] . $parts['object'];
 			}
 			
 			return $import;
  	}
	
		/**
		 * Builds parameters.
		 *
		 * @param				$notes				array			Notes from the Operator.
		 * @param				$system				array			The system vars.
		 * @param				$parts				array			The parts of the tag.
		 * @return										mixed			The result of the operation.
		 *
		 */
 		public function build_parameter($notes, $parts, $system) {
 			// Set the $parameter function for convenience.
 			$parameter = $parts['parameters'];
 			
 			// Initialize a result.
 			$result = "";
 			
 			// Check first to see if there is an index.
 			// If we don't, see if we have an object ID. 
 			// We can't get any parameter without one of the two.
 			if (!empty($parts['index'])) {
 				// If we're querying the Project object, then we know to use find_by_title().
 				if ($notes['class_name'] == "Project") {
	 				$object = call_user_func(array($notes['class_name'], "find_by_title"), $parts['index']);
	 			}
 			} else if (!empty($system['obj_id'])) {
 				// Get the parameter. If we are getting the parameter of a
 				// Project, also set it's Foundation and make it's thumb path.
 				$object = call_user_func(array($notes['class_name'], "find_by_id"), $system['obj_id']);
			}
			
			if (!empty($object)) {
	 			if (strtolower($notes['class_name']) == "project") {
	 				$object->set_foundation();
	 				$object->make_thumb_path();
	 			}
	 			
	 			if (!empty($object->$parameter)) {
	 				// Array of paragraph-based parameters.
	 				$pbased = array("description", "bio", "status", "blurb");
	 				if (in_array($parameter, $pbased)) {
		 				// If the parameter is one of the paragraph-based parameters,
		 				// offload to the build_longform() function.
		 				$result = $this->build_longform($object, $parts);
		 			} else if ($parameter == "photo" && $parts['object'] == "user") {
		 				// If the parameter is asking for a user's photo, create the
		 				// user, make a caption and attributes, and make the image.
		 				$user = User::find_by_id(10);
		 				$caption = "Photo of " . $user->first_name . " " . $user->last_name . ".";
		 				$attributes = array("class"=>"parsel_user_photo");
		 				
		 				$result = Marker::make_image($user->photo, $caption, $attributes);
		 			} else {
		 				// Otherwise, prepare the content for display in HTML.
	 					$result = htmlentities(stripslashes($object->$parameter));
	 				}
	 			// Not sure if this is necessary
 	 			# } else if ($parameter == "xlarge" || $parameter == "large" || $parameter == "medium") {
	 			#		$object->$parameter = $object->full;
		 		} else if ($parameter == "date") {
		 			// If the object is completed, then create the date.
					if (!empty($object->completed)) {
						$parts = explode("-", $object->completed);
						$year = (int)array_shift($parts);
						$month = month_from_date($object->completed);
						
						if (!empty($month) && !empty($year)) {
							$result = $month . " " . $year;
						} else if (!empty($month) && empty($year)) {
							$result = $month;
						} else if (empty($month) && !empty($year)) {
							$result = $year;
						} else {
							$result = "";
						}
					}
		 		} else if ($parameter == "permalink") {
	 				// If the parameter is asking for a project's permalink, call the function
	 				// that will create it for us.
	 				$result = $object->permalink();
				}
				
	 			if (!empty($object->$parameter) && is_array(json_decode($object->$parameter))) {
						// Check if the parameter is an array after passing 
						// through json_decode()
	 					$field = Field::find_by_name($param);
		 				$keys = json_decode($object->$parameter);
		 				$string = "";
		 				if (count($keys) == 0) {
		 					$string = "None available.";
		 				} else {
		 					// If the array isn't empty, then make a string from
		 					// the resulting structured data.
			 				for ($i = 0; $i < count($keys); $i++) {
			 					if (is_numeric($keys[$i])) {
			 						$info = $field->get_data_by_id($keys[$i]);
			 					}
			 					
			 					if ($i == 0) {
				 					$string .= $info['value'];
				 				} else if (count($keys > 1) && $i != 0) {
				 					$string .= ", " . $info['value'];
				 				}
			 				}
		 				}				
		 			$result = $string;
		 		}
			}
 			return $result;			
 		}
 		
 		/**
 		 * Convenience function that builds long-form text (paragraphs).
 		 * 
 		 * @param				$object				object		The object we're getting info from.
		 * @param				$parts				array			The parts of the tag.
 		 *
 		 */
 		public function build_longform($object, $parts) {
 			// For convenience, redefine the parameter var.
 			$parameter = $parts['parameters'];
 		
	 		// If the parameter is one of the paragraph-based parameters,
			// reformat the text and add classes.
			$class = "parsel_" . $parts['parameters'];
			$result = $this->reformat($object->$parameter, $class);
			
			// If there is a subset group, then we need to get the subset.
			if (!empty($parts['subset_group'])) {
				$function = "break_into_" . $parts['subset_group'];
				$result = $this->$function($result, $parts['subset_group'], $parts['subset']);
			}
			
			return $result;
 		}
 		
 		/**
 		 * Convenience function that breaks long-form text into paragraphs.
 		 *
		 * @param				$text					string		The text to filter.
		 * @param				$group				string		The subset group.
 		 * @param				$subset				string		The comma-separated subset.
 		 *
 		 */
 		public function break_into_paragraphs($text, $group, $subset) {
 			// Define the delimiters.
			$breaks = array("\n\n", "\n");
					 		
	 		// Explode the subset into an array.
			$subset = explode(",", $subset);
			
			// Define the system's break string and replace the organic
			// breaks with something the system can better understand.
			$parsel_break = "[parsel_break]";
			$text = str_replace($breaks, $parsel_break, $text);
			
			// Break the text apart at the system breaks and for each subset
			// value, get the corresponding part from the explosion.
			$fragments = "";
			if ($parts = explode($parsel_break, $text)) {
				foreach ($subset as $value) {
					if (array_key_exists($value-1, $parts)) {
						$fragments .= $parts[$value-1];
					}
				}
			}
			
			return $fragments;
 		} 
 		 
 		/**
 		 * Convenience function that breaks long-form text into sentences.
 		 *
		 * @param				$text					string		The text to filter.
		 * @param				$group				string		The subset group.
 		 * @param				$subset				string		The comma-separated subset.
 		 *
 		 */
 		public function break_into_sentences($text, $group, $subset) {
 			// Define the delimiters.
			$breaks = array(".  ", ". ", ".");
	 		
	 		// Explode the subset into an array.
			$subset = explode(",", $subset);
			
			// Define the system's break string and replace the organic
			// breaks with something the system can better understand.
			$parsel_break = "[parsel_break]";
			$text = str_replace($breaks, $parsel_break, $text);
			
			// Break the text apart at the system breaks and for each subset
			// value, get the corresponding part from the explosion.
			$fragments = "";
			if ($parts = explode($parsel_break, $text)) {
				foreach ($subset as $value) {
					// Add back the period at the end of each sentence and wrap in
					// a <span> tag.
					$content = $parts[$value-1] . ".";
					$attributes = array("class"=>"parsel_sentence");
					$html = Marker::make_span($content, $attributes);
					
					$fragments .= $html;
				}
			}
			
			return $fragments;
 		} 
 		 
		/**
		 * Convenience function that reformats long-form text (paragraphs) 
		 * into a Parsel-friendly format.
		 *
		 * @param				$text					string		The text to reformat.
		 * @param				$class				string		Optional additional classes to add.
		 * @return										string		The reformatted text.
		 *
		 */
		public function reformat($text, $class="") {
			// Characters to be replaced.
			$replace = array('\r', '\n', '%0a', '%0d');
			
			// Replace and reformat.
			// $reformatted = stripslashes(Markdown(str_replace($replace, "\n", $text)));
			$reformatted = Markdown(stripslashes($text));
			// Create paragraph with classes.
			$class .= " parsel_paragraph";
			$paragraph = '<p class="' . $class . '">';
			
			// Replace tags with new paragraph.
			$reformatted = str_replace('<p>', $paragraph, $reformatted);

			return $reformatted;
		}
 		
 		/** 
 		 * Builds images.
 		 * 
 		 * @param				$parts				array				The parts of the tag.
 		 * @param				$obj_id				int					The current object ID.
 		 * @param				$attributes		array				Attributes for the container.
 		 * @return										string			The resulting HTML.
 		 *
 		 */
 		public function build_images($parts, $obj_id, $attributes=array()) {
			// Find all the images for the project, then loop over them and 
			// fill in our items with the appropriate data.
			if (!empty($parts['subset'])) {
	 			$images = Image::find_in_subset($parts['subset'], $obj_id);
	 		} else {
		 		if (!empty($parts['filter_value'])) {
		 			if ($parts['filter_group'] == "tag") {
		 				$images = Image::find_by_tag($parts['filter_value'], $obj_id);
		 			}
	 			} else {
		 			$images = Image::find_by_pid($obj_id);
		 		}
	 		}
				
			// Loop over the images, building each as an image and adding it
			// to our items array.
			$items['tag'] 			 = "img";
			$items['children'] 	 = array();
			$items['attributes'] = array();
			
			// Make sure there is an image to loop over.
			if (!empty($images)) {
				// If we only have one image, we have to wrap it in an array
				// so that it can be looped over in the foreach()
				if (count($images) == 1) {
					$images = array($images);	
				}
				
				// Loop over each image and build the items.
				// !! REFACTOR THIS NOW
				foreach ($images as $image) {
					// Build the image.
					$item = $this->build_image($image, $parts);
					
					// Display the image in different ways depending on the mode.
					if ($parts['mode'] == "grid") {
						$item['attributes'] = array("class"=>"parsel_grid_item");
					} else if ($parts['mode'] == "mesh") {
						$item['attributes'] = array("class"=>"parsel_mesh_item");
					} else {
						// If the images are to be displayed as a list,
						// or no mode is specified.
						$item['attributes'] = array("class"=>"parsel_list_item");
					}
					
					if ($parts['object'] == "slideshow") {
						$item['attributes']['class'] = $item['attributes']['class'] . " parsel_slideshow_next";
					}
					if (is_array($parts['options']) && 
							(in_array("captions", $parts['options']) ||
							 in_array("caption", $parts['options']))) {
						// If we are adding captions, we should change the application
						// flow a bit.
						
						// Make the image and caption.
						$img = Marker::make_image($item['source'], $item['caption'], $item['attributes']);
						
						$caption = "";
						if (!in_array("slideshow", $parts['options'])) {
							$caption = Marker::make_paragraph($item['caption'], array("class"=>"parsel_caption"));
						}
						
						// Encapsulate in a div depending on the modifiers.
						$modifier = "after";
						foreach($parts['modifiers'] as $modifier) {
							if ($modifier['option'] == ("captions" || "caption")) {
								$modifier = $modifier['modifier'];
								break;
							}
						}
						
						if ($modifier == "after") {
							$item = $img . $caption;
						} else {
							$item = $caption . $img;
						}

						$items .= $item;
					} else {
						$items['children'][] = $item;
					}
				}
				// There is a problem with how we are doing this, so we get "Array"
				// at the beginning of the string. This fixes it for now. It is 
				// imperative that we refactor this in the next big update.
				if (is_string($items)) {
					$items = substr($items, 5);
				}
			}
			
			// Make the attributes for the container.
			if (empty($attributes)) {
				$attributes['class'] = "parsel_images";
			} else {
				$attributes['class'] .= " parsel_images";
			}
			
			$html = "";
			if (!empty($items['children'])) {
				$html = Marker::make_div($items, $attributes);
			}
 			
 			return $html;
 		}
 		
 		public function build_image($image, $parts) {
 			// If there is a single image, then it gives us the array in this
 			// step, so we should check and shift to prevent errors.
 			if (is_array($image)) {
 				$image = array_shift($image);
 			}
 		
 			// Prevent errors by initializing an empty array in the options
 			// if it is empty.
 			if (empty($parts['options'])) {
 				$parts['options'] = array();
 			}
 		
 			// Set the full index to the full size image.
 			// Set the caption index to the caption.
 			$data['full'] = $image->full;
 			
 			if (in_array("captions", $parts['options'])) {
				$data['caption'] = htmlentities(stripslashes($image->caption));
		 	} else {
		 		$data['caption'] = "";
		 	}
			
			// Get the requested size of the image, failsafing back to the 
			// full-sized image if the requested size does not exist.
			// The default size is medium.
			if ($size = $this->has_size($parts['options'])) {
				$data['source'] = $this->degrade($image, $size);
			}
			
			// Convert from an absolute path to relative so that the image can
			// be outputted.
			$pos = strpos($data['source'], "/content/");
			$data['source'] = substr($data['source'], $pos);

			// Depending on options, include the image's thumbnail and link.
 			if (!empty($parts['options']) && in_array("thumbnails", $parts['options'])) {
 				$data['thumb'] = $image->thumb;
 			}

 			if (!empty($parts['options']) && in_array("links", $parts['options'])) {
 				$data['link'] = $image->link;
 			}
			
 			return $data;
 		}
 		
 		/**
 		 * Helper function that degrades the image quality if the requested
 		 * size is not present.
 		 *
 		 * @param				$image			Image				The image object to analyze.
 		 * @param				$size				string			The size to check for.
 		 * @return									string			The available size.
 		 *
 		 */
 		public function degrade($image, $size) {
 			$sizes = array("full", "small", "medium", "large", "xlarge");
			$path = false;
			
			if (in_array($size, $sizes)) {
				if ($image->$size && $image->$size != "/") {
					$path = $image->$size;
				} else {
					$path = $image->full;
				}
			}
			
			return $path;
 		}
 		
 		/**
 		 * Checks whether or not size is declared as an option.
 		 *
 		 * @param			$options		array				The options from the tag.
 		 * @return								string			The first size found.
 		 * @return								boolean			False if nothing found.	
 		 *
 		 */
 		public function has_size($options) {
 			$sizes = array(1=>"full", 2=>"small", 3=>"medium", 4=>"large", 5=>"xlarge");

 			$has_size = "medium";
 			foreach ($sizes as $size) {
 				if ($pos = in_array($size, $options)) {
	 				if (!empty($sizes[$pos])) {
						$has_size = $size;
					}
				}
 			}
 			return $has_size;
 		}

 		public function build_social($param, $mode="", $uid=10) {
 			$user = User::find_by_id($uid);
 			$social = json_decode($user->social);
			
			$value = "";
			if (empty($mode)) {
	 			if (!empty($social->$param)) {
	 				$value = $social->$param;
	 			}
			} else if ($mode == 'widget') {
				if ($param == 'twitter') {
					$twitter = new Twitter("jedmund");
					$tweets = $twitter->get(3);
					$list = Marker::make_list("ul", $tweets, array("class"=>"parsel_twitter"), array("class"=>"parsel_tweet"));
				} else if ($param == 'dribbble') {
					$dribbble = new Dribbble("jedmund");
					$shots = $dribbble->get(1);
					$list = Marker::make_list("ul", $shots, array("class"=>"parsel_dribbble"), array("class"=>"parsel_shot"));
				}
				return $list;
			}
				 			
 			return $value;
 		}
 		
 	 	public function style($mode, $options) {
	 		$attributes = array();
	 		if ($mode == "list") {
	 			$attributes['class'] = "parsel_list";
	 		} else if ($mode == "grid") {
	 			$attributes['class'] = "parsel_grid";
	 		}
	 		
	 		if (!empty($options) && in_array("thumbnails", $options)) {
	 			$attributes['class'] .= " parsel_thumb";
	 		}
	 		
	 		return $attributes;
	 	}
	}