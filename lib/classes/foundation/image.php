<?php

/**
 * Image.php
 *
 * This class helps manage the images of a project. 
 *
 */
 
 	require_once(LIB_PATH.DS."init".DS."config.php");
	
	class Image {
		protected static $table = "images";
		protected static $fields = array("id", "pid", "full", "thumb", "small", "medium", "large", "xlarge", "caption", "default_image", "link", "sequence", "coords");
		protected static $safe_fields = "id, pid, full, thumb, small, medium, large, xlarge, caption, default_image, link, sequence, coords";
		
		public $id;
		public $pid;
		public $full;
		public $thumb;
		public $small;
		public $medium;
		public $large;
		public $xlarge;
		public $caption;
		public $default_image;
		public $link;
		public $sequence;
		public $coords;
		
		
		/**
		 * Convenience function that translates the size variable names
		 * into their abbreviated equivalents.
		 *
		 * @param				$size				The size name, short or long
		 * @return			string			The converted string.
		 *
		 */
		public function convert_name($size) {
			$result = "";
			
			if (strlen($size) < 3) {
				// If the string length is less than three, we're converting up.
				switch ($size) {
					case "sm": $result = "small";  break;
					case "md": $result = "medium"; break;
					case "lg": $result = "large";  break;
					case "xl": $result = "xlarge"; break;
				}
			} else {
				// Otherwise, we're converting down.
				switch ($size) {
					case "small":  $result = "sm"; break;
					case "medium": $result = "md"; break;
					case "large":  $result = "lg"; break;
					case "xlarge": $result = "xl"; break;
				}
			}
			
			return $result;
		}
		
		/**
		 * Checks the path for a certain size of the image.
		 *
		 */
		public function check_path($size) {
			// Assume that the file does not exist.
			$exists = false;
			
			if (is_file(PUBLIC_PATH.DS.$this->$size)) {
				// If the file DOES exist, then we set $exists to true,
				// and that will be returned.
				$exists = true;
			} else if ($size != 'full' && !is_file(PUBLIC_PATH.DS.$this->$size)) {
				// If the size doesn't exist, and the file is not the full image,
				// we should check to see whether the full file exists.
				if ($this->check_path('full')) {
					// If it does, then we can regenerate the broken size.
					list($width, $height) = getimagesize(PUBLIC_PATH.DS.$this->full);
										
					if ($path = $this->generate_size($width, $this->convert_name($size))) {
						// Resize was successful! Save to database.
						$search = strpos($size, $user->username);
						$pos = $search - strlen("/content/");
						$size = substr($size, strpos($size, "content"));
						
						$exists = true;
						$this->$size = $path;
						$this->save();
					} else {
						// The image might be too small for this size.
						// Not sure what to do here.
					}
				} else {
					// The image was deleted in the full image check.
					// Not sure what to do here.
				}
			} else if ($size == 'full' && !is_file(PUBLIC_PATH.DS.$this->$size)) {
				// Otherwise, we delete the image, because its useless.
				// We also notify the user of the change.
				$this->delete();
				$project = Project::find_by_id($this->pid);
				
				$notice = new Notice;
				$notice->type = 1;
				$notice->text = "Foundation detected that there were missing image files in the project " . $project->title . " and deleted them automatically. Please try to re-upload the image.";
				$notice->save();

			}
			
			return $exists;
		}
		
		/**
		 * Calculates whether an image size can be generated and
		 * if it can, generates a size of an image and returns its path.
		 * If it cannot, the function returns false.
		 *
		 * Size can be sm, md, lg, or xl
		 *
		 * @param				width				int
		 * @param				size				string
		 *
		 */
		public function generate_size($width, $size) {			
			// Get the actual size value from the Settings object.
			$name = "image_"  . $size . "_width";
			$setting = Setting::find_by_name($name);
			$size_value = $setting->get_value();
			
			if ($width > $size_value) {
				$path = $this->scale($size, $size_value);
			} else {
				$path = false;
			}
			
			return $path;
		}
		
		/** 
		 * Generates all sizes of an image.
		 *
		 */
		public function generate_sizes() {
			$project= Project::find_by_id($this->pid);
			$user = User::find_by_id($project->uid);
			
			list($sm, $md, $lg, $xl) = Setting::image_sizes();
			list($width, $height) = getimagesize(PUBLIC_PATH.DS.$this->full);
			
			// Generate sizes
			$sizes['small']  = $this->generate_size($width, "sm");
			$sizes['medium'] = $this->generate_size($width, "md");
			$sizes['large']  = $this->generate_size($width, "lg");
			$sizes['xlarge'] = $this->generate_size($width, "xl");
			
			foreach ($sizes as $key=>$size) {
				$search = strpos($size, $user->username);
				$pos = $search - strlen("/content/");
				$size = DS . substr($size, strpos($size, "content"));
				$this->$key = $size;
			}
		}
		
		/** 
		 * Saves the image as a JPG at the given size, with the given quality.
		 *
		 * @param				$mode				What size we are scaling the image to.		 
		 * @param				$w					The desired width.		 
		 * @param				$h					The desired height.		 
		 * @param				$q					The desired JPG quality.		 
		 * @return				path				The path to the newly created image.		 
		 *
		 */
		public function scale($mode, $w=0, $h=0, $q=80) {
			if (empty($w) || empty($h)) {
				// Convert the local path of the full image to an absolute path,
				// and get its width and height.
				$full = PUBLIC_PATH.DS.$this->full;
				
				list($width, $height, $type, $attr) = getimagesize($full);

				// Create a GD image object based on our source image's extension.
				switch ($type) {
				  case 1: $source = imagecreatefromgif($full); break;
				  case 2: $source = imagecreatefromjpeg($full);  break;
				  case 3: $source = imagecreatefrompng($full); break;
				  default:  trigger_error('Unsupported filetype!', E_USER_WARNING); break;				
				}
				
				if ($source) {
					// Determine the width and height of the source image.
					$sx = imagesx($source);
					$sy = imagesy($source);
					
					// Determine the scaling ratio.
					if (!empty($w)) {
						$r = $width / $w;
					} else if (!empty($h)) {
						$r = $height / $h;
					}
					
					// Generate the destination image's width and height with the 
					// ratio.
					$dx = (!empty($w)) ? $w : $width / $r;
					$dy = (!empty($h)) ? $h : $height / $r;
					
					$dest = imagecreatetruecolor($dx, $dy);
	
					if (($type == 1) || ($type == 3)) {
					  imagealphablending($dest, false);
					  imagesavealpha($dest,true);
					  $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
					  imagefilledrectangle($dest, 0, 0, $dx, $dy, $transparent);
					}
					
					imagecopyresampled($dest, $source, 0, 0, 0, 0, $dx, $dy, $sx, $sy);				
					
					$name = explode(".", basename($this->full));
					$ext	= $name[1];
					$name = $name[0];
					$file = str_replace($name . "." . $ext, $name . "_" . $mode . "." . $ext, $full);

					switch ($type) {
					  case 1: imagegif($dest, $file); break;
					  case 2: imagejpeg($dest, $file);  break;
					  case 3: imagepng($dest, $file); break;
					  default:  trigger_error('Failed resize image!', E_USER_WARNING);  break;
					}
	
					chmod($file, 0777);
					return $file;
				}
			}
		}

		/**
		 * Makes a thumbnail for the image according to coordinates.
		 *
		 * @param				$coords			Optional array of coordinates.
		 * @param				path				The path to the newly created image.
		 *
		 */
		public function make_thumbnail($coords=array()) {
			// If a custom coordinate set hasn't been specified, use what is in
			// the class as an instance variable.
			if (empty($coords)) {
				$coords = json_decode(stripslashes($this->coords));
			}
			
			// Get the settings for thumbnail width and height.
			$setting = Setting::find_by_name('thumbnail_width');
			$settings['w'] = $setting->get_value();
			
			$setting = Setting::find_by_name('thumbnail_height');
			$settings['h'] = $setting->get_value();
			
			$ext = substr(strrchr($this->full, '.'), 1);
			
				switch ($ext) {
					case 'jpg': 
						$source = imagecreatefromjpeg(PUBLIC_PATH.$this->full);
					break;
					
					case 'jpeg':
						$source = imagecreatefromjpeg(PUBLIC_PATH.$this->full);
					break;
					
					case 'gif':
						$source = imagecreatefromgif(PUBLIC_PATH.$this->full);
					break;
					
					case 'png':
						$source = imagecreatefrompng(PUBLIC_PATH.$this->full);
					break;
				}
			
			$dst_r = imagecreatetruecolor($settings['w'], $settings['h']);
			imagecopyresampled($dst_r, $source, 0, 0, $coords->x, $coords->y, $settings['w'], $settings['h'], $coords->w, $coords->h);
			
			// Create the filename.
			$mode = "thumb";
			$name = explode(".", basename($this->full));
			$name = $name[0];
			$file = str_replace($name . "." . $ext, $name . "_" . $mode . "." . $ext, $this->full);
			$path = PUBLIC_PATH.$file;
			
			imagejpeg($dst_r, $path, 100);
			
			$this->thumb = $file;
			$this->save();
		}
		
		/** 
		 * Convenience function that performs all of the necessary
		 * actions to delete an image entirely from the server.
		 *
		 */
		public function erase() {
			if ($this->full)   @unlink(PUBLIC_PATH.$this->full);
			if ($this->xlarge) @unlink(PUBLIC_PATH.$this->xlarge);
			if ($this->large)	 @unlink(PUBLIC_PATH.$this->large);
			if ($this->medium) @unlink(PUBLIC_PATH.$this->medium);
			if ($this->small)	 @unlink(PUBLIC_PATH.$this->small);
			if ($this->thumb)  @unlink(PUBLIC_PATH.$this->thumb);

			$this->delete();
		}

		/** 
		 * Returns an array of objects containing safe data about a specfic
		 * image based on its tags.
		 * 
		 * @param				$subset 		string					The sequencee of the object we'll fetch.
		 * @param				$pid				int							The Project ID to look in.		 
		 * @return									array, object		The object.
		 *
		 */
		public static function find_by_tag($tag, $pid=0) {
			$tag = Tag::find_by_name($tag);
			$result = ImageTagAssociation::find_by_tid($tag->id);
			
			$images = array();
			if (!empty($result)) {
				foreach ($result as $assoc) {
					$image = Image::find_by_id($assoc->iid);
					
					if (!empty($pid)) {
						if ($pid == $image->pid) {
							$images[] = $image;
						}
					}
				} 
			}
						
			return self::sort($images);
		}
		
		/**
		 * Sorts an array of image objects by their sequence.
		 *
		 * @param				$images			Array						The array of Image objects.
		 * @return									Array						The sorted array of Image objects.
		 *
		 */
		public static function sort($images) {
			$sorted = array();
			$current = 0;
			
			foreach ($images as $image) {
				$sorted[$image->sequence] = $image;
			}
			
			sort($sorted);
			
			return $sorted;
		}

		/**
		 * Returns an array of objects containing safe data about a specfic
		 * image based on a sequence subset.
		 * 
		 * @param				$subset 		string					The sequencee of the object we'll fetch.
		 * @param				$pid				int							The Project ID to look in.		 
		 * @return									array, object		The object.
		 *
		 */
		public static function find_in_subset($subset, $pid) {
			$sql = "SELECT " . self::$safe_fields . " FROM " . self::$table;
   		$sql .= " WHERE sequence IN (" . $subset . ")";
   		$sql .= " AND pid = " . $pid;
   		$sql .= " ORDER by sequence ASC";
   		
			$result = self::find_by_sql($sql);
			return (count($result) > 1) ? $result : array_shift($result);
		}

		/** 
		 * Returns an array of objects containing safe data about a specfic
		 * image based on its sequence.
		 * 
		 * @param				$sequence		The sequencee of the object we'll fetch.		 
		 * @param				$fields			The fields which we want to get.
		 * @return			$result			The object.
		 *
		 */
		public static function find_by_sequence($sequence, $pid=0, $fields=0) {
			$sql = "SELECT " . self::$safe_fields . " FROM " . self::$table;
   		$sql .= " WHERE sequence = '" . $sequence . "'";
   		$sql .= (!empty($pid)) ? " AND pid = " . $pid : "";
   		$sql .= " LIMIT 1";
						
			$result = self::find_by_sql($sql);
			return !empty($result) ? array_shift($result) : false;
		}
		
		/** 
		 * Returns an array of objects containing safe data about a specfic field
		 * based on their ID.
		 * 
		 * @param				$pid				The project ID whose images we want to fetch.		 
		 * @param				$fields			The fields which we want to get.
		 * @return			$result			Object containing field information.
		 *
		 */
		public static function find_by_pid($pid, $fields=0) {
			global $database;
   		$sql = "SELECT " . self::$safe_fields . " FROM " . self::$table . 
   					 " WHERE pid = " . $pid . " ORDER BY sequence ASC";
						
			$result = self::find_by_sql($sql);
			return $result;
		}

		
		/** 
		 * Returns an array containing safe data about all images.
		 * 
		 * @return			$result			Array of objects containing image information.
		 *
		 */
		public static function find_all($fields=0) {
			$sql = "SELECT " . self::$safe_fields . " FROM " . self::$table . 
						 " ORDER BY id ASC";
						
			return self::find_by_sql($sql);
		}
					
		/** 
		 * Returns an array of objects containing safe data about a specfic field
		 * based on their ID.
		 * 
		 * @param				$id					The image ID to fetch.		 
		 * @param				$fields			The fields which we want to get.
		 * @return			$result			Object containing field information.
		 *
		 */
		public static function find_by_id($id, $fields=0) {
			global $database;
   		$sql = "SELECT " . self::$safe_fields . " FROM " . self::$table . 
   					 " WHERE id = " . $id . " LIMIT 1";
						
			$result = self::find_by_sql($sql);
			return !empty($result) ? array_shift($result) : false;
		}
			
			
		// Make method find(), get(), whatever, that calls $db->get() with parameters
		// but also instantiates each object afterwards.
		// Then, replace the $db->get()s in this class with this method.
		// This will replace find_by_sql().
				
	  /** 
		 * Performs the given SQL query and returns an array of objects of its results.
		 * 
		 * @param			$sql					The SQL query to perform.
		 * @return		$result				Array of objects containing image information.
		 *
		 */
		public static function find_by_sql($sql="") { 
			global $db;
			
			$result = $db->query($sql);
			
			$object_array = array();
			while ($row = $db->fetch_array($result)) {
				$object_array[] = self::instantiate($row);
			}
			return $object_array;
		}		
					
		/** 
		 * Counts how many images are currently in the database.
		 * 
		 * @return		integer			Number of images.
		 *
		 */
		public static function count_all() {
			global $db;
			
			$sql = "SELECT COUNT(*) FROM " . self::$table;
			$result = array_shift($db->query($sql));
			return $result['COUNT(*)'];
		}
		
		/** 
		 * Turns a set of data into an object.
		 * 
		 * @param			$record				A row of database information.
		 * @return		$object				An object containing the database information
		 *
		 */
		private function instantiate($record) {
			$object = new self;
			foreach ($record as $attribute=>$value) {
				if ($object->has_attribute($attribute)) {
			 		$object->$attribute = $value;
				}
			}
			return $object;
		}
			
		/** 
		 * Checks if the object has the given attribute.
		 * 
		 * @param			$attribute		A string representing an attribute name, probably a database column title.
		 * @return		$bool					True if the attribute exists.
		 *
		 */
		private function has_attribute($attribute) {
			$object_vars = $this->attributes();
			return array_key_exists($attribute, $object_vars);
		}
		
		/** 
		 * Creates an array of the attributes of the current object.
		 * 
		 * @return		$attributes		An array of attributes.
		 *
		 */
		protected function attributes() {
			$attributes = array();
			foreach(self::$fields as $field) {
				if (property_exists($this, $field)) {
					$attributes[$field] = $this->$field;
				}
			}	
			return $attributes;
		}
		
		/** 
		 * Sanitizes the values of the attributes of the current object.
		 * 
		 * @return		$clean_attributes		An array of attribute and their cleaned values.
		 *
		 */
		protected function sanitized_attributes() {
			global $db;
			
			$clean_attributes = array();
			foreach ($this->attributes() as $key => $value) {
				if (isset($value) && !empty($value)) {
					$clean_attributes[$key] = $db->escape_value($value);
				}
			}
			return $clean_attributes;
		}
		
		/** 
		 * If the object ID does not exist in the database, it creates a new
		 * row. Otherwise, we update the existing row of information.
		 * 
		 * @return		$bool		True if the resulting function successfully updates the database.
		 *
		 */
		public function save() {
			return isset($this->id) ? $this->update() : $this->create();
		}
			
		/** 
		 * Creates a new row in the database.
		 * 
		 * @return		$bool		True if the database is successfully updated.
		 *
		 */
		public function create() {
			global $db;
		
			$attributes = $this->sanitized_attributes();
					
			$sql = "INSERT INTO " . self::$table . " (";
			$sql .= join(", ", array_keys($attributes)); 
			$sql .= ") VALUES ('";
			$sql .= join("', '", array_values($attributes));
			$sql .= "')";
			if ($db->query($sql)) {
				$this->id = $db->insert_id();
				return true;
			} else {
				return false;
			}
		}
		
		/** 
		 * Updates an existing row in the database.
		 * 
		 * @return		$bool		True if the database is successfully updated.
		 *
		 */
		public function update() {
			global $db;
			
			$attributes = $this->sanitized_attributes();
			$attribute_pairs = array();
			foreach ($attributes as $key => $value) {
				$attribute_pairs[] = "{$key} = '{$value}'";
			}
	
			$sql = "UPDATE " . self::$table . " SET ";
			$sql .= join (", ", $attribute_pairs);
			$sql .= " WHERE id = " . $db->escape_value($this->id);
	
			$db->query($sql);
			return ($db->affected_rows() == 1) ? true : false;
		}
			
		/** 
		 * Deletes a row in the database.
		 * 
		 * @return		$bool		True if the database is successfully updated.
		 *
		 */
		public function delete() {
			global $database;
			
			$sql = "DELETE FROM " . self::$table . " ";
			$sql .= "WHERE id = " . $database->escape_value($this->id) . " ";
			$sql .= "LIMIT 1";
			
			$database->query($sql);
			return ($database->affected_rows() == 1) ? true : false;
		}
	}