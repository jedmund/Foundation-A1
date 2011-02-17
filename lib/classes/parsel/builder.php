<?php

/**
 * Parsel
 * builder.php
 *
 * Builder builds pages.
 *
 * TODO
 * -> Builder shouldn't have static functions. This way, we can keep pointers
 * 		for loops (ie. over images) and current page selectors (ie. for navs)
 *
 */
 
 	require_once(LIB_PATH.DS."init".DS."config.php");

 	class Builder {
 		public $pointer; // The pointer in the current loop.
 										 // Nested loops shouldn't be common, but if they are, 
 										 // we can create a new Builder object to handle it.
 		public $current; // The current page.
 	
 		public function param($class_name, $param, $id) {
 			$object->$param = "";
 			
 			if (!empty($id)) {
	 			$object = $class_name::find_by_id($id);
	 			
	 			if (strtolower($class_name) == "project") {
	 				$object->set_foundation();
	 				$object->make_thumb_path();
	 			}
	 			
	 			if (!empty($object->$param) /* && strpos($object->$param, "\n") */) {
	 				if ($param == 'description') {
		 				$object->$param = stripslashes(Markdown(str_replace(array('\r', '\n', '%0a', '%0d'), "\n", $object->$param)));
		 				$object->$param = str_replace('<p>', '<p class="parsel_description">', $object->$param);
		 			} else if ($param == 'bio') {
		 				$object->$param = stripslashes(Markdown(str_replace(array('\r', '\n', '%0a', '%0d'), "\n", $object->$param)));
		 				$object->$param = str_replace('<p>', '<p class="parsel_bio">', $object->$param);
		 			} else if ($param == 'status') {
		 				$object->$param = stripslashes(Markdown(str_replace(array('\r', '\n', '%0a', '%0d'), "\n", $object->$param)));
		 				$object->$param = str_replace('<p>', '<p class="parsel_status">', $object->$param);
		 			} else if ($param == 'blurb') {
		 				$object->$param = stripslashes(Markdown(str_replace(array('\r', '\n', '%0a', '%0d'), "\n", $object->$param)));
		 			} else if ($param == 'photo') {
		 				$object->$param = Markup::make_image($object->$param);
		 			} else {
	 					$object->$param = stripslashes($object->$param);
	 				}
				} else if ($param == 'name') {
	 				$object->$param = $object->first_name . " " . $object->last_name;
		 		} else if ($param == 'date') {
					if (!empty($object->completed)) {
						$parts = explode("-", $object->completed);
						$year = (int)array_shift($parts);
						$month = month_from_date($object->completed);
						
						if (!empty($month) && !empty($year)) {
							$object->$param = $month . " " . $year;
						} else if (!empty($month) && empty($year)) {
							$object->$param = $month;
						} else if (empty($month) && !empty($year)) {
							$object->$param = $year;
						} else {
							$object->$param = "";
						}
					}
		 		} else if ($param == 'email') {
	 				if ($class_name == 'user') {
		 				$object->$param = $object->get_email();
		 			} else {
		 				$object->$param = '';
		 			}
	 			}
	 			
	 			if (is_array(json_decode($object->$param))) {
	 					$field = Field::find_by_name($param);
		 				$keys = json_decode($object->$param);
		 				$string = '';
		 				if (count($keys) == 0) {
		 					$string = "None available";
		 				} else {
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
		 			$object->$param = $string;
		 		}
			}
 			return $object->$param;			
 		}
 		
 		public function index($class_name, $index, $oid) {
 			$object = $class_name::find_by_sequence($index, $oid);
			return (!empty($object)) ? $object->id : false;
 		}
 		
 		public function sub($class_name, $params, $oid) {
 			$object = $class_name::find_by_id($oid);
 			$param = $params[0];
 			
			$content = stripslashes(str_replace("\n\n", "\n", $object->$param));
			
			$unit = substr($params[1], 0, 1);
			$target = substr($params[1], 1);
			
			if ($unit == 'p') {
				$delimiter = "\n";

				if ($target == 1) {
					$start_pos = 0;
				} else {
					$start_pos = strnpos($content, $delimiter, $target-1);
				}
				
				if ($target == substr_count($content, $delimiter)+1) {
					$end_pos = strlen($content);
				} else {
					$end_pos = strnpos($content, $delimiter, $target);
				}				
			} else if ($unit == 's') {
				$delimiter = ".";
				
				if ($target == 1) {
					$start_pos = 0;
				} else {
					$start_pos = strnpos($content, $delimiter, $target-1);
					$start_pos += 2;
				}
				
				if ($target == substr_count($content, $delimiter)+1) {
					$end_pos = strlen($content);
				} else {
					$end_pos = strnpos($content, $delimiter, $target);
					$end_pos++;
				}
			}
			
			$sub = '';
			if ($target <= substr_count($content, $delimiter)+1) {
				$string = substr($content, $start_pos, ($end_pos-$start_pos));
				$sub = '<p class="parsel_' . $param . ' ' . $unit . $target . '">' . str_replace(array("<p>", "</p>"), "", stripslashes(Markdown($string))) . '</p>';
			}
			
			return $sub;
 		}
 		
 		public function chain($tobject, $params, $oid=0) {
			$all_params = $params;
 			array_unshift($params, $tobject);

 			$class_name = "";
 			$index = 0;
			
			if (strpos($params[1], "_")) {
 				$slug = $params[1];
 			} else {
 				$slug = '';
 			}
			
 			while ($object = self::is_pair($params, $oid, $slug)) {
 				if ($object) {
 					$last = $object;
 				}
 				$oid = (!empty($object->id)) ? $object->id : $oid;
 				$class_name = get_class($object); 
 				$params = array_splice($params, $index+2);
 			}

 			$param = array_shift($params);
 			
 			if (!is_numeric(substr($param, 0, 1)) && is_numeric(substr($param, 1))) {
 				$index = array_search($param, $all_params);
 				$sub_params = array_slice($all_params, $index-1);
 				$last = self::sub($tobject, $sub_params, $oid);
 				$param = '';
 			}
 			
 			return (!empty($param)) ? self::param($class_name, $param, $oid) : $last;
 		}
 		
 		public function social($param, $mode="", $uid=10) {
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
					$tweets = $twitter->get(4);
					$list = Markup::make_list("ul", $tweets, array("class"=>"parsel_twitter"), array("class"=>"parsel_tweet"));
				} else if ($param == 'dribbble') {
					$dribbble = new Dribbble("jedmund");
					$shots = $dribbble->get(1);
					$list = Markup::make_list("ul", $shots, array("class"=>"parsel_dribbble"), array("class"=>"parsel_shot"));
				}
				return $list;
			}
				 			
 			return $value;
 		}
 		
 		public function setting($param, $request_uri) {
 			if ($param == 'pagename') {
 				$parts = explode("/", $request_uri);
 				array_shift($parts);
 				
 				if ($parts[0] == 'projects') {
 					$project = Project::find_by_slug($parts[1]);
 					$value = $project->title;
 				} else {
 					$parts = explode('.', $parts[0]);
 					$value = ucwords($parts[0]);
 				}
 			} else if ($param == 'permalink') {
 			 	$value = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
 			} else if ($param == 'adminlink') {
 				$value = "Built on <a href='http://getfoundation.com/'>Foundation</a> &bull; <a href='/admin'>admin</a>";
 			} else if ($param == 'about') {
 				$value = "/about.php";
 			} else {
	 			$object = Setting::find_by_name($param);
	 			$value = $object->get_value();
	 		}
 			return $value;
 		}
 		
 		public function has_pairs($tobject, $params) {
 			if (!is_array($params)) {
 				$array[] = $params;
 				$params = $array;
 			}
			
 			array_unshift($params, $tobject);
 			$count = 0;

 			if (strpos($params[1], "_")) {
 				$slug = $params[1];
 			} else {
 				$slug = '';
 			}
 			
 			while ($object = self::is_pair($params, 0, $slug)) {
 				$count++;
 				$params = array_splice($params, 2);
 			}
 			
 			return $count;
 		}
 		
 		public function even_pairs($tobject, $params) {
 			$pairs = (self::has_pairs($tobject, $params))*2;
 			$pcount = count($params)+1;
 			return ($pcount-$pairs == 0) ? true : false; 
 		}
 		
 		public function is_pair($params, $id=0, $slug='') {
 			$result = false;
 			$index = 0;
 			
 			if ($index+1 < count($params)) {
	 			$pair = array_slice($params, $index, 2);
	 			
	 			$pair[0] = ucwords($pair[0]);
	 			$pair[0] = depluralize($pair[0]);

	 			if (class_exists($pair[0])) {
	 				if (is_numeric($pair[1])) {
	 					if (!empty($id)) {
		 					$object = $pair[0]::find_by_sequence($pair[1], $id);
						} else {
							$object = $pair[0]::find_by_sequence($pair[1]);
						}
	 				} else {
		 				$object = new $pair[0];
		 				$object_vars = array_keys(get_object_vars($object));
						
		 				if (!in_array($pair[1], $object_vars)) {
							if (in_array("name", $object_vars)) {
								$object = $pair[0]::find_by_name($pair[1]);
							} else if (!empty($slug)) {
								$object = $pair[0]::find_by_slug($pair[1]);
							} else if (in_array("title", $object_vars)) {
								$object = $pair[0]::find_by_title($pair[1]);
							}
						}
		 			}
		 			$result = $object;
 				}	
 			}
 			return $result;
 		}
 		
 	
 		public function build_nav($mode, $options=array()) {
 			// Set up an array to put the content in.
 			$items = array();
 			
 			// Find all of the projects, then loop over them and fill in our
 			// item with the appropriate data.
 			$projects = Project::find_all();
 			foreach ($projects as $project) {
 				$item['value'] = $project->title;
 				$item['href']  = "/projects/" . $project->slug;
 				
 				if (is_array($options)) {
	 				if (in_array("thumbnails", $options)) {
		 				$item['thumb'] = $project->path . 'thumb.png';
	 				}
	 				
	 				if (in_array("titles", $options)) {
	 					$item['title'] = stripslashes($project->title);
	 				}
	 				
	 				if (in_array("blurbs", $options)) {
	 					$item['blurb'] = stripslashes($project->blurb);
	 				}
 				}
 				
 				$item['attributes'] = self::style($mode, $options);

 				$items[] = $item;
 			}

 			$html = Markup::make_nav($mode, $items, "", "");
 			return $html;
 		}
 		
 		public function build_image($image, $mode, $options) {
 			if (empty($options)) {
 				$options = array();
 			}
 		
 			$data['value'] = $image->full;
 			$data['alt'] = $image->caption;
			
			if (in_array("small", $options)) {
				$data['src'] = $image->small;
			} else if (in_array("medium", $options)) {
				$data['src'] = $image->medium;
			} else if (in_array("large", $options)) {
				$data['src'] = $image->large;
			} else if (in_array("xlarge", $options)) {
				$data['src'] = $image->xlarge;
			} else if (in_array("full", $options)) {
				$data['src'] = $image->full;
			} else {
				$data['src'] = $image->medium;
			}
 			
 			if (!empty($options) && in_array("thumbnails", $options)) {
 				$data['thumb'] = $image->thumb;
 			}
 			
 			if (!empty($options) && in_array("caption", $options)) {
 				$data['caption'] = $image->caption;
 			}
 			
 			if (!empty($options) && in_array("links", $options)) {
 				$data['link'] = $image->link;
 			}
 			
			$data['attributes'] = $this->style($mode, $options);
 			
 			return $data;
 		}
 		
 		public function build_images($mode, $options=array(), $pid=0, $cid=0, $index=0) {
 			if (empty($index) && !empty($pid)) {
	 			// Set up an array to put the content in.
	 			$items = array();
	 			
	 			// Find all the images for the project, then loop over them and fill
	 			// in our items with the appropriate data.
	 			$images = Image::find_by_pid($pid);
	 			
	 			foreach ($images as $image) {
	 				$item = $this->build_image($image, $mode, $options);
	 				$items[] = $item;
	 			}
	 			
			} else if (!empty($index) && !empty($pid)) {
				$image = Image::find_by_sequence($index, $id);
				$item = $this->build_image($image, $mode, $options);
				$items[] = $item;
				
			} else if (!empty($cid) && empty($pid)) {
				$image = Image::find_by_id($cid);
				$item = $this->build_image($image, $mode, $options);
				$items[] = $item;
			}
			
			$html = '';
			if (!empty($items)) {
	 			$html = Markup::make_images($mode, $items, "", "");
	 		}
	 		
 			return $html;
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