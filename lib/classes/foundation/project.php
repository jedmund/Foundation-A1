<?php

/**
 * Project.php
 *
 * This class helps manage projects. 
 *
 */
 
 	require_once(LIB_PATH.DS."init".DS."config.php");
	
	class Project {
		protected static $table = "projects";
		protected static $fields = array(
																"id", "fid", "uid", "title", "blurb", "description", 
																"template", "path",  "client", "completed", "archived", 
																"slug", "sequence"
															 );
		protected static $safe_fields = "id, fid, uid, title, blurb, description, 
																		 template, path, client, completed, archived, 
																		 slug, sequence";

		
		public $id;
		public $fid;
		public $uid;
		public $title;
		public $blurb; 
		public $description;
		public $completed;
		public $client;
		public $archived;
		public $tags = array();
		public $template;
		public $path;
		public $sequence;
		public $slug;
		
		public $print_formats;
		public $paper_used;
		public $print_method;
		public $software;
		
		public $website;
		public $languages;
		
		public $cameras;
		public $film;

		public $music;
		
		public $product_type;
		public $manufacturing_processes;
		public $materials;
		
		public function make_thumb_path($mode='thumb') {
			if ($files = @scandir(PUBLIC_PATH.DS.$this->path)) {
				if ($mode == 'system') {
					$grep = '/system_thumb/';
				} else {
					$grep = '/thumb/';
				}
				
				$match = array_shift(preg_grep($grep, $files));
				if (!empty($match)) {
				 	$grep = str_replace('/', '', $grep);
					$this->thumb = $this->path . $grep . '.png';
				}
			} else {
				$notice = new Notice;
				$notice->type  = 1;
				$notice->text  = "There was a problem accessing the folder for the project '" . $this->title . "'. ";
				$notice->text .= "Please check that this project has a folder inside of the 'content' folder and try again.";
				$notice->save();
			}
		}
		
		/** 
		 * Takes an array of Field objects, and changes the value of that field
		 * within the Project from a JSON string of IDs to a JSON string of values.
		 *
		 * @param				$fields				An array of Field objects
		 *
		 */
		 public function get_field_values($fields) {
		 	$field_values = array();
		 	
			foreach ($fields as $field) {
				if ($field->type == "fstinput") {
					$name = $field->name;

					if (!empty($this->$name)) {
						$ids = json_decode($this->$name);
						$values = array();
						
						foreach ($ids as $id) {
							$value_obj = $field->get_data_by_id($id);
							$value = $value_obj['value'];
							$values[] = $value; 
						}
						
						$field_values[$name]['name'] = $name;
						$field_values[$name]['data'] = json_encode($values);
					}
				} else if ($field->type == "finput") {
				  $name = $field->name;
				  
					if (!empty($this->$name)) {
						$field_values[$name]['name'] = $name;
						$field_values[$name]['data'] = $this->$name;
					}
				}
			}
			return $field_values;
		 }
		
		/** 
		 * Creates folder for the project.
		 *
		 */
 	  public function create_folder() {
 	  	$user = User::find_by_id($this->uid);
 	  	$path = '..'.DS.'..'.DS.'content'.DS.$user->username.DS.to_filename($this->title);
 	  	$folder = false;
 	  	
 	  	if (!is_dir($path)) {
 	 	  	if (mkdir($path, 0777)) {
	 	  		$folder = true;
	 	  	}
	 	  } else {
	 	  	$folder = true;
	 	  }
	 	  chmod($path, 0777);
	 	  return $folder;
 	  }
		
		/** 
		 * Helper function that returns the safe fields. 
		 *
		 * @param				array					An array of fields to remove before return.
		 * @return			array					The object's safe fields as an array.
		 *
		 */
		public function get_fields($remove = array('id','uid','fid')) {
			$fields = explode(", ", self::$safe_fields);
			
			if (!empty($remove)) {
				foreach ($remove as $field) {
					if (in_array($field, $fields)) {
						$key = array_search($field, $fields);
						unset($fields[$key]);
					}
				}
			}
			return $fields;
		}
		 
		/**
		 * Modifies the project according to the project's foundation.
		 * If the object has an ID, then it will grab the tertiary
		 * information and then modify the object to allow edits, 
		 * otherwise it simply allows the tertiary data to be saved.
		 *
		 * @param				int						The foundation ID to set.
		 * @return			boolean				Returns true if all operations were successful.
		 *
		 */
		public function set_foundation($fid=0) {
			global $db; 
			
			$fid = (empty($this->fid)) ? $fid : $this->fid;
			$foundation = Foundation::find_by_id($fid);
			$this->foundation = $foundation->name;
			
			// Decode the fields and merge the array with the object's
			// database fields instance variable.		 	
			$field_ids = json_decode($foundation->props);
			$fields = array();
			
			foreach ($field_ids as $field_id) {
				$field = Field::find_by_id($field_id);
				$fields[] = $field->name;
			}
			
			self::$fields = array_merge(self::$fields, $fields);
			// Join the array together with commas and add it onto the object's
			// safe fields string. 
			$fields = join(", ", $fields);
			self::$safe_fields .= ", " . $fields;
			
			// If the object has an ID, information already exists, so we should
			// pull that foundation data from the database.
			if (!empty($this->id)) {
				$sql = "SELECT {$fields} FROM " . self::$table . " WHERE id = {$this->id} LIMIT 1";
				$result = $db->query($sql);
				
				if ($result) {
					$row = $db->fetch_array($result);
					
					// Assign the fetched data to their respective instance variables.
					foreach ($row as $key => $val) {
						$this->$key = stripslashes($val);
					}
				} else return false;
			}
			return true;
		}
		 
		/**
		 * Helper function that tells whether or not the project has tags.
		 *
		 * @return			boolean				True if the project has tags.
		 *
		 */
		public function has_tags() {
		 	$tag_assocs = Tag_Association::find_by_pid($this->id);
		 	if (!empty($tag_assocs)) {
		 		return true;
		 	} else return false;
		}
		
		/**
		 * Helper function that tells how many tags the project has.
		 *
		 * @return			int						Number of tags attached to the project.
		 *
		 */
		public function num_tags() {
		 	if (!$this->has_tags()) return false;
		 	$tag_assocs = Tag_Association::find_by_pid($this->id);
		 	return count($tag_assocs);
		}
		
		/**
		 * Gets an array of tag objects for the current project.
		 *
		 * @return			array				The array of tag objects.
		 */
		public function get_tags() {
		 	global $db;
		 	
		 	$tag_assocs = Tag_Association::find_by_pid($this->id);
		 	foreach ($tag_assocs as $tag_assoc) {
		 		$tag = Tag::find_by_id($tag_assoc);
		 		$this->tags[] = $tag;
		 	}
		}
		 
		/**
		 * Adds a tag association for the current project. 
		 * SHOULD CHECK IF IT ALREADY EXISTS
		 *
		 * @param				$id					The ID of the tag.
		 * @param				$name				The name of the tag.
		 *
		 */
		 /*
		 public function add_tag($tid=0, $name="") {
		 	global $db;
		 	
		 	if (!empty($tid)) {
		 		$tag = Tag::find_by_id($tid);
		 	} else if (!empty($name)) {
		 		$tag = Tag::find_by_name($name);
		 	}
		 	
		 	$tag_assoc = new Tag_Association;
		 	$tag_assoc->pid = $this->id;
		 	$tag_assoc->tid = $tag->id;
		 	$tag_assoc->save();
		 	
		 	if (!empty($this->tags) {
		 		$this->tags[] = $tag;
		 	}
		 }
		*/
		/**
		 * Removes a tag association for the current project.
		 * SHOULD CHECK IF IT ALREADY EXISTS
		 *
		 * @return			array				The array of tag objects.
		 */
		 /*
		 public function remove_tag($tid=0, $name="") {
		 	global $db;
		 	
			// Get the Tag object so that we have all information.
			if (!empty($tid)) {
				$tag = Tag::find_by_id($tid);
			} else if (!empty($name)) {
				$tag = Tag::find_by_name($name);
			}
			
			// Find the Tag Association object.
			
			
		 	$tag_assoc = new Tag_Association;
		 	$tag_assoc->pid = $this->id;
		 	$tag_assoc->tid = $tag->id;
		 	$tag_assoc->save();
		 	
		 	if (!empty($this->tags) {
		 		$this->tags[] = $tag;
		 	}
		 }
		*/

		 
		/** 
		 * Returns an array of objects containing safe data about a specfic
		 * project based on its title.
		 * 
		 * @param				$title			The title of the project we'll fetch.		 
		 * @param				$fields			The fields which we want to get.
		 * @return			$result			Object containing user information.
		 *
		 */
		/*
		 public function get_foundation($id) {
			global $db;
			
	    $sql = "SELECT fid FROM " . self::$table . " WHERE id = " . $id . " LIMIT 1";
	    $result = array_shift($db->query($sql));
	    
	    $fid = $result['fid'];

	    $props = self::get_foundation_props($fid);
	    return !empty($props) ? $props : false;
		}
		
		private function get_foundation_props($fid) {
			global $db;
			
			$foundation = Foundation::find_by_id($fid);
			$prop_array = json_decode($foundation->props);
			return $prop_array;
		}
		*/

		/** 
		 * Returns an array of objects containing safe data about archived
		 * projects.
		 * 
		 * @return			$result			Array of objects containing project information.
		 *
		 */
		public static function find_archived($uid = 0) {
			$sql = "SELECT " . self::$safe_fields . " FROM " . self::$table;
   		$sql .= " WHERE archived = 1 ";
   		$sql .= (!empty($uid)) ? "AND uid = " . $uid . " " : "";
   		$sql .= "ORDER BY sequence ASC";
			
			return self::find_by_sql($sql);
		}

		/** 
		 * Returns an array of objects containing safe data about a specfic
		 * project based on its title.
		 * 
		 * @param				$title			The title of the project we'll fetch.		 
		 * @param				$fields			The fields which we want to get.
		 * @return			$result			Object containing user information.
		 *
		 */
		public static function find_by_title($title, $fields=0) {
			$sql = "SELECT " . self::$safe_fields . " FROM " . self::$table;
   		$sql .= " WHERE title = '" . $title . "' LIMIT 1";
						
			$result = self::find_by_sql($sql);
			return !empty($result) ? array_shift($result) : false;
		}
		
		/** 
		 * Returns an array of objects containing safe data about a specfic
		 * project based on its slug.
		 * 
		 * @param				$slug				The slug of the object we'll fetch.		 
		 * @param				$fields			The fields which we want to get.
		 * @return			$result			The object.
		 *
		 */
		public static function find_by_slug($slug, $fields=0) {
			$sql = "SELECT " . self::$safe_fields . " FROM " . self::$table;
   		$sql .= " WHERE slug = '" . $slug . "' LIMIT 1";
						
			$result = self::find_by_sql($sql);
			return !empty($result) ? array_shift($result) : false;
		}

		
		/** 
		 * Returns an array of objects containing safe data about a specfic
		 * project based on its sequence.
		 * 
		 * @param				$sequence		The sequence of the object we'll fetch.		 
		 * @param				$fields			The fields which we want to get.
		 * @return			$result			The object.
		 *
		 */
		public static function find_by_sequence($sequence, $fields=0) {
			$sql = "SELECT " . self::$safe_fields . " FROM " . self::$table;
   		$sql .= " WHERE sequence = '" . $sequence . "' LIMIT 1";
						
			$result = self::find_by_sql($sql);
			return !empty($result) ? array_shift($result) : false;
		}
		
		/** 
		 * Returns an array of objects containing safe data about specfic
		 * projects based on the user who created it (user ID).
		 * 
		 * @param				$uid				The user ID to filter by.		 
		 * @param				$archive		Boolean set to true to include archived projects.
		 * @return			$result			Object containing user information.
		 *
		 */
		public static function find_by_uid($uid, $archive=false) {
			$sql = "SELECT " . self::$safe_fields . " FROM " . self::$table;
			if (!$archive) {
				$sql .= " WHERE archived = 0 AND";
			} else {
				$sql .= " WHERE ";
			}		
			$sql .= " uid = " . $uid . " ORDER BY sequence ASC";

			return self::find_by_sql($sql);
		}

		/********************************************************************
		 * Database-neutral functions.																			*
		 *																																	*
		 ********************************************************************/
	
		/** 
		 * Returns an array containing safe data about all projects.
		 * 
		 * @param				$archive		Boolean set to true to include archived projects.
		 * @return			$result			Array of objects containing project information.
		 *
		 */
		public static function find_all($archive=false) {
			
			$sql = "SELECT " . self::$safe_fields . " FROM " . self::$table;
			if (!$archive) {
				$sql .= " WHERE archived = 0 ";
			}
			$sql .= " ORDER BY sequence ASC";
			return self::find_by_sql($sql);
		}
				
		/** 
		 * Returns an array of objects containing safe data about a specfic foundation
		 * based on their ID.
		 * 
		 * @param				$id					The foundation ID to fetch.		 
		 * @return			$result			Object containing project information.
		 *
		 */
		public static function find_by_id($id) {
   		$sql = "SELECT " . self::$safe_fields . " FROM " . self::$table . 
   					 " WHERE id = " . $id . " LIMIT 1";
						
			$result = self::find_by_sql($sql);
			return !empty($result) ? array_shift($result) : false;
		}
				
	  /** 
		 * Performs the given SQL query and returns an array of objects of its results.
		 * 
		 * @param			$sql					The SQL query to perform.
		 * @return		$result				Array of objects containing foundation information.
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
		 * Counts how many foundations are currently in the database.
		 * 
		 * @return		integer			Number of foundations.
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
				$clean_attributes[$key] = $db->escape_value($value);
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
					
			$sql  = "INSERT INTO " . self::$table . " (";
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
	
			$sql  = "UPDATE " . self::$table . " SET ";
			$sql .= join (", ", $attribute_pairs);
			$sql .= " WHERE id = " . $db->escape_value($this->id);
			// return ($db->affected_rows() == 1) ? true : false;

			if ($db->query($sql)) {
				return true;
			} else return false;
		}
		
		/** 
		 * Deletes a row in the database.
		 * 
		 * @return		$bool		True if the database is successfully updated.
		 *
		 */
		public function delete() {
			global $database;
			
			$sql  = "DELETE FROM " . self::$table . " ";
			$sql .= "WHERE id = " . $database->escape_value($this->id) . " ";
			$sql .= "LIMIT 1";
			
			$database->query($sql);
			return ($database->affected_rows() == 1) ? true : false;
		}
	}