<?php

/**
 * Tag.php
 *
 * This class helps manage Tags. 
 *
 */
 
 	require_once(LIB_PATH.DS."init".DS."config.php");
	
	class Tag {
		protected static $table = "tags";
		protected static $fields = array("id", "name");
		
		public $id;
		public $name;		
		
		/*
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
		 * Returns an array of objects containing safe data about a specfic
		 * Tag based on its name.
		 * 
		 * @param				$name				The name to fetch.		 
		 * @param				$fields			The fields which we want to get.
		 * @return			$result			Object containing user information.
		 *
		 */
		 public static function find_by_name($name, $fields=0) {
			global $db;
	    
	    $db->where('name', $name);
	    $result = self::find($fields);
	    
			return !empty($result) ? array_shift($result) : false;
		}
		
		/********************************************************************
		 * Database-neutral functions.																			*
		 *																																	*
		 ********************************************************************/
	
		/** 
		 * Returns an array containing safe data about all Tags.
		 * 
		 * @return			$result			Array of objects containing Tag information.
		 *
		 */
		public static function find_all($fields=0) {
			global $db;
			
			$result = self::find($fields);

			return !empty($result) ? $result : false;
		}
				
		/** 
		 * Returns an array of objects containing safe data about a specfic Tag
		 * based on their ID.
		 * 
		 * @param				$id					The Tag ID to fetch.		 
		 * @param				$fields			The fields which we want to get.
		 * @return			$result			Object containing Tag information.
		 *
		 */
		public static function find_by_id($id, $fields=0) {
			global $db;
	    
	    $db->where('id', $id);
	    $result = self::find($fields);
	    
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
		 * @return		$result				Array of objects containing Tag information.
		 *
		 */
		public static function find_by_sql($sql="") { 
			global $db;
			
			$result = $db->query($sql);
			
			$object_array = array();
			for ($i = 0; $i < count($result); $i++) {
				$object_array[] = self::instantiate($result[$i]);
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
		 * Counts how many Tags are currently in the database.
		 * 
		 * @return		integer			Number of Tags.
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

			if ($db->insert(self::$table, $attributes)) {
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

			$db->where('id', $this->id);
			$db->update(self::$table, $attributes);
			return ($db->affected_rows() == 1) ? true : false;
		}
		
		/** 
		 * Deletes a row in the database.
		 * 
		 * @return		$bool		True if the database is successfully updated.
		 *
		 */
		public function delete() {
			global $db;
			
			$db->where('id', $this->id);
			$db->delete(self::$table);
			return ($db->affected_rows() == 1) ? true : false;
		}
	}