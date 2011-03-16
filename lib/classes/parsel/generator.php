<?php
	/**
	 * Parsel R2
	 *
	 * generator.php
	 * Generator generates complex structures.
	 *
	 */
	 
		require_once("parsel.php");
		 
   	defined("OPEN_TAG") ? null : define("OPEN_TAG", "{");
		defined("CLOSE_TAG") ? null : define("CLOSE_TAG", "}");
		defined("EXT") ? null : define("EXT", "htm");
		
		defined("VERSION") ? null : define("VERSION", "R2");

	  class Generator {
		  /**
			 * Generates navigation.
			 *
			 * @param				$system				array			The system vars.
	  	 * @param				$parts				array			The parts of the tag.
			 * @return										mixed			The result of the operation.
			 *
			 */
	  	public function generate_nav($parts, $system) {
	  		// Get all the projects.
	  		$projects = Project::find_all();
	  		
	  		$items = array();
	  		foreach ($projects as $project) {
	  			// Prepare the project.
	  			$project->make_thumb_path();
	  			$project->set_foundation();
	  			
	  			$title_link = "";
	  			$thumb_link = "";
	  			$blurb = "";
	  			
	  			if (empty($parts['options'])) {
	  				if ($parts['mode'] == "grid" || $parts['mode'] == "mesh") {
	  					$parts['options'] = array("thumbnails");
	  				} else if ($parts['mode'] == "list" || $parts['mode'] == "dropdown") {
	  					$parts['options'] = array("titles");
	  				}
	  			}
	  			
	  			// Make the title span, blurb paragraph and the thumbnail image.
	  			if (in_array("titles", $parts['options']) || in_array("title", $parts['options'])) {
	  				$content = htmlentities($project->title);
		  			$title = Marker::make_span($content, array("class"=>"parsel_project_title"));
		  			$title_link = Marker::make_link($project->permalink(), $title);
		  		}
		  		
		  		if (in_array("blurbs", $parts['options']) || in_array("blurb", $parts['options'])) {
		  			$content = htmlentities($project->blurb);
						$blurb = Marker::make_paragraph($content, array("class"=>"parsel_project_blurb"));
		  		}
		  		
		  		if (in_array("thumbnails", $parts['options']) || in_array("thumbnail", $parts['options'])) {
		  			$thumb = Marker::make_image($project->thumb, $project->title, array("class"=>"parsel_project_thumb"));
		  			$thumb_link = Marker::make_link($project->permalink(), $thumb);
		  		}
					
	  			$item['content'] = $thumb_link . $title_link . $blurb;
	  			$items[] = $item;
	  		}

	  		// Make the construct and its class.
				$class = "parsel_" . $parts['mode'] . "_nav";
				
				if ($parts['mode'] == "dropdown") {
					$construct = Marker::make_select($items, array("class"=>$class));
				} else {
					$construct = Marker::make_list("ul", $items, array("class"=>$class));
				}
				
				// Make the nav element and return it.
				$html = Marker::make_nav($construct);
				
				
	  		return $html; 
	  	}
	  	
	  	/**
			 * Generates a slideshow.
			 *
	  	 * @param				$parts				array			The parts of the tag.
			 * @param				$system				array			The system vars.
			 * @return										mixed			The result of the operation.
			 *
			 */
	  	public function generate_slideshow($parts, $system) {
	  		// First, build the images.
	  		$builder = new Builder;
	  		
	  		// Determine if we need to make a text nav.
				$text_nav = Setting::find_by_name('slideshow_text_nav');
				$text_nav = $text_nav->get_value();
				
				// Make the slideshow nav if necessary.
				$nav = "";
				if ($text_nav) {
					// Get the label values.
					$text_nav_prev = Setting::find_by_name('slideshow_text_nav_prev');
					$text_nav_next = Setting::find_by_name('slideshow_text_nav_next');
					$text_nav_prev = $text_nav_prev->get_value();
					$text_nav_next = $text_nav_next->get_value();
					
					// Make the links for the navigation.			
					$prev_link = Marker::make_link("", $text_nav_prev);
					$next_link = Marker::make_link("", $text_nav_next);

					// Add the links to the parent array.
					$prev['content'] = $prev_link;
					$next['content'] = $next_link;
					
					// Add the classes of the parent.			
					$prev['attributes']['class'] = "parsel_slideshow_prev";
					$next['attributes']['class'] = "parsel_slideshow_next";
					
					$children = array($prev, $next);
					$list = Marker::make_list("ul", $children);
					$nav  = Marker::make_nav($list, array("class"=>"parsel_slideshow_nav"));
				}

	  		
	  		$images = $builder->build_images($parts, $system['obj_id'], array("class"=>"parsel_slideshow"));
	  		
	  		$html = "";
	  		if (!empty($images)) {
	  			echo htmlspecialchars($images);
	  			if (is_array($parts['options']) && in_array("post-nav", $parts['options'])) {
		  			$html = $images . $nav;
		  		} else {
		  			$html = $nav . $images;
		  		}
				}
				
				return $html;
	  	}
	  	
	  	/**
			 * Generates social widgets.
			 *
	  	 * @param				$parts				array			The parts of the tag.
			 * @param				$system				array			The system vars.
			 * @return										mixed			The result of the operation.
			 *
			 */
	  	public function generate_widget($parts, $system) {
	  		$function = "generate_" . $parts['filter_group'];
	  		$widget = $this->$function($parts);
	  		
	  		return $widget;
	  	}
	  	
	  	
	  	/**
			 * Helper function to build Twitter widgets.
			 *
	  	 * @param				$parts				array			The parts of the tag.
			 * @return										mixed			The result of the operation.
			 *
			 */
	  	public function generate_twitter($parts) {
	  		$handle = $this->get_handle("twitter");
	  		$amount = (empty($parts['subset'])) ? 3 : $parts['subset'];
	  		
	  		// Make a new Twitter object and get the default number of tweets.
	  		$twitter = new Twitter($handle);
	  		$tweets = $twitter->get($amount);
	  		
	  		$items = array();
	  		foreach ($tweets as $tweet) {
	  			$date = Marker::make_span($tweet['date'], array("class"=>"parsel_date"));
	  			$date_link = Marker::make_link($tweet['href'], $date);
	  			$text = Marker::make_paragraph($tweet['text']);
	  			
	  			$content = $text . $date_link;
	  			$item['content'] = $content;
	  			$item['attributes']['class'] = "parsel_tweet"; 
	  			$items[] = $item; 
	  		}
	  		
	  		$html = Marker::make_list("ul", $items, array("class"=>"parsel_twitter"));
				return $html;
	  	}
	  	
	  	/**
			 * Helper function to build Dribbble widgets.
			 *
	  	 * @param				$parts				array			The parts of the tag.
			 * @return										mixed			The result of the operation.
			 *
			 */
	  	public function generate_dribbble($parts) {
	  		$handle = $this->get_handle("dribbble");
	  		$amount = (empty($parts['subset'])) ? 2 : $parts['subset'];
	  		
	  		// Make a new Twitter object and get the default number of tweets.
	  		$dribbble = new Dribbble($handle);
	  		$shots = $dribbble->get($amount);
	  		
	  		$items = array();
	  		foreach ($shots as $shot) {
	  			$image = Marker::make_image($shot['thumb'], $shot['title']);
	  			$image_link = Marker::make_link($shot['href'], $image);
	  			
	  			$content = $image_link;
	  			$item['content'] = $content;
	  			$item['attributes']['class'] = "parsel_shot"; 
	  			$items[] = $item; 
	  		}
	  		
	  		$html = Marker::make_list("ul", $items, array("class"=>"parsel_dribbble"));
				return $html;
	  	}
	  	
	  	/**
			 * Helper function to get handles for a service.
			 *
			 * @param				$service			string		The desired service name.
	  	 * @param				$uid					int				The user ID to lookup.
			 * @return										string		The user's handle.
			 * @return										boolean		False if no handle was provided.
			 *
			 */
			public function get_handle($service, $uid=10) {
				$user = User::find_by_id($uid);
	  	 	$social = json_decode($user->social);
	  	 	
	  	 	return $social->$service;
			}
			
			/**
			 * Helper function to build the title for the current page.
			 *
	  	 * @param				$parts				array			The parts of the tag.
			 * @param				$system				array			The system vars.
			 * @return										mixed			The result of the operation.
			 *
			 */
	  	public function generate_title($parts, $system) {
	  		$setting = Setting::find_by_name("title");
	  		$site_title = $setting->get_value();
	  		
	  		$setting = Setting::find_by_name("title_delimiter");
	  		$delimiter = $setting->get_value();
	  		
	  		
	  		$content = "";	  		
	  		if ($system['request_uri'] == "/" || $system['request_uri'] == "/home") {
	  			$content = $site_title;
	  		} else if (strpos($system['request_uri'], "projects/") && !empty($system['obj_id'])) {
					$project = Project::find_by_id($system['obj_id']);
	  			$content = $project->title . " " . $delimiter . " " . $site_title;
	  		} else if ($system['request_uri'] == "/projects") {
	  			$content = "Projects " . $delimiter . " " . $site_title;
				} else if ($system['request_uri'] == "/about") {
	  			$content = "About " . $delimiter . " " . $site_title;
	  		} else if ($system['request_uri'] == "/contact") {
	  			$content = "Content " . $delimiter . " " . $site_title;
	  		}

	  		return $content;
	  	}

	  }