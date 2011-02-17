<?php

/**
 * User.php
 *
 * This class helps manage users. 
 *
 */
 
 	require_once(LIB_PATH.DS."init".DS."config.php");
	
	class User {
		protected static $table = "users";
		protected static $fields = array(
																"id", "username", "password", "email", "level",
																"first_name", "last_name", "birthday", "city", "country",
																"last_active", "bio", "social", "resume", "portfolio", "photo",
																"status");
																
		protected static $safe_fields = "id, username, level, first_name, last_name, 
																		 birthday, city, country, last_active, bio, social,
																		 resume, portfolio, photo, status";
		
		public $id;
		public $fbid;
		public $oauth_token;
		public $oauth_secret;
		
		public $username;
		public $password;
		public $email;
		public $level;
		
		public $first_name;
		public $last_name;
		public $birthday;
		public $city;
		public $country;
		
		public $last_active;
		
		public $bio;
		public $social;
		public $resume;
		public $portfolio;
		public $photo;
		public $status;
		
		/** 
		 * Returns the user's full name if applicable. If first name or last
		 * name are not filled out, returns the username.
		 *
		 */
		public function full_name() {
			if (!empty($this->first_name) && !empty($this->last_name)) {
				$full_name = $this->first_name . " " . $this->last_name;
			} else {
				$full_name = $this->username;
			}
			
			return $full_name;
		}
			
		/**
		 * Authenticates that the user is supplying the correct credentials
		 * to log in.
		 *
		 * @param			$username		The supplied username.
		 * @param			$password		The supplied password.
		 * @return		$result			Returns the user's object if authentication is
		 *												successful, otherwise we return false.
		 *
		 */
		public static function authenticate($username, $password) {
			global $db;
			
			$username = $db->escape_value(trim($username));
			$password = $db->escape_value(trim($password));

			$salt = hash("sha1", strtolower($username));
			$password = crypt($password, $salt);
			
			$sql =  "SELECT * FROM " . self::$table . " ";
			$sql .= "WHERE username = '{$username}' ";
			$sql .= "AND password = '{$password}' ";
			$sql .= "LIMIT 1";
			
			$result = self::find_by_sql($sql);
			
			return !empty($result) ? array_shift($result) : false;
		}

		
		/** 
		 * Returns an array of objects containing safe data about a specfic user
		 * based on their username.
		 * 
		 * @param				$username		The username to fetch.		 
		 * @param				$fields			The fields which we want to get.
		 * @return			$result			Object containing user information.
		 *
		 */
		public static function find_by_username($username, $fields=0) {
			global $db;
	    
	  	$sql = "SELECT " . self::$safe_fields . " FROM " . self::$table;
   		$sql .= " WHERE username = " . $username . " LIMIT 1";
						
			$result = self::find_by_sql($sql);
			return !empty($result) ? array_shift($result) : false;	
		}
		
		public function get_email() {
			$sql = "SELECT email FROM " . self::$table . " WHERE id = " . $this->id;
			$result = array_shift(self::find_by_sql($sql));

			return !empty($result) ? $result->email : false;
			
		}
		
		/********************************************************************
		 * Database-neutral functions.																			*
		 *																																	*
		 ********************************************************************/
	
		/** 
		 * Returns an array containing safe data about all users.
		 * 
		 * @return			$result			Array of objects containing user information.
		 *
		 */
		public static function find_all($fields=0) {
			$sql = "SELECT " . self::$safe_fields . " FROM " . self::$table . 
						 " ORDER BY id ASC";
						
			return self::find_by_sql($sql);
		}
				
		/** 
		 * Returns an array of objects containing safe data about a specfic user
		 * based on their ID.
		 * 
		 * @param				$id					The user ID to fetch.		 
		 * @param				$fields			The fields which we want to get.
		 * @return			$result			Object containing user information.
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
		 * @param			$params				The parameters to bind to the MySQLi Statement Object.
		 * @return		$result				Array of objects containing user information.
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
		 * Gets specified information from the database, then instantiates
		 * it into objects.
		 *
		 * @param				$fields			The fields which we want to get.
		 * @return			objects			The information instantiated int0o objects.
		 *
		 */
		public static function find($fields=0) {
			global $db;
			
			if (empty($fields)) {
				$fields = array("*");
			}
			
			$result = $db->get(self::$table, $fields);
			
			$object_array = array();
			for ($i = 0; $i < count($result); $i++) {
				$object_array[] = self::instantiate($result[$i]);
			}
			return $object_array;
		}

		
		/** 
		 * Counts how many users are currently in the database.
		 * 
		 * @return		integer			Number of users.
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