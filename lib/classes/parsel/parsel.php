<?php
	/**
	 * Parsel R2
	 *
	 * parsel.php
	 * Parsel handles high-level layout management.
	 *
	 */
	 
		require_once("sequencer.php");
		require_once("reader.php");
		require_once("operator.php");
		require_once("builder.php");
		require_once("generator.php");
		require_once("scripter.php");
		require_once("marker.php");
		
   	defined("OPEN_TAG") ? null : define("OPEN_TAG", "{");
		defined("CLOSE_TAG") ? null : define("CLOSE_TAG", "}");
		defined("EXT") ? null : define("EXT", "htm");
		
		defined("VERSION") ? null : define("VERSION", "R2");

	  class Parsel {
			public $tags;
			private $obj_id;
			private $page;
			private $path;
			private $layout;
			private $contents;
			private $order;
		
			private $slideshow_options;
	 		
	 		// The set of Parsel-recognized objects and their pluralizations.
	 		private static $object_map = array("project"	  => "projects",
								 		 							  	   "foundation" => "foundations",
																	 		   "user"			  => "user",
								 									 		   "image"	 	  => "images",
								 							 		 		   "video"		  => "videos",
																	 		   "site"		 	  => "site",
																	 		   // Generate
																	 		   "nav"				=> "nav",
																	 		   "title"			=> "title",
																	 		   "slideshow"  => "slideshow",
																	 		   "widget"			=> "widget");
	 		
	 		/**
			 * The constructor for a Parsel takes in the layout
			 * and the specific page to parse.
			 *
			 * @param				$layout				The current layout (theme).
			 * @param				$page					The page to parse.
			 * @param				$obj_id				The object ID of the page, if applicable.
			 * @param				$original			Whether or not this is the original 
			 															instsnce of Parsel. Defaults to true.
			 *
			 */
	 		public function __construct($layout, $page, $obj_id=0, $original=true) {
	 			// Load plugins.
	 			if ($original) {
	 				$this->load_plugins();
	 			}
	 		
	 			// Set the layout.
	 			$this->set_layout($layout);
	 			
	 			// If we have an object ID for this page, we should set it.
	 			if (!empty($obj_id)) {
		 			$this->set_obj_id($obj_id);
		 		}
		 
	 			// Set up the object by setting the current page and 
	 			// generating the absolute path to the page. Then, get
	 			// the contents and get the tags from within it.
	 			$this->set_page($page);
				$this->make_path();
				
	 			if ($this->get_file_contents()) {
		 			$this->get_tags();
	 			}
	 			
	 			// Create a Sequencer.
	 			$this->order = new Sequencer($this->tags);
	 			
	 			// Save a copy of the original layout with tags,
	 			// and then immediately begin processing.
	 			$this->process();
	 			
	 			if ($original) {
		 			// Maintain Javascript dependencies.
		 			$this->script();
	 			}
	 		}
	 		
	 		/**
	 		 * Includes all Parsel plugins.
	 		 *
	 		 */
	 		public function load_plugins() {
	 			$path = CLASS_PATH.DS."parsel".DS."plugins";

				if (is_dir($path)) {
					if ($dh = opendir($path)) {
						while (($file = readdir($dh)) !== false) {
            	if ($file != "." && $file != "..") {
	            	$plugin = $path . DS . $file . DS . "*.php";

								foreach(glob($plugin) as $filename) {
									include $filename;
								}
            	}
		        }
		        closedir($dh);
					}
				}
	 		}
	 		
	 		/**
			 * Returns the current object ID.
			 *
			 * @return			string				The current object ID.
			 *
			 */
	 		public function get_obj_id() {
	 			return $this->obj_id;
	 		}
	 		
	 		/**
			 * Sets the current object ID.
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
			 * Returns the current path.
			 *
			 * @return			string				The current path.
			 *
			 */
	 		public function get_path() {
	 			return $this->path;
	 		}
	 		
	 		/**
			 * Sets the current path.
			 *
			 * @param				$obj_id				The new current path.
			 *
			 */
	 		public function set_path($path) {
	 			$this->path = $path;
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
	 		 * Returns the current contents..
			 *
			 * @return			string			The current contents.
			 *
			 */		
	 		public function get_contents() {
	 			return $this->contents;
	 		}
	 		
	 		
	  	/**
			 * Puts the contents of the current page into an instance variable.
			 *
			 */
	 		public function get_file_contents() {
	 			$success = false;
	 			if ($this->contents = file_get_contents($this->path)) {
	 				$success = true;
	 			}
	 			
	 			return $success;
	 		}
	 		
	 		/**
	 		 * Gets the map of Parsel-recognized objects.
	 		 *
	 		 * @return							array			The array of object names.
	 		 *
	 		 */
	 		public static function get_object_map() {
	 			return self::$object_map;
	 		}
	 		
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
	 						if (self::is_object(self::get_object(self::sanitize($tag)))) {
		 						$valid = true;
		 					}
	 					}
				return $valid;
	 		}
	 		
	  	/**
			 * Gets the tags from the contents and puts them into an instance 
			 * variable.
			 *
			 */
	 		public function get_tags() {
				$this->tags = $this->strip_javascript($this->contents);
	 		}
	 		
	 		public function strip_javascript($content) {
	 			$count = substr_count($content, "<script");
	 			
	 			if ($count > 0) {
	 					for ($i = 1; $i <= $count; $i++) {
						$start = strnpos($content, "<script", 1);
						$end = strnpos($content, "</script>", 1);
						$str = substr($content, $start, (($end-$start)+strlen("</script>")));
						$content = str_replace($str, "", $content);
					}
	 			}
	 			
	 			preg_match_all('(\\' . OPEN_TAG . '.*?\\' . CLOSE_TAG . ')', $content, $matches);
				return array_shift($matches);
	 		}
	 		
	 		public function make_path() {
	 			if (strpos($this->page, EXT)) {
		 			$this->path = LAYOUT.DS.$this->layout.DS.$this->page;
		 		} else {
		 			$this->path = LAYOUT.DS.$this->layout.DS.$this->page.'.'.EXT;
		 		}
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
	 			 		 		
	 		public static function error($type, $tag="") {
				if ($type == "invalid") {
					$error  = "The tag <strong>" . $tag . "</strong> is, for one reason or another, invalid.<br>";
					$error .= "Please revise your tags and try again.<br><br>";
					$error .= "Tag: " . $tag;
				} else if ($type == "operation") {
					$error  = "The operation <strong>" . Reader::get_operation($tag) . "</strong> is not supported.<br>";
					$error .= "Please revise your tags and try again.<br><br>";
					$error .= "Tag: " . $tag;
				} else if ($type == "object") {
					$error  = "The object <strong>" . Reader::get_object($tag) . "</strong> is not valid.<br>";
					$error .= "Please revise your tags and try again.<br><br>";
					$error .= "Tag: " . $tag;
				} else if ($type = "404") {
					$error = "The layout file you requested was not found.<br>";
					$error .= "Please check that it exists and try again.";
				}
				
				echo $error;
	 		}

	 		public function prepare() { 		
				// Save slideshow options.
				if ($mode == "slideshow") {
					$this->slideshow_options = $options;
				}

 				$contents = $this->{'p' . $operation}($object, $mode, $options, $param, $source);

 				if ($object == 'site' && 
 						$param == 'pagename' && 
 						($_SERVER['REQUEST_URI'] == '/index.php' || 
 						 $_SERVER['REQUEST_URI'] == '/' ||
 						 $_SERVER['REQUEST_URI'] == '/home' )) {
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

			/**
			 * Creates an array containing the variables for the Parsel object.
			 *
			 * @return			array				The array containing system vars.
			 */
			public function systemize() {
				$system['layout'] 		 = $this->get_layout();
				$system['page'] 			 = $this->get_page();
				$system['path'] 			 = $this->get_path();
				$system['obj_id'] 		 = $this->get_obj_id();
				$system['request_uri'] = $_SERVER['REQUEST_URI'];
				
				return $system;
			}
			
			/**
			 * Initiates the Scripter to analyze the existing Javascript
			 * and add Parsel dependencies.
			 *
			 */
			public function script() {
				$scripter = new Scripter($this->contents, $this->tags);
				$scripter->catalog();
				$scripter->clean();
				$this->contents = $scripter->get_contents();
			}
			
			/**
			 * Processes the tags in the layout file one-by-one with the 
			 * Sequencer, comprehends each one with the Reader, then calls
			 * the Operator to replace the tag with content.
			 *
			 */
			public function process() {
				// Make an array of system variables.
				$system = $this->systemize();

				// Loop over the tags using the Sequencer.
				while ($this->order->get_index() < $this->order->count()) {
					// Get the tag from the Sequencer and dissect it into parts.
					$tag = $this->order->get_tag();
					$parts = Reader::comprehend($tag);
					
					// Call the operator to perform the correct function.
					$function = "p" . $parts['operation'];
					
					if ($function != "p") {
						$result = call_user_func_array(array("Operator", $function), array($system, $parts));
						
						# echo Reader::stringify($tag);
						
						// Replace the tag in the contents with the result.
						$this->contents = str_replace($tag, $result, $this->contents);
				
						// Advance to the next tag.
						$this->order->advance();
					} else {
						self::error("object", $tag);
						exit;
					}
				}
			}
		 		
	 		public function render() {
	 			echo $this->contents;
			}
	 		 		
	 		/*
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
	 		*/
	 			 		
	 		/**
	 		 * Converts the current Parsel into a string for debugging.
	 		 * 
	 		 * @return			string				The stringified Parsel.				
	 		 *
	 		 */
	 		public function stringify() {
	 			$string  = "";
	 			$string .= "Parsel<br>";
	 			$string .= "Layout: " . $this->layout . "<br>";
	 			$string .= "Page: " . $this->page . "<br>";
	 			$string .= "Path: " . $this->path . "<br>";
	 			$string .= "Tagcount: " . count($this->tags) . "<br><br>";
	
	 			return $string;
	 		}
	 	}