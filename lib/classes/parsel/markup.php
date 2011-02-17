<?php

/**
 * Markup
 * markup.php
 *
 * Markup writes markup.
 *
 */
 
 	require_once(LIB_PATH.DS."init".DS."config.php");

	// These constants should help clean up the source code a bit.
 	defined(BR) ? null : define(BR, "\n");
 	defined(TAB) ? null : define(TAB, "\t");

 	class Markup {
 	
 		/** 
 		 * <ul> <ol>
 		 * Makes an unordered or ordered list in HTML.
		 !! 	Limitation: This method doesn't allow for semantics on individual items.
		 									Maybe the $items array is associative with specific keys 
		 									per item?
 		 *
 		 * @param				$type					String that determines the type of list to build.
 		 * @param				$items				An array of list items.
 		 * @param				$parent_attr 	An array of attributes for the parent element.
 		 * @param				$child_attr		An array of attributes for the children elements.
 		 * @return			string				A string containing the HTML for the list.
 		 *
 		 */	 
 		public static function make_list($type, $items, $parent_attr, $child_attr) {
 			// First build the parent attributes.
 			$attributes = self::build_attributes($parent_attr);
 			
 			// Create the opening and closing tags for the parent element.
 			$opening = "<" . $type . $attributes . ">" . BR;
 			$closing = "</" . $type . ">";
 			
 			// Now, build the children attributes.
 			$attributes = self::build_attributes($child_attr);
 			
 			// Create the children elements.
 			$children = self::make_children("li", $items, $attributes);
 			 			
 			// Put the list together.
 			$html = $opening . $children . $closing;
 			return $html;
 		}
 		
 		/** 
 		 * <select>
 		 * Makes an dropdown list in HTML.
		 !! 	Limitation: This method doesn't allow for semantics on individual items.
		 									Maybe the $items array is associative with specific keys 
		 									per item?
 		 *
 		 * @param				$items				An array of list items.
 		 * @param				$parent_attr 	An array of attributes for the parent element.
 		 * @param				$child_attr		An array of attributes for the children elements.
 		 * @return			string				A string containing the HTML for the list.
 		 *
 		 */	 
 		public static function make_select($items, $parent_attr, $child_attr) {
 			$type = "select";
 		
			// First build the parent attributes.
 			$attributes = self::build_attributes($parent_attr);
 			
 			// Create the opening and closing tags for the parent element.
 			$opening = "<" . $type . $attributes . ">" . BR;
 			$closing = "</" . $type . ">";
 			
 			// Now, build the children attributes.
 			$attributes = self::build_attributes($child_attr);
 			
 			// Create the children elements.
 			$children = self::make_children("option", $items, $attributes);
 			 			
 			// Put the list together.
 			$html = $opening . $children . $closing;
 			return $html;
		}
		
		/**
		 * A helper function to make children elements.
		 *
		 * @param				$tag					The tag to wrap the items in.
 		 * @param				$items				An array of items.
 		 * @param				$attributes		An array of attributes for the elements.
 		 * @return			string				A string containing the HTML for the 
 		 															elements.
		 *
		 */
		public static function make_children($tag, $items, $attributes) {
			$children = "";
			foreach ($items as $item) {
 				// Build the system attributes.
 				$system_attr = (!empty($item['attributes'])) ? self::build_attributes($item['attributes']) : "";
 			
 				// We might need to make a merge attributes function to 
 				// prevent overwriting things and have semantic code.
 				$opening_ch = TAB . "<" . $tag . $system_attr . $attributes . ">" . BR; 
 				$closing_ch = "</" . $tag . ">" . BR;
 				
 				if (!empty($item['href'])) {
					$link_op = TAB . "<a href='" . $item['href'] . "'>" . BR;
 					$link_cl = "</a>" . BR;
 				} else {
 					$link_op = "";
 					$link_cl = "";
 				}
 				
 				if (!empty($item['date'])) {
 					$date = "<span class='parsel_date'>" . $item['date'] . "</span>";
 				}

 				$blurb = '';
 				if (!empty($item['thumb']) || !empty($item['src'])) {
	 				if (!empty($item['thumb'])) {
	 					$src = $item['thumb'];
	 				} else if (!empty($item['src'])) {
	 					$src = $item['src'];
	 				}
	 				
	 				$value = TAB . "<img alt='" . $item['value'] . "' src='" . $src . "'>" . BR;
	 				
	 				if (!empty($item['title']) && $item['type'] != 'dribbble') {
	 					$value .= "<h3>" . $item['title'] . "</h3>";
	 				} else {
	 					$title = "<h3>" . $item['title'] . "</h3>";
	 				}
	 				
	 				if (!empty($item['blurb'])) {
	 					$blurb = "<p>" . $item['blurb'] . "</p>";
	 				}
	 			} else if (!empty($item['text'])) {
	 				$value = TAB . "<p class='parsel_text'>" . $item['text'] . "</p>" . BR;
	 			} else {
	 				$value = TAB . $item['value'] . BR;
 				}
 				
 				if (!empty($item['type'])) {
 	 				if ($item['type'] == 'twitter') {
		 				$child = $opening_ch . $value . $link_op . $date . $link_cl . $closing_ch;
	 				} else if ($item['type'] == 'dribbble') {
	 					$child = $opening_ch . $link_op . $value . $link_cl .$closing_ch;
	 				} else {
	 					$child = $opening_ch . $link_op . $value . $link_cl . $blurb . $closing_ch;
	 				}
	 			} else {
 					$child = $opening_ch . $link_op . $value . $link_cl . $blurb . $closing_ch;
	 			}
	 			
 				$children .= $child;
 			}
			return $children;
		}
		
		public static function make_slideshow($items, $parent_attr, $child_attr) {
			$html = '';
		
			if (!empty($items)) {
				// Determine if we need to make a text nav.
				$text_nav = Setting::find_by_name('slideshow_text_nav');
				$text_nav = $text_nav->get_value();
				if ($text_nav) {
					// If we do, get the label values.
					$text_nav_prev = Setting::find_by_name('slideshow_text_nav_prev');
					$text_nav_next = Setting::find_by_name('slideshow_text_nav_next');
					$text_nav_prev = $text_nav_prev->get_value();
					$text_nav_next = $text_nav_next->get_value();
					
					$nav = "<nav class='ss_nav'>". BR;
					$nav .= TAB . "<ul>" . BR;
					$nav .= TAB . TAB . "<li class='ss_prev'><a>" . $text_nav_prev . "</a></li>" . BR;
					$nav .= TAB . TAB . "<span class='ss_separator'>/</span>" . BR;
					$nav .= TAB . TAB . "<li class='ss_next'><a>" . $text_nav_next . "</a></li>" . BR;
					$nav .= TAB . "</ul>" . BR;
					$nav .= TAB . "<div class='pager'></div>" . BR;
					$nav .= "</nav>" . BR;
				}
			
				// Build the parent attributes
				$attributes = self::build_attributes($parent_attr);
				
				// Create the opening and closing tags for the parent element.
				$opening = "<div class='parsel_slideshow ss_next' " . $attributes . ">" . BR;
				$closing = "</div>" . BR;
				
				// Create the contents.
				$slideshow = '';
				foreach ($items as $item) {
					$image = self::make_image($item['src'], $item['alt'], $child_attr);
					$slideshow .= $image;
				}
				
				// Add the caption container.
				$caption= "<p class='caption'></p>";
				
				// Assemble the container.
				$html = $nav . $opening . $slideshow . $closing . $caption;
				return $html;
			}
		}
		
		public static function make_slideshow_js($options=0) {
			if (empty($options)) {
				$options = array();
			}
		
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
			$jq = "$('.parsel_slideshow').cycle({";
			
			// Select the proper transition
			$jq .= 'fx: "' . $transition . '", ';
			
			// If the user asked for captions for this particular slideshow,
			// display them. This is not a stored setting, it is set in Parsel.
			if (in_array("captions", $options)) {
				$jq .= "after: function() { \$('.caption').html(this.alt); },";
			}
			
			// If the user has defined autoplay, set their defined delay.
			// Otherwise, the slideshow can only be navigated with buttons.
			if (!$autoplay) {
				$jq .= "timeout: 0,";
			} else {
				$jq .= "timeout: " . $delay . ", ";
			}
			
			// If the user has defined text navigation, display it.
			if ($text_nav) {
				$jq .= "next: '.ss_next',";
				$jq .= "prev: '.ss_prev',";
			} else {
				// Otherwise, clicking the image advances the slideshow.
				// This is not user-changeable.
				$jq .= "next:   '.ss_next',";
			}
			
			if ($pager) {
				$jq .= "pager: '.pager',";
			}
			
			$jq .= "});";
			
			return $jq;
		}
		
 		public static function make_nav($mode, $items, $parent_attr, $child_attr) {
 			// First build the parent attributes.
 			$attributes = self::build_attributes($parent_attr);
 			
 			// Create the opening and closing tags for the parent element.
 			$opening = "<nav " . $attributes . ">" . BR;
 			$closing = "</nav>" . BR;
 			
 			// Create the list.
 			// !! Add mode conditional
 			if ($mode == "grid" || $mode == "list") {
	 			$list = self::make_list("ul", $items, "", $child_attr);
	 		} else if ($mode == "dropdown") {
	 			$list = self::make_select($items, "", $child_attr);
	 		} else {
	 			// Throw error, mode doesn't exist.
	 		}
 			
 			// Assemble the nav
 			$html = $opening . $list . $closing;
 			
 			return $html;
 		}
 		
 		public static function make_images($mode, $items, $parent_attr, $child_attr) {
 			// First build the parent attributes.
 			$attributes = self::build_attributes($parent_attr);
 			
 			// Create the list.
 			// !! Add mode conditional
 			if ($mode == "grid" || $mode == "list") {
	 			$html = self::make_list("ul", $items, "", $child_attr);
	 		} else if ($mode == "slideshow") {
				$html = self::make_slideshow($items, $parent_attr, $child_attr);
	 		} else if (count($items) == 1) {
	 			// Throw error, mode doesn't exist.
	 			$item = array_shift($items);
	 			$item['title'] = (empty($item['title'])) ? "" : $item['title'];
	 			$html = self::make_image($item['src'], $item['title'], $child_attr);
	 		}
 			
 			return $html;
 		}
 		
		public static function make_image($src, $alt='', $attributes=array()) {
			$type = "img ";

			$html = "<" . $type . " "; 
			
			if (!empty($alt)) {
				$html .= "alt='" . $alt . "' ";
			}
			
			$html .= "src='" . $src . "' ";
			
			$html .= self::build_attributes($attributes);
			
			$html .= ">";
			
			return $html;
		}
 		
 		/** 
 		 * Helper function that builds attributes from an associative array.
 		 * 
 		 * @param				$attributes		An array of attributes.
 		 * @return			string				A string with the attributes formatted
 		 															for use in an HTML tag.
 		 *
 		 */
		 public static function build_attributes($attributes) {
		 	if (empty($attributes)) {
				$string = "";
		 	} else {
	 		 	// The string begins with a space so we can cleanly add it into
	 			// the opening tag.
	 			$string = " ";
	 			foreach ($attributes as $attr => $val) {
	 				$string .= $attr . "=\"" . $val . "\" ";
	 			}
	 		}
	 		
 			return $string;
		 }
 	}