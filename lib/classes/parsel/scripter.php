<?php
	/**
	 * Parsel R2
	 *
	 * scripter.php
	 * Scripter handles Javascript.
	 *
	 */

	require_once("parsel.php");

	class Scripter {
		private $factory;
		private $contents;
		private $tags;
		private $scripts;
		private $count;
	
		public function __construct($contents, $tags) {
			// Set up instance variables.
			$this->factory  = "..".DS."lib".DS."classes".DS."parsel".DS."inc".DS."js".DS;
			$this->contents = $contents;
			$this->tags			= $tags;
			$this->scripts	= array();
			$this->count		= $this->count_instances();
			
			// Catalog the existing Javascript.
			$this->catalog();
			
			// If the script needs dependencies, add them.
			if ($keywords = $this->needs_dependencies()) {
				$this->add_dependencies($keywords);
			}
		}
	
		/**
		 * Gets the contents from the Scripter.
		 *
		 * @return									string			The contents from the Scripter.
		 *
		 */
		public function get_contents() {
			return $this->contents;
		}
		
		/**
		 * This function gets the number of Javascript objects in the content.
		 *
		 * @return									int					Number of Javascript instances.
		 *
		 */
		public function count_instances() {
			return substr_count(strtolower($this->contents), "<script");
		}
		
		/**
		 * Gets Javascript content and places into an instanced array.
		 *
		 */
		public function catalog() {
			for ($i = 1; $i < $this->count+1; $i++) {
				$start = strnpos($this->contents, "<script", 	 $i);
				$end   = strnpos($this->contents, "</script>", $i) + strlen("</script>");
				
				$script = substr($this->contents, $start, ($end-$start));
				$this->scripts[] = $script;
			}
		}
		
		/**
		 * This function returns Javascript that tries to load jQuery from 
		 * the Google Libraries API, and if it can't, loads a local copy.
		 *
		 * @return									string			The javascript.
		 *
		 */
		public function jquery() {
			$check   = "!window.jQuery && document.write('<script src=\"" . $this->factory . "jquery-1.5.1.min.js\"><\/script>')";
			$jquery  = Marker::make_script("", "https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js");
			$jquery .= Marker::make_script($check);
			
			return $jquery;
		}
		
		/**
		 * This function gets the user's slideshow settings and generates
		 * the appropriate script.
		 *
		 * @param				$keywords			array				An array of found keywords.
		 * @return										string			The javascript.
		 *
		 */
		public function slideshow($keywords=array()) {
			// Get the slideshow settings.
			$autoplay = Setting::find_by_name('slideshow_autoplay');
			$autoplay = $autoplay->get_value();
			if ($autoplay) {
				$delay = Setting::find_by_name('slideshow_delay');
				$delay = $delay->get_value();
			}
			
			$text_nav = Setting::find_by_name('slideshow_text_nav');
			$text_nav = $text_nav->get_value();
			if ($text_nav) {
				$text_nav_prev = Setting::find_by_name('slideshow_text_nav_prev');
				$text_nav_next = Setting::find_by_name('slideshow_text_nav_next');
				$text_nav_prev = $text_nav_prev->get_value();
				$text_nav_next = $text_nav_next->get_value();
			}

			$pager = Setting::find_by_name('slideshow_pager');
			$pager = $pager->get_value();
			
			$transition = Setting::find_by_name('slideshow_transition');
			$transition = $transition->get_value();

			// Begin building the jQuery statement.
			$script  = "$('document').ready(function() {";
			$script .= "$('.parsel_slideshow_images').each(function() {";
			$script .= "var \$this = $(this);";
			$script .= "$(this).cycle({";

			// Select the proper transition
			$script .= 'fx: "' . $transition . '", ';

			// If the user asked for captions for this particular slideshow,
			// display them. This is not a stored setting, it is set in Parsel.
			if (array_key_exists("captions", $keywords)) {
				$script .= "after: function() { \$this.parent().find('.parsel_slideshow_caption').html(this.alt); },";
			}
			
			// If the user has defined autoplay, set their defined delay.
			// Otherwise, the slideshow can only be navigated with buttons.
			if (!$autoplay) {
				$script .= "timeout: 0,";
			} else {
				$script .= "timeout: " . $delay . ", ";
			}
			
			if ($pager) {
				$script .= "pager: '.parsel_slideshow_pager',";
			}
			
			// If the user has defined text navigation, display it.
			if ($text_nav) {
				$script .= "next: \$this.parent().find('.parsel_slideshow_prev'),";
				$script .= "prev: \$this.parent().find('.parsel_slideshow_next')";
			} else {
				// Otherwise, clicking the image advances the slideshow.
				// This is not user-changeable.
				$script .= "next: \$this.parent().find('.parsel_slideshow_prev'),";
			}
			
			$script .= "});";
			$script .= "});";
			$script .= "});";


			return Marker::make_script($script);
		}
		
		/**
		 * Checks whether or not the page needs dependencies to be loaded.
		 *
		 * @return									array				Array of dependent tags.
		 * @return									boolean			False if dependencies are not needed.
		 *
		 */
		public function needs_dependencies() {
			$keywords = array("slideshow", "mesh", "captions");
			
			$dependencies = false;
			foreach ($this->tags as $tag) {
				foreach ($keywords as $keyword) {
					if (strpos($tag, $keyword)) {
						$dependencies[$keyword] = $tag;
					}
				}
			}
			
			return $dependencies;
		}
		
		/**
		 * Adds all dependencies to the script after user-loaded javascript.
		 * This should probably only load what's needed.
		 *
		 */
		public function add_dependencies($keywords) {
			$dependencies  = $this->jquery();
			$dependencies .= Marker::make_script("", ($this->factory . "jquery.cycle.all.min.js"));
			$dependencies .= $this->slideshow($keywords);
			$dependencies .= "</head>";
			
			$this->contents = str_replace("</head>", $dependencies, $this->contents); 
		}
		
		public function clean() {
			foreach ($this->scripts as $script) {
				$this->contents = str_replace($script, "", $this->contents);
			}
		}
		
		public function stringify() {
			$string  = "";
 			$string .= "Scripter<br>";
			for ($i = 0; $i < $this->count; $i++) {
				$string .= ($i+1) . ": " . htmlspecialchars($this->scripts[$i]) . "<br>";
			}
			
			$string .= "<br>";
			echo $string;
		}
	}