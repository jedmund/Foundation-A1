<?php

/**
 * Systask.php
 *
 * This class helps manage system-level tasks. 
 *
 */
 
 	require_once(LIB_PATH.DS."init".DS."config.php");
	
	class Systask {
		protected static $table = "queue";
		protected static $fields = array("id", "priority", "script", "params", "date_execute", "date_created", "date_completed", "completed");
		protected static $safe_fields = "id, priority, script, params, date_execute, date_created, date_completed, completed";
		
		public $id;
		public $priority;
		public $script;
		public $params;
		public $date_execute;
		public $date_created;
		public $date_completed;
		public $completed;

		public function schedule($script, $date_execute, $params=array(), $priority=1) {
			$this->script = $script;
			$this->date_execute = $date_execute;
			$this->params = $params;
			$this->priority = $priority;
			$this->save();
		}
		
		public function run() {			
			$tasks = PUBLIC_PATH.DS.'admin'.DS.'system'.DS.'tasks'.DS;
			$path = $tasks.$this->script.".php";
			$call = "php " . $path . ' 2>&1';
			
			if ($this->launch_bg_process($call)) {
				$this->complete();
			}
		}

		public function launch_bg_process($call) {
    	$o = array();
			exec($call, $o);
			$o = implode("\n", $o);

	    return true;
		}

		public function complete() {
			global $database;
			$this->date_completed = $database->dbtime();
			$this->completed = 1;
			$this->save();
		}

		public static function find_incomplete($priority=0) {
			$sql = "SELECT " . self::$safe_fields . " FROM " . self::$table .
						 " WHERE completed < 1".
						 " ORDER BY date_execute ASC";
			return self::find_by_sql($sql);
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
		
		
		// Make method find(), get(), whatever, that calls $database->get() with parameters
		// but also instantiates each object afterwards.
		// Then, replace the $database->get()s in this class with this method.
		// This will replace find_by_sql().
				
	  /** 
		 * Performs the given SQL query and returns an array of objects of its results.
		 * 
		 * @param			$sql					The SQL query to perform.
		 * @return		$result				Array of objects containing foundation information.
		 *
		 */
		public static function find_by_sql($sql="") { 
			global $database;
			
			$result = $database->query($sql);
			
			$object_array = array();
			while ($row = $database->fetch_array($result)) {
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
			global $database;
			
			$sql = "SELECT COUNT(*) FROM " . self::$table;
			$result = array_shift($database->query($sql));
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
			global $database;
			
			$clean_attributes = array();
			foreach ($this->attributes() as $key => $value) {
				if (isset($value) && !empty($value)) {
					$clean_attributes[$key] = $database->escape_value($value);
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
			if ($database->query($sql)) {
				$this->id = $database->insert_id();
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
			global $database;
			
			$attributes = $this->sanitized_attributes();
			$attribute_pairs = array();
			foreach ($attributes as $key => $value) {
				$attribute_pairs[] = "{$key} = '{$value}'";
			}
	
			$sql = "UPDATE " . self::$table . " SET ";
			$sql .= join (", ", $attribute_pairs);
			$sql .= " WHERE id = " . $database->escape_value($this->id);
	
			$database->query($sql);
			return ($database->affected_rows() == 1) ? true : false;
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
