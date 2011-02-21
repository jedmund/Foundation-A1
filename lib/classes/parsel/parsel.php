<?php

/**
 * Parsel
 * parsel.php
 *
 * Parsel parses templates. 
 *
 */
 
 	require_once(LIB_PATH.DS."init".DS."config.php");
 	
 	defined("OPEN_TAG") ? null : define("OPEN_TAG", "{");
	defined("CLOSE_TAG") ? null : define("CLOSE_TAG", "}");
	defined("EXT") ? null : define("EXT", "htm");
	
	// These constants should help clean up the source code a bit.
 	defined("BR") ? null : define("BR", "\n");
 	defined("TAB") ? null : define("TAB", "\t");
 	
 	class Parsel {
 		
 		private static $default_operation;
 		
 		private static $operations = array("display", "include", "import");
 		private $objects = array("project"	  => "projects",
			 		 							  	 "foundation" => "foundations",
												 		 "user"			 	=> "user",
			 									 		 "image"	 	  => "images",
			 							 		 		 "video"		  => "videos",
												 		 "site"		 		=> "site");
		private $social = array("facebook", "twitter", 
														"youtube",	"vimeo",
														"github", 	"dribbble",
														"tumblr",   "posterous", 
														"lastfm", 	"linkedin",
														"gowalla",  "foursquare",
														"email",		"skype",
														"spotify",	"zootool",
														"flickr",   "ffffound");
 		private $obj_id;
 		private $page;
 		private $path;
 		private $layout;
 		private $contents;
 		public $tags;


		private $slideshow_options;
 		
 		/**
		 * The constructor for a Parsel takes in the layout
		 * and the specific page to parse.
		 *
		 * @param				$layout				The current layout (theme).
		 * @param				$page					The page to parse.
		 *
		 */
 		public function __construct($layout, $page="", $obj_id=0) {
 			$this->set_layout($layout);
 			
 			if (!empty($obj_id)) {
	 			$this->set_obj_id($obj_id);
	 		}
	 
	 		if (!empty($page)) {
	 			$this->set_page($page);
				$this->make_path();
	 			$this->get_contents();
	 			$this->get_tags();
			}
			 			
 			self::$default_operation = "display";
 		}
 		
 		/**
		 * Returns the current page.
		 *
		 * @return			string				The current object ID.
		 *
		 */
 		public function get_obj_id() {
 			return $this->obj_id;
 		}
 		
 		/**
		 * Sets the current page.
		 *
		 * @param				$obj_id				The new current object ID.
		 *
		 */
 		public function set_obj_id($obj_id) {
 			$this->obj_id = $obj_id;
 		}

 		/**
		 * Returns the current page.
		 *
		 * @return			string				The current page.
		 *
		 */
 		public function get_page() {
 			return $this->page;
 		}
 		
 		/**
		 * Sets the current page.
		 *
		 * @param				$page					The new current page.
		 *
		 */
 		public function set_page($page) {
 			$this->page = $page;
 		}
 
 		/**
		 * Returns the current layout.
		 *
		 * @return			string			The current layout.
		 *
		 */		
 		public function get_layout() {
 			return $this->layout;
 		}
 		
 		/**
		 * Sets the current layout.
		 *
		 * @param				$layout				The new current layout.
		 *
		 */
 		public function set_layout($layout) {
 			$this->layout = $layout;
 		}
 		
 		
  	/**
		 * Puts the contents of the current page into an instance variable.
		 *
		 */
 		public function get_contents() {
 			$success = false;
 			if ($this->contents = @file_get_contents($this->path)) {
 				$success = true;
 			}
 			
 			return $success;
 		}
 		
  	/**
		 * Gets the tags from the contents and puts them into an instance 
		 * variable.
		 *
		 */
 		public function get_tags() {
 			$nojs = $this->contents;
 			
 			$count = substr_count($nojs, "<script");
 			
			if ($count > 0) {
				for ($i = 1; $i <= $count; $i++) {
					$start = strnpos($nojs, "<script", 1);
					$end = strnpos($nojs, "</script>", 1);
					$str = substr($nojs, $start, (($end-$start)+strlen("</script>")));
					$nojs = str_replace($str, "", $nojs);
				}
			}

 			
 			preg_match_all('(\\' . OPEN_TAG . '.*?\\' . CLOSE_TAG . ')', $nojs, $matches);
			$this->tags = array_shift($matches);
 		}
 		
 		public function make_path() {
 			$this->path = LAYOUT.DS.$this->layout.DS.$this->page.'.'.EXT;
 		}
 		
 		/**
		 * Strips the opening and ending characters from the given tag.
		 *
		 * @param				$tag				The tag to sanitize.
		 * @return 			string			The sanitized tag.
		 *
		 */
 		public static function sanitize($tag) {
 			return str_replace(array('{','}'), '', $tag);
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
 		 * Extracts the object from the tag.
 		 *
 		 * @param				$tag				The tag to extract from.
 		 * @return			string			The object from the tag.
 		 *
 		 */
 		public static function get_object($tag) {
 			$tag = self::sanitize($tag);

 			// If the tag is an operation, find the object inside the phrase.
 			// Otherwise, explode the tag at the parameter delimiter.
 			if ($words = self::is_operation($tag)) {
 				$object = $words[1];
 				
 				if (substr($object, 0, 1) == "\"") {
 					$object = trim(str_replace("\"", "", $object));
 				} else {
	 				$object = trim(array_shift(explode('.', $object)));
	 			}
 			} else {
 				if (substr($tag, 0, 1) == "\"") {
 					$object = trim(str_replace("\"", "", $tag));
 				} else {
	  			$object = trim(array_shift(explode('.', $tag)));
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
 		 * Extracts the mode from the tag, or false if there is no mode.
 		 *
 		 * @param				$tag				The tag to extract from.
 		 * @return 			string			The mode from the tag.
 		 *
 		 */
 		public static function get_mode($tag) {
 			$tag = self::sanitize($tag);
 			
 			$object = self::get_object($tag);
 			$params = self::get_param($tag);
 			
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
 		
 		public static function get_source($tag) {
 			$tag = self::sanitize($tag);
 			
 			$delimiter = "from";
 			$source = false;
 			
 			if ($words = explode(" ", trim($tag))) {
 				if (in_array($delimiter, $words)) {
 					$key = array_search($delimiter, $words);
 					$source = $words[$key+1];
 				}
 			}
 			return trim($source); 			
 		}
 		
 		private function is_chain($params) {
			$chain = false;
 			if (is_array($params) && count($params > 1)) {
				$chain = true;
 			}
 			return $chain;
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
			$separator = "and";
			
 			// If the tag has options, find them inside the phrase.
 			// Otherwise, return false, because there is no mode.
 			if ($words = self::is_operation($tag)) {
 				if (in_array($delimiter, $words)) {
	 				$key = array_search($delimiter, $words);
	 				
	 				// Split the array at the key.
	 				$options = array_slice($words, $key+1);

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
 		 * Extracts the parameters from the tag, or false if there are no 
 		 * options.
 		 * 
 		 * @param				$tag				The tag to extract from.
 		 * @return			string			The parameter from the tag.				
 		 *
 		 */
 		public static function get_param($tag) {
 			$tag = self::sanitize($tag);
 			$delimiter = ".";
 			$params = array();
 			
 			// Get rid of any options or modes in the tag.
 			if (strpos($tag, "as")) {
 				$tag = array_shift(explode("as", $tag));
 			}
 			
 			if (strpos($tag, "with")) {
 				$tag = array_shift(explode("with", $tag));
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
 		
 		public function error($type) {
 			$this->set_page($type);
 			$this->make_path();
 			if ($this->get_contents($page)) {
 				$this->get_tags();
 				$this->display();
 			} else {
 				echo "<p>There is no template for error type " . $type . ".</p>\n";
 				echo "<p>Please contact the site owner.</p>";
 			}
 		}
 		
 		public function process() {
 			// Add Javascript dependencies.
 			if (!strpos($this->contents, "<script")) {
 				$js = '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
 							 <script>!window.jQuery && document.write(\'<script src="/resources/js/jquery-1.4.2.min.js"><\/script>\')</script>';
 			} else {
 				$js = "";
 			}
 			
 			$jq = '<script type="text/javascript">' . BR . '$(document).ready(function() {' . BR;
 			if (strstr($this->contents, "slideshow")) {
				$js .= "<script src='".DS.'resources'.DS.'jquery.cycle.all.min.js' . "' type='text/javascript'></script>";
				$jq .= Markup::make_slideshow_js($this->slideshow_options);				
 			}
 			
 			$jq .= BR . '});' . BR . '</script>';
 			
 			$js .= $jq;
 			$js .= "</body>";
 			
 			$this->contents = str_replace("</body>", $js, $this->contents);
 		}

 		public function prepare() { 			
 			foreach ($this->tags as $tag) {
 				$operation = $this->get_operation($tag);
 				$object = $this->get_object($tag);
 				$source = $this->get_source($tag);
 				$mode = $this->get_mode($tag);
 				$param = $this->get_param($tag);
 				$options = $this->get_options($tag);
 				
 		/*
				echo "Operation: " . $operation . "<br>";
 				echo "Object: " . $object . "<br>";
 				echo "Source: " . $source . "<br>";
 				echo "Mode: " . $mode . "<br>";
 				echo "Param: " . $param . "<br>";
 				echo "Options: " . $options . "<br>";
 				echo "<hr>";
		*/
	
				// Save slideshow options.
				if ($mode == "slideshow") {
					$this->slideshow_options = $options;
				}

 				$contents = $this->{'p' . $operation}($object, $mode, $options, $param, $source);

 				if ($object == 'site' && 
 						$param == 'pagename' && 
 						($_SERVER['REQUEST_URI'] == '/index.php' || 
 						 $_SERVER['REQUEST_URI'] == '/')) {
 					$start = strpos($this->contents, "<title>");
 					$end = strpos($this->contents, "</title>") + strlen("</title>");
 					$old = substr($this->contents, $start, ($end-$start));
 					
 					$setting = Setting::find_by_name('title');
 					$new = "<title>" . $setting->get_value() . "</title>";
 					
 					$this->contents = str_replace($old, $new, $this->contents);
 				} else {
					$this->contents = str_replace($tag, $contents, $this->contents);
				}
 			}
 		}
 		
 		public function render() {
 			$this->prepare();
 			$this->process();
 			echo $this->contents;
		}
 	 		
 		public function pinclude($object) { 			
 			$parsel = new $this($this->layout, $object, $this->obj_id, $_SERVER['REQUEST_URI']);
 			$parsel->prepare();
			$contents = $parsel->contents;
			return $contents;
 		}
 		
 		public function pimport($object, $mode, $options, $param, $source) {
 			$setting = Setting::find_by_name('layout');
 			$import = "";
 			$path = DS.'layout'.DS.$setting->get_value().DS;
 			
 			if ($source == 'web') {
 				$path = '';
 				$source = '';
 			}
 			
 			if (!empty($mode)) {
 				if ($mode == 'css') {
 					if (empty($source)) {
 						$import = '<link href="' . $path . '" rel="stylesheet" type="text/css">' . BR;
 					} else {
 						$import = '<link href="' . $path . $source . DS . $object . '" rel="stylesheet" type="text/css">' . BR;
 					}
 				} else if ($mode == 'js') {
 					if (empty($source)) {
 						$import = '<script src="' . $path . $object . '" type="text/javascript"></script>';
 					} else {
 						$import = '<script src="' . $path . $source . DS . $object . '" type="text/javascript"></script>';
 					}
 				}
 			} else {
 				$import = $path . $source . DS . $object;
 			}
 			
 			return $import;
 		}
 		 		
 		public function pdisplay($object, $mode, $options=array(), $param=0) {
 			$builder = new Builder();
 			
	 		// Get the class name (singular) and function name (plural) from the map.
			if (array_key_exists($object, $this->objects)) {
				$function_obj = $this->objects[$object];
				$class_name = ucwords($object);
			} else {
				$function_obj = $object;
				$class_name = ucwords(array_search($object, $this->objects));
			}
			
			$function = "build_" . $function_obj;
 			
 			$contents = '';
			if ($object != "page" && $param) {	
					
 				/**
 				 * If we have a single parameter and it is non-numeric, then
 				 * it is likely a request for an instance variable.
 				 * EX: { project.title }
 				 *
 				 */ 
 				if (!is_array($param) && !is_numeric($param)) {
 					if ($object == 'user') {
 						if (!is_array($param) && in_array($param, $this->social)) {
	 						$contents = Builder::social($param, $mode, 10);
 						} else {
		 					$contents = Builder::param($class_name, $param, 10);
		 				}
	 				} else if ($object == 'site') {
	 					$contents = Builder::setting($param, $_SERVER['REQUEST_URI']);
 					} else { 
	 					$contents = Builder::param($class_name, $param, $this->obj_id);
	 				}
					// This is causing an error... aren't we replacing tags outside 
					// of this function?
					//$this->contents = str_replace($tag, $contents, $this->contents);

				/**
				 * If we have a single parameter and it is numeric, then it is
				 * likely a request for a local index request.
				 * EX: { images.1 }
				 *
				 */
				} else if (!is_array($param) && is_numeric($param)) {
					$oid = Builder::index($class_name, $param, $this->obj_id); // get the object requested.
					$contents = $builder->{$function}($mode, $options, 0, $oid); // mode, options, object id
				/**
				 * If we have multiple parameters, then it is likely a global 
				 * index request.
				 * EX: { projects.1.images.1 }
				 *
				 */
				} else if (is_array($param)) {
					if (Builder::has_pairs($object, $param) == 1) {	
						$contents = Builder::chain($object, $param, $this->obj_id);
					} else if (Builder::even_pairs($object, $param)) {
						$obj = Builder::chain($object, $param, $this->obj_id);
						$function = "build_" . strtolower(get_class($obj)) . "s";
						$contents = $builder->{$function}($mode, $options, 0, $obj->id);
					} else if ($param[0] == 'image') {
						$image = Image::find_by_sequence($param[1]);
						$image = $builder->build_image($image, $mode, $options);
						$contents = Markup::make_image($image['src'], $image['alt'], $image['attributes']);
					}
					
 					//$this->contents = str_replace($tag, $contents, $this->contents);
 				} else {
 					if (is_numeric($param) && !is_array($param)) {
	 					$contents = $builder->{$function}($mode, $options, $this->obj_id, 0, $param);
	 				} else {
	 					$contents = $builder->{$function}($mode, $options, $this->obj_id);
	 				}
 				}
 			} else if ($object == "page" && $param) {
 				$title = '';
 				if (!is_array($param) && $param == "title") {
 					$setting = Setting::find_by_name('title');
 					$title = $setting->get_value();
 					
 					if (!empty($this->obj_id)) {
 						$project = Project::find_by_id($this->obj_id);
 						$title = $project->title . " on " . $title;
 					} else {
 						// Put something here for generic pages.
 					}
 					$contents = $title;
 				}
	 		} else {
				$contents = $builder->{$function}($mode, $options, $this->obj_id);
	 		}
	 		
 			return $contents;
 		}
 		
 		/**
 		 * Converts the current Parsel into a string for debugging.
 		 * 
 		 * @return			string				The stringified Parsel.				
 		 *
 		 */
 		public function stringify() {
 			$string .= "Parsel<br>";
 			$string .= "Layout: " . $this->layout . "<br>";
 			$string .= "Page: " . $this->page . "<br>";
 			$string .= "Path: " . $this->path . "<br>";
 			$string .= "Tagcount: " . count($this->tags);

 			return $string;
 		}
 	}