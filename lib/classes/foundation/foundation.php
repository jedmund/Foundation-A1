<?php

/**
 * Foundation.php
 *
 * This class helps manage foundations. 
 *
 */
 
 	require_once(LIB_PATH.DS."init".DS."config.php");
	
	class Foundation {
		protected static $table = "foundations";
		protected static $fields = array("id", "uid", "name", "props");
		protected static $safe_fields = "id, uid, name, props";
		
		public $id;
		public $uid;
		public $name;
		public $props;
		
		
		public function fields_as_names() {
			$ids = json_decode($this->props);
			
			$names = array();
			foreach ($ids as $id) {
				$field = Field::find_by_id($id);
				$names[] = $field->name;
			}
			return $names;
		}
		
		/**
		 * Gets the fields for this Foundation.
		 *
		 * @return			objects			An array of Field objects
		 *
		 */
		 public function get_fields() {
			 $ids = json_decode($this->props);
			 
			 $fields = array();
			 foreach ($ids as $id) {
			 	$field = Field::find_by_id($id);
			 	$fields[] = $field;
			 }
			 
			 return $fields;
		 }
		 
		/**
		 * Accessor methods for the id property.
		 * 
		 * @param				$id					The new ID.		 
		 * @return			integer			The object's current ID.
		 *
		 */
		public function getID() {
			return $this->id;
		}
		
		public function setID($id) {
			$this->id = $id;
		}
		
		/** 
		 * Accessor methods for the uid property.
		 * 
		 * @param				$uid				The new user ID.		 
		 * @return			integer			The object's current user ID.
		 *
		 */
		public function getUID() {
			return $this->uid;
		}
		
		public function setUID($uid) {
			$this->uid = $uid;
		}
		
		/** 
		 * Accessor methods for the name property.
		 * 
		 * @param				$name				The new name.		 
		 * @return			string			The object's current name.
		 *
		 */
		 public function getName() {
		 	return $this->name;
		 }
		 
		 public function setName($name) {
		 	$this->name = $name;
		 }
		 
		/** 
		 * Accessor methods for the keys property.
		 * 
		 * @param				$props			An array of properties.
		 * @return			array				The object's current properties, as an array.
		 *
		 */
		 public function get_props() {
		 	$props = json_decode($this->props);
		 	return $props;
		 }
		 
		 public function set_props($props) {
		 	global $db;
		 	
		 	$this->props = json_encode($props);
		 }
		 
		/** 
		 * Returns an array of objects containing safe data about a specfic
		 * foundation based on its name.
		 * 
		 * @param				$name				The name to fetch.		 
		 * @param				$fields			The fields which we want to get.
		 * @return			$result			Object containing user information.
		 *
		 */
		 public static function find_by_name($name, $fields=0) {
			global $db;
	    
	    $sql = "SELECT " . self::$safe_fields . " FROM " . self::$table;
   		$sql .= " WHERE name = '" . $name . "' LIMIT 1";
						
			$result = self::find_by_sql($sql);
			return !empty($result) ? array_shift($result) : false;
		}
		
		/** 
		 * Returns an array of objects containing safe data about a specfic
		 * foundation based on the user who created it (user ID).
		 * 
		 * @param				$uid				The user ID to filter by.		 
		 * @param				$fields			The fields which we want to get.
		 * @return			$result			Object containing user information.
		 *
		 */
		public static function find_by_uid($uid, $fields=0) {
			global $db;
	    
	    $sql = "SELECT " . self::$safe_fields . " FROM " . self::$table;
   		$sql .= " WHERE uid = " . $uid . " LIMIT 1";
						
			$result = self::find_by_sql($sql);
			return !empty($result) ? array_shift($result) : false;
		}

		/********************************************************************
		 * Database-neutral functions.																			*
		 *																																	*
		 ********************************************************************/
	
		/** 
		 * Returns an array containing safe data about all foundations.
		 * 
		 * @return			$result			Array of objects containing foundation information.
		 *
		 */
		public static function find_all($fields=0) {
			$sql = "SELECT " . self::$safe_fields . " FROM " . self::$table . 
						 " ORDER BY id ASC";
						
			return self::find_by_sql($sql);
		}
				
		/** 
		 * Returns an array of objects containing safe data about a specfic foundation
		 * based on their ID.
		 * 
		 * @param				$id					The foundation ID to fetch.		 
		 * @param				$fields			The fields which we want to get.
		 * @return			$result			Object containing foundation information.
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
			global $database;
		
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