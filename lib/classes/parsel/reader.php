<?php
	/**
	 * Parsel R2
	 *
	 * reader.php
	 * Reader reads tags and separates them into their component parts.
	 *
	 */
	 
	 // Require the Parsel master file.
	 require_once('parsel.php');
	 
	 class Reader {
		// The default operation applied to a tag if no recognized 
 		// operation is set.
 		private static $default_operation = "show";
 		
 		// Throwaway words are words that the system does not use when
 		// parsing a tag, but may be useful to the end user to form and
 		// remember tags as coherent English sentences.
 		private static $throwaways 		= array(" a ", " an ", " the ");
 		
 		// The set of Parsel-recognized operations.
 		private static $operations 		= array("show", "generate", "include", "import");
 		
 		// The set of Parsel-recognized subset groups.
 		private static $subset_groups = array("paragraphs", "sentences");
 																		 		   
		// The set of social objects used.
		private $social  							= array("facebook", "twitter", 
														 				  		"youtube",	 "vimeo",
														 			 	 			"github", 	 "dribbble",
																		  		"tumblr",   "posterous", 
																		  		"lastfm", 	 "linkedin",
																		  		"gowalla",  "foursquare",
																		  		"email",		 "skype",
																		  		"spotify",	 "zootool",
																		 			"flickr",   "ffffound");
 		
 		
 	  /** 
 		 * Convenience function that tests whether or not a tag is valid.
 		 *
 		 * @param			$tag			The tag to test.
 		 * @return		boolean		True if it is a valid tag.
 		 *
 		 */
 		public static function is_valid($tag) {
 			$valid = false;
 			$len = strlen($tag);
 			
 			if (substr($tag, 0, 1) == "{" &&
 					substr($tag, $len-1, 1) == "}") {
 						$object = self::get_object(self::sanitize($tag), false);
 						if (self::is_object($object) ||
 								substr_count($object, "\"") == 2 ||
 								substr_count($object, "'")  == 2) {
	 						$valid = true;
	 					}
 					}
			return $valid;
 		}
 		
 		/**
		 * Strips the opening and ending characters from the given tag.
		 *
		 * @param				$tag				The tag to sanitize.
		 * @return 			string			The sanitized tag.
		 *
		 */
 		public static function sanitize($tag) {
 			$tag = str_replace(array("{","}"), "", $tag);
 			$tag = str_replace(self::$throwaways, " ", $tag);
 			return $tag;
 		}

		/**
 		 * Extracts the object from the tag.
 		 *
 		 * @param				$tag					The tag to extract from.
 		 * @param				$stripquotes	Option to strip quotations. 
 		 															Default: true.
 		 * @return			string				The object from the tag.
 		 *
 		 */
 		public static function get_object($tag, $stripquotes=true) {
 			$tag = self::sanitize($tag);
			$object = false;
 			// If the tag is an operation, find the object inside the phrase.
 			// Otherwise, explode the tag at the parameter delimiter.
 			if ($words = self::is_operation($tag)) {
 				$object = $words[1];
 				
 				if (substr($object, 0, 1) == "\"") {
 					if ($stripquotes) {
	 					$object = trim(str_replace("\"", "", $object));
	 				}
 				} else {
 					if (strpos($object, "'s")) {
		 				$object = trim(array_shift(explode("'s", $object)));
		 			} 
		 			
		 			if (strpos($object, ":")) {
		 				$object = trim(array_shift(explode(":", $object)));
		 			}
	 			}
 			} else {
 				if (substr($tag, 0, 1) == "\"") {
	 				if ($stripquotes) {
	 					$object = trim(str_replace("\"", "", $tag));
	 				}
 				} else {
	  			if (strpos($tag, "'s")) {
		 				$object = trim(array_shift(explode("'s", $tag)));
		 			} 
		 			
		 			if (strpos($tag, ":")) {
		 				$object = trim(array_shift(explode(":", $tag)));
		 			}
	  		}
 			}

			// Return the trimmed result.
			return trim($object);
 		}
 		
 		/** 
 		 * Extracts the operation from the tag.
 		 * If there isn't an explicit operation, we use the
 		 * defined default operation.
 		 *
 		 * @param				$tag			The tag to extract from.
 		 * @return 			string		The operation from the tag.
 		 *
 		 */
 		public static function get_operation($tag) {
 			$tag = self::sanitize($tag);
 			// Find the operation inside the phrase.
 			// Otherwise, return the default operation.
 			if ($words = self::is_operation($tag)) {
 				$operation = $words[0];
 			} else {
  			$operation = self::$default_operation;
 			}
 			

			// Return the trimmed result.
			return trim($operation);
 		}
		 		
  	/**
		 * Tests whether the given object is valid.
		 *
		 * @param				$object			The object to test.
		 * @return 			boolean			Returns true if the object is valid. 
		 *
		 */
		public static function is_object($object) {
			$valid = false;
			$object_map = Parsel::get_object_map();
			if (in_array($object, $object_map) || array_key_exists($object, $object_map)) {
				$valid = true;
			}
			return $valid;
		}

		/**
		 * Tests whether the given tag has an explicit operation.
		 *
		 * @param				$tag				The tag to test.
		 * @return 			array				An array with every word as an index.
		 														Returns false if it is not an operation.
		 *
		 */
 		public static function is_operation($tag) {
 			$operation = false;
 			$words = explode(' ', trim($tag));
 			$word = (count($words) > 1) ? $words[0] : false;
 			if (in_array($word, self::$operations)) {
 				$operation = $words;
 			}
 			return $operation;
 		}

		/**
 		 * Extracts the mode from the tag, or false if there is no mode.
 		 *
 		 * @param				$tag				The tag to extract from.
 		 * @return 			string			The mode from the tag.
 		 *
 		 */
 		public static function get_mode($tag) {
 			$tag = self::sanitize($tag);
 			
 			$object = self::get_object($tag);
 			$params = self::get_parameters($tag);
 			
			$delimiter = "as";
			
 			// If the tag has a mode, find it inside the phrase.
 			// Otherwise, return false, because there is no mode.
 			if ($words = explode(' ', trim($tag))) {
 				if (in_array($delimiter, $words)) {
	 				$key = array_search($delimiter, $words);
	 				$mode = $words[$key+1];
	 			} else {
	 				if (is_array($params) &&
	 					 (($object == "images" || 
	 					 	 $object == "image" || 
	 					 	 in_array("image", $params) || 
	 					 	 in_array("images", $params))) &&
	 						self::is_chain($params)) {
	 					$mode = "medium";
	 				} else if (($object == "images" || $object == "image") && empty($params)) {
	 					$mode = "list";
	 				} else {
		 				$mode = false;
		 			}
	 			}
 			} else {
 				$mode = false;
 			}

			// Return the trimmed result.
 			return trim($mode);
 		}
 		
		/** 
 		 * Extracts the index from the tag.
 		 *
 		 * @param				$tag				The tag to extract from.
 		 * @return			string			The index from the tag.
 		 *
 		 */
 		public static function get_index($tag) {
			$tag = self::sanitize($tag);
 			$delimiter = ":";
 			$params = array();
 			
			// Get rid of any options or modes in the tag.
 			if (strpos($tag, " as ")) {
 				$tag = array_shift(explode("as", $tag));
 			}

 			if (strpos($tag, " with ")) {
 				$tag = array_shift(explode("with", $tag));
 			}

 			
 			$index = false;	
 			// Split the tag into parts at the delimiter.
 			if ($parts = explode($delimiter, $tag)) {
 				// Make sure there are enough parts.
 				if (!empty($parts[1])) {
 					// Check and see if the first character is a quotation mark.
 					if (substr($parts[1], 0, 1) == "\"" ||
 							substr($parts[1], 0, 1) == "'") {
	 					// If the first character of the second part is a double or
		 				// single quotation mark, find the ending mark.
		 				if (substr($parts[1], 0, 1) == "\"" &&
		 						substr_count($parts[1], "\"") > 1) {
		 					$start = 0;
		 					$end = strnpos($parts[1], "\"", 2);
		 				} else if (substr($parts[1], 0, 1) == "'" &&
		 									 substr_count($parts[1], "'") > 1) {
		 					$start = 0;
		 					$end = strnpos($parts[1], "'", 2);
		 				}
		 				
			 			// Get the encapsulated string.
		 				$index = substr($parts[1], $start+1, ($end-$start-1));
			 		} else {
		 				// If there isn't quotation marks, then we'll split the second
		 				// part into words and get the first word, and replace any attached
		 				// particles.
		 				if ($words = explode(" ", $parts[1])) {
		 					$index = str_replace("'s", "", $words[0]);
		 				}
		 			}
		 		}
		 	}
 			// Return the trimmed result.
 			return trim($index);
 		}
 		
 		/**
 		 * Extracts the parameters from the tag, or false if there are no 
 		 * options.
 		 * 
 		 * @param				$tag				The tag to extract from.
 		 * @return			string			The parameter from the tag.				
 		 *
 		 */
 		public static function get_parameters($tag) {
 			$tag = self::sanitize($tag);
 			$delimiter = "'s";
 			$params = array();
 			
 			// Get rid of any options or modes in the tag.
 			if (strpos($tag, " as ")) {
 				$tag = array_shift(explode(" as ", $tag));
 			}
 			
 			if (strpos($tag, " with ")) {
 				$tag = array_shift(explode(" with ", $tag));
 			}
 			 			
 			if ($subset_group = self::get_subset_group($tag)) {
 				$tag = array_shift(explode($subset_group, $tag));
 			}
 			
 			if (strpos($tag, "\"")) {
 				$start = strpos($tag, "\"");
 				$end = strrpos($tag, "\"");
 				$quotes = substr($tag, $start, ($end-$start+1));
 				$tag = str_replace($quotes, "", $tag);
 			}
 			
 			// If the tag has a parameter, find it inside the phrase.
 			// Otherwise, return false, because there is no parameter.
 			if (strpos($tag, $delimiter)) {
 				$parts = explode($delimiter, $tag);
				foreach ($parts as $part) {
					$params[] = trim($part);
				}
				$params = array_slice($params, 1);
 			} else {
 				$params = false;
 			}
 			
 			// Return the trimmed result.
 			return (count($params) == 1 && !empty($params)) ? array_shift($params) : $params;
 		}

 		/**
 		 * Extracts the options from the tag, or false if there are no 
 		 * options.
 		 * 
 		 * @param				$tag				The tag to extract from.
 		 * @return			array				The options from the tag.				
 		 *
 		 */
 		public static function get_options($tag) {
 			$tag = self::sanitize($tag);
			$delimiter = "with";
			$separator = array("and");
			$filter = "from";
			
 			// If the tag has options, find them inside the phrase.
 			// Otherwise, return false, because there is no mode.
 			if ($words = self::is_operation($tag)) {
 				if (in_array($delimiter, $words)) {
	 				$key = array_search($delimiter, $words);
	 				
	 				// Split the array at the key.
	 				$options = array_slice($words, $key+1);

	 				// Find the filter keyword and slice there.
	 				if ($filter_key = array_search($filter, $options)) {
		 				$options = array_slice($options, 0, $filter_key);
		 			}

	 				// If the split array has more than one index, then we need to
	 				// grab every other key.
	 				$keys = array();
	 				if (count($options) > 1) {
		 				for ($i = 0; $i < count($options); $i+=2) {
		 					$keys[] = trim($options[$i]);
		 				}
		 				
		 				$options = $keys;
	 				} 
	 			} else {
	 				$options = false;
	 			}
 			} else {
 				$options = false;
 			}
 			
			// Return the trimmed result.
 			return $options;
 		}

		/**
		 * Extracts the filter group from the tag.
		 *
		 * @param 			$tag				The tag to extract from.
		 * @return			string			The filter group from the tag.
		 *
		 */
		public static function get_filter_group($tag) {
			$tag = self::sanitize($tag);
			$delimiter = "from";
			
			$filter_group = false;
			if ($words = explode(" ", $tag)) {
				if ($key = array_search($delimiter, $words)) {
					$filter_group = trim($words[$key+1]);
				}
			}
			
			return $filter_group;
		}
		
		/**
		 * Extracts the filter group from the tag.
		 *
		 * @param 			$tag				The tag to extract from.
		 * @return			string			The filter group from the tag.
		 *
		 */
		public static function get_filter_value($tag) {
			$tag = self::sanitize($tag);
			$filter_group = self::get_filter_group($tag);
			$filter_value = false;

			if (!empty($filter_group) && $words = explode($filter_group, $tag)) {
				if ($pos = strpos($words[1], "\"")) {
					$start = $pos+1;
					$end = strnpos($words[1], "\"", 2);
					$filter_value = substr($words[1], $start, ($end-$start));
				} else if ($pos = strpos($words[1], "'")) {
					// Optimize this, we're unnecessarily repeating code.
					$start = $pos+1;
					$end = strnpos($words[1], "'", 2);
					$filter_value = substr($words[1], $start, ($end-$start));
				}
			}
			
			return $filter_value;
		}
		
		/**
		 * Extracts the subset group from the tag.
		 *
		 * @param 			$tag				The tag to extract from.
		 * @return			string			The subset group from the tag.
		 *
		 */
		public static function get_subset_group($tag) {
			$tag = self::sanitize($tag);
			$subset_group = false;
			$delimiter = "#";
			
			// First explode into two parts using the subset delimiter.
			if ($parts = explode($delimiter, $tag)) {
				// Then explode the first part into words.
				if ($words = explode(" ", trim($parts[0]))) {
					// If we have more than one word, get the last one.
					if (count($words) > 1) {
						$candidate = $words[count($words)-1];
						if (in_array($candidate, self::$subset_groups)) {
							$subset_group = $candidate;
						}
					}
				}
			}
			return $subset_group;
		}

		
		/**
		 * Extracts the subset from the tag.
		 *
		 * @param 			$tag				The tag to extract from.
		 * @return			string			The subset from the tag.
		 *
		 */
		public static function get_subset($tag) {
			$tag = self::sanitize($tag);
			$subset = false;
			$delimiter = "#";
			$keywords  = array("and", "as", "from", "in", "with");
			
			if ($parts = explode($delimiter, $tag)) {
				if (count($parts) > 1) {
					// If there is more than one part after explosion, then we need 
					// to check for other keywords and split the string there.
					if ($pos = strpos_array($parts[1], $keywords)) {
						$subset = trim(substr($parts[1], 0, $pos));
					} else {
						$subset = $parts[1];
					}
				}
			}

			// If there is a hyphen, convert to comma-separated values.
			$parts = explode(",", $subset);
			$pieces = array();
			foreach ($parts as $part) {
				if (strpos($part, "-")) {
					$pieces[] = self::dehyphenate(trim($part));
				} else {
					$pieces[] = trim($part);
				}
			}
			
			$subset = join(",", $pieces);
			return $subset;
		}
		
		/**
		 * Convenience function that converts hyphen-separated ranges to
		 * comma-separated values.
		 *
		 * @param				$range			The range to convert.
		 * @return			$solid			The comma-separated value list.
		 *
		 */
		public static function dehyphenate($range) {
			$pos = strpos($range, "-");
			$val1 = substr($range, $pos-1, ($pos-($pos-1)));
			$val2 = substr($range, $pos+1);
			
			$parts = array();
			for ($i = $val1; $i <= $val2; $i++) {
				$parts[] = $i;
			}
			
			$solid = implode(",", $parts);
			return $solid;
		}
		
		/**
		 * Convenience function that dissects the given tag into its parts.
		 * If the tag is not valid, then this function returns false.
		 *
		 * @param				$tag					The tag to dissect.
		 * @return			array					The parts in an array.
		 *
		 */
		public static function comprehend($tag) {
			$parts = false;
			
			if (self::is_valid($tag)) {
				$parts['operation']	   = self::get_operation($tag);
		 		$parts['object'] 			 = self::get_object($tag);
		 		$parts['mode'] 				 = self::get_mode($tag);
		 		$parts['index']				 = self::get_index($tag);
		 		$parts['parameters']	 = self::get_parameters($tag);
		 		$parts['options'] 		 = self::get_options($tag);
		 		$parts['filter_group'] = self::get_filter_group($tag);
		 		$parts['filter_value'] = self::get_filter_value($tag);
		 		$parts['subset_group'] = self::get_subset_group($tag);
		 		$parts['subset']			 = self::get_subset($tag);
			}
				 		
	 		return $parts;
		}

 		/**
 		 * Converts the current Parsel into a string for debugging.
 		 *
		 * @param				$tag					The tag to stringify.
 		 * @return			string				The stringified Parsel.				
 		 *
 		 */
 		public static function stringify($tag) {
 			$string  = "reader.php: $tag<br>";
 			$string .= "Operation: " 		. self::get_operation($tag) 	 . "<br>";
 			$string .= "Object: " 	 		. self::get_object($tag)			 . "<br>";
 			$string .= "Mode: " 		 		. self::get_mode($tag)		 		 . "<br>";
 			$string .= "Index: "		 		. self::get_index($tag)				 . "<br>";
 			$string .= "Parameters: "		. self::get_parameters($tag)   . "<br>";
 			$string .= "Options: "			. self::get_options($tag)			 . "<br>";
 			$string .= "Filter Group: " . self::get_filter_group($tag) . "<br>";
 			$string .= "Filter Value: " . self::get_filter_value($tag) . "<br>";
 			$string .= "Subset: " 			. self::get_subset($tag)			 . "<br>";
 			$string .= "<<<OUTPUT<br><br><br>";
 			return $string;
 		}
	}
?>