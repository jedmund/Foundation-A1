<?php
	/**
	 * Parsel R2
	 *
	 * marker.php
	 * Marker marks up content in HTML.
	 *
	 */
 
	require_once("parsel.php");
		
	// These constants should help clean up the source code a bit.
	// defined(BR) ? null : define(BR, "\n");
	// defined(TAB) ? null : define(TAB, "\t");

	class Marker {		
		/**
		 * A helper function to convert tags to make functions.
		 * 
		 * @param				$tag				 The tag to convert.
		 * @return			string			 The name of the function.
		 *
		 */
		public static function make_func_name($tag) {
			$func_name = false;
			
			switch ($tag) {
				case "a": 	 	 $func_name = "make_link";			break;
				case "div":	 	 $func_name = "make_div";	 			break;
				case "img": 	 $func_name = "make_image";			break;
				case "li":		 $func_name	= "make_list_item"; break;
				case "option": $func_name = "make_option";  	break;
				case "p":		 	 $func_name = "make_paragraph"; break;
				case "span": 	 $func_name = "make_span"; 			break;
			}
			
			return $func_name;
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
		public static function make_children($tag, $items, $attributes=array()) {
			$children = "";

			foreach ($items as $item) {
				if (empty($item['attributes'])) {
					$item['attributes'] = array();
				}
			
				// Determine what kind of element we are making.
				$func_name = self::make_func_name($tag);

				// Call the appropriate function for our item.
				if ($func_name == "make_link") {
					$child = call_user_func_array(array("self", $func_name), array($item['href'], $item['content'], $item['attributes']));
				} else if ($func_name == "make_image") {
					$child = call_user_func_array(array("self", $func_name), array($item['source'],  $item['caption'], $item['attributes']));
				} else {
					$child = call_user_func_array(array("self", $func_name), array($item['content'], $item['attributes']));
				}
				
				$children .= $child;
			}
			
			return $children;
		}
		
		/** 
		 * <a>
		 * Makes an anchor in HTML.
		 *
		 * @param			$link					The href element of the anchor
		 * @param			$content			The content of the anchor
		 * @param			$attributes 	The attributes of the link as an array
		 * @return		string				A string containing the anchor parts
		 *
		 */
		public static function make_link($link, $content, $attributes=array()) {
			// Create the opening tag.
			$opening = "<a";

			// Add the `parsel_link` class to the attributes and build them.
			if (!empty($attributes["class"])) {
				$attributes["class"] = $attributes["class"] . " parsel_link";
			} else {
				$attributes["class"] = "parsel_link";
			}
			
			$link_attr = self::make_attributes($attributes);
			$opening  .= $link_attr;
	
			// Add the link to the anchor and close the opening tag.
			if (!empty($link)) {
				$opening .= " href=\"" . $link . "\">";
			} else {
				$opening .= ">";
			}
			
			// Create the closing tag.
			$closing = "</a>";
			
			// If the content is an array, it contains children, so we should
			// call make_children() on it.
			if (is_array($content)) {
				$content = self::make_children($content["tag"], $content["children"], $content["attributes"]);
			}
			
			// Encode special characters
			// $content = htmlspecialchars($content);
			
			// Put everything together and return.
			return $opening . $content . $closing;
		}

		/** 
		 * <div>
		 * Makes a div in HTML.
		 *
		 * @param			$content			The content of the div.
		 * @param			$attributes		The attributes of the div as an array
		 * @return		string				A string containing the div parts
		 *
		 */
		public static function make_div($content, $attributes=array()) {
			// Create the opening tag.
			$opening = "<div";
			
			// Add the `parsel_div` class to the attributes and build them.
			// Close the opening tag.
			if (!empty($attributes["class"])) {
				$attributes["class"] = $attributes["class"] . " parsel_div";
			} else {
				$attributes["class"] = "parsel_div";
			}
			
			$span_attr = self::make_attributes($attributes);
			$opening .= $span_attr . ">";
			
			// Create the closing tag.
			$closing = "</div>";
			
			// If the content is an array, it contains children, so we should
			// call make_children() on it.
			if (is_array($content)) {
				$content = self::make_children($content["tag"], $content["children"], $content["attributes"]);
			} else {
				// Encode special characters
				// $content = htmlspecialchars($content);
			}
			
			// Put everything together and return.
			return $opening . $content . $closing;
		}
		
		/**
		 * <img> group
		 * Makes a group of images in HTML.
		 *
		 * @param			$src					The source of the image.
		 * @param			$alt					The alternate text of the image.
		 * @param			$attributes		The attributes of the image as an array
		 * @return		string				A string containing the image parts
		 *
		 */
		public static function make_images($mode, $items, $parent_attr, $child_attr) {
			// First build the parent attributes.
			$attributes = self::make_attributes($parent_attr);
			
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

		
		/**
		 * <img>
		 * Makes an image in HTML.
		 *
		 * @param			$src					The source of the image.
		 * @param			$alt					The alternate text of the image.
		 * @param			$attributes		The attributes of the image as an array
		 * @return		string				A string containing the image parts
		 *
		 */
		public static function make_image($source, $alt="", $attributes=array()) {
			// Create the tag.
			$tag = "<img ";
			
			if (!empty($alt)) {
				$tag .= "alt='" . $alt . "' ";
			}
			
			// Add the `parsel_image` class to the attributes and
			// build them, then add to the tag.
			if (!empty($attributes['class'])) {
				$attributes['class'] = $attributes['class'] . " parsel_image";
			} else {
				$attributes['class'] = "parsel_image";
			}
			
			$attributes = self::make_attributes($attributes);
			$tag .= $attributes;
			
			// Add the image's source and close the opening tag.
			$tag .= "src='" . $source . "'>";
			
			return $tag;
		}
		
		/** 
		 * <li>
		 * Makes a list item in HTML.
		 *
		 * @param			$content			The content of the list item
		 * @param			$attributes		The attributes of the list item as an array
		 * @return		string				A string containing the list item parts
		 *
		 */
		public static function make_list_item($content, $attributes=array()) {
			// Create the opening tag.
			$opening = "<li";

			// Add the `parsel_list_item` class to the attributes and build 
			// them.
			if (empty($attributes['class'])) {
				$attributes['class'] = "parsel_list_item";
			} else {
				$attributes['class'] .= " parsel_list_item";
			}
			
			$item_attr = self::make_attributes($attributes);
			$opening .= $item_attr . ">";
			
			// Create the closing tag.
			$closing = "</li>";
			
			// If the content is an array, it contains children, so we should
			// call make_children() on it.
			if (is_array($content)) {
				$content = self::make_children($content["tag"], $content["children"], $content["attributes"]);
			}
			
			// Encode special characters
			// $content = htmlspecialchars($content);
			
			// Put everything together and return.
			return $opening . $content . $closing;
		}
		
		/**
		 * <nav>
		 * Makes a nav in HTML.
		 *
		 * @param			$content			The content of the nav.
		 * @param			$attributes		The attributes of the nav as an array
		 * @return		string				A string containing the nav parts
		 *
		 */
		public static function make_nav($content, $attributes=array()) {
			// Create the opening tag.
			$opening = "<nav";
			
			// Add the `parsel_nav` class to the attributes and build 
			// them.
			if (!empty($attributes["class"])) {
				$attributes["class"] = $attributes["class"] . " parsel_nav";
			} else {
				$attributes["class"] = "parsel_nav";
			}
			
			$span_attr = self::make_attributes($attributes);
			$opening .= $span_attr . ">";
			
			// Create the closing tag.
			$closing = "</nav>";

			// Encode special characters
			// $content = htmlspecialchars($content);
			
			// Put everything together and return.
			return $opening . $content . $closing;
		}
		
		/** 
		 * <option>
		 * Makes a option in HTML.
		 *
		 * @param			$content			The content of the option
		 * @param			$attributes		The attributes of the option as an array
		 * @return		string				A string containing the option parts
		 *
		 */
		public static function make_option($content, $attributes=array()) {
			// Create the opening tag.
			$opening = "<option";
			
			// Add the `parsel_option` class to the attributes and build 
			// them.
			if (!empty($attributes["class"])) {
				$attributes["class"] = $attributes["class"] . " parsel_option";
			} else {
				$attributes["class"] = "parsel_option";
			}
			
			$span_attr = self::make_attributes($attributes);
			$opening .= $span_attr . ">";
			
			// Create the closing tag.
			$closing = "</option>";
			
			// If the content is an array, it contains children, so we should
			// call make_children() on it.
			if (is_array($content)) {
				$content = self::make_children($content["tag"], $content["children"], $content["attributes"]);
			}
			
			// Encode special characters
			// $content = htmlspecialchars($content);
			
			// Put everything together and return.
			return $opening . $content . $closing;
		}
		
		/** 
		 * <p>
		 * Makes a paragraph in HTML.
		 *
		 * @param			$content			The content of the paragraph.
		 * @param			$attributes		The attributes of the span as an array
		 * @return		string				A string containing the span parts
		 *
		 */
		public static function make_paragraph($content, $attributes=array()) {
			// Create the opening tag.
			$opening = "<p";
			
			// Add the `parsel_paragraph` class to the attributes and 
			// build them. Also close the opening tag.
			if (!empty($attributes["class"])) {
				$attributes["class"] = $attributes["class"] . " parsel_paragraph";
			} else {
				$attributes["class"] = "parsel_paragraph";
			}

			$p_attr = self::make_attributes($attributes);
			$opening .= $p_attr . ">";
			
			// Create the closing tag.
			$closing = "</p>";
			
			// Encode special characters
			// $content = htmlspecialchars($content);
			
			// Put everything together and return.
			return $opening . $content . $closing;
		}
		
		/** 
		 * <script>
		 * Makes a script in HTML.
		 *
		 * @param			$content			The content of the script, optional.
		 * @param			$source				The source of the script, optional.
		 * @return		string				A string containing the script parts
		 *
		 */
		public static function make_script($content="", $source="") {
			// Create the opening tag.
			$opening = "<script type=\"text/javascript\"";
			
			// Create the closing tag.
			$closing = "</script>";

			// If we have a source, add it, otherwise, add the content.
			$tag = "";
			if (!empty($source)) {
				$source = " src=\"" . $source . "\"";
				$tag = $opening . $source . ">" . $closing;
			} else if (!empty($content)) {
				$opening .= ">";
				$tag = $opening . $content . $closing;
			}
			
			return $tag;
		}

			/** 
			 * <select>
			 * Makes an dropdown list in HTML.
			 !! Limitation: This method doesn't allow for semantics on individual items.
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
			// Add the `parsel_select` class to every select.
			$parent_attr['class'] .= ' parsel_select';
				$attributes = self::make_attributes($parent_attr);
				
				// Create the opening and closing tags for the parent element.
				$opening = "<" . $type . $attributes . ">";
				$closing = "</" . $type . ">";
				
				// Now, build the children attributes.
				// Add the `parsel_option` class to every option.
				$child_attr['class'] .= ' parsel_option';
				$attributes = self::make_attributes($child_attr);
				
				// Create the children elements.
				$children = self::make_children("option", $items, $attributes);
				 			
				// Put the list together.
				$html = $opening . $children . $closing;
				return $html;
		}
		
		/** 
		 * <span>
		 * Makes a span in HTML.
		 *
		 * @param			$content			The content of the span.
		 * @param			$attributes		The attributes of the span as an array
		 * @return		string				A string containing the span parts
		 *
		 */
		public static function make_span($content, $attributes=array()) {
			// Create the opening tag.
			$opening = "<span";
			
			// Add the `parsel_span` class to the attributes and build them.
			if (!empty($attributes["class"])) {
				$attributes["class"] = $attributes["class"] . " parsel_span";
			} else {
				$attributes["class"] = "parsel_span";
			}
			
			$span_attr = self::make_attributes($attributes);
			$opening .= $span_attr . ">";
			
			// Create the closing tag.
			$closing = "</span>";
			
			// If the content is an array, it contains children, so we should
			// call make_children() on it.
			if (is_array($content)) {
				$content = self::make_children($content["tag"], $content["children"], $content["attributes"]);
			}
			
			// Encode special characters
			// $content = htmlspecialchars($content);
			
			// Put everything together and return.
			return $opening . $content . $closing;
		}
		
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
		public static function make_list($type, $items, $parent_attr=array(), $child_attr=array()) {
			// First build the parent attributes.
			// Add the `parsel_list` class to every list.
			if (empty($parent_attr['class'])) {
				$parent_attr['class']  = "parsel_list";
			} else {
				$parent_attr['class'] .= " parsel_list";
			}
			
			// Make the attributes.
			$attributes = self::make_attributes($parent_attr);
			
			// Create the opening and closing tags for the parent element.
			$opening = "<" . $type . $attributes . ">";
			$closing = "</" . $type . ">";
			
			// Create the children elements.
			$children = self::make_children("li", $items);
			 			
			// Put the list together.
			$html = $opening . $children . $closing;
			return $html;
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
					
					$nav = "<nav class='ss_nav'>";
					$nav .= "<ul>";
					$nav .= "<li class='ss_prev'><a>" . $text_nav_prev . "</a></li>";
					$nav .= "<span class='ss_separator'>/</span>";
					$nav .= "<li class='ss_next'><a>" . $text_nav_next . "</a></li>";
					$nav .= "</ul>";
					$nav .= "<div class='pager'></div>";
					$nav .= "</nav>";
				}
			
				// Build the parent attributes
				$attributes = self::make_attributes($parent_attr);
				
				// Create the opening and closing tags for the parent element.
				$opening = "<div class='parsel_slideshow ss_next' " . $attributes . ">";
				$closing = "</div>";
				
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
					
		/** 
		 * Helper function that builds attributes from an associative array.
		 * 
		 * @param				$attributes		An array of attributes.
		 * @return			string				A string with the attributes formatted
		 															for use in an HTML tag.
		 *
		 */
		public static function make_attributes($attributes) {
			if (empty($attributes)) {
				$string = "";
		 	} else {
	 		 	// The string begins with a space so we can cleanly add it into
	 			// the opening tag.
	 			$string = " ";
	 			if (is_array($attributes)) {
		 			foreach ($attributes as $attr => $val) {
		 				$string .= $attr . "=\"" . $val . "\" ";
		 			}
				}
	 		}
			return " " . trim($string);
		 }
		}