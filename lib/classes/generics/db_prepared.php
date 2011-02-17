<?php

/**
 * Database.php
 *
 * This class helps your application connect to and manipulate 
 * a MySQL database.
 *
 */
 
 	require_once(LIB_PATH.DS."init".DS."constants.php");
	
	class Database {

		protected $sql;
		protected $where = array();
		protected $paramTypeList;
		private $mysqli;
		private $magic_quotes_active;
		private $real_escape_string_exists;

		/**
		 * Constructor for the database object.
		 *
		 * @param				$dbname				The name of the database to connect to.
		 *
		 */
		public function __construct() {
		  $this->open_connection();
			$this->magic_quotes_active = get_magic_quotes_gpc();
			$this->real_escape_string_exists = function_exists("mysql_real_escape_string");
		}
   
		/**
		 * Opens a MySQLi database connection and connects to the database.
		 * 
		 * @param			$dbname			The name of the database to connect to.
		 *
		 */
	 	public function open_connection() {
	 		$this->mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	 		if (!$this->mysqli) {
	 			die("Database connection failed: " . mysqli_error($this->mysqli));
	 		} 
	 	}

		/** 
 		 * Closes the MySQLi database connection.
 		 *
 		 */    	
   	public function close_connection() {
   		if (isset($this->mysqli)) {
   			mysqli_close($this->mysqli);
   			unset($this->mysqli);
   		}
   	}

		/**
		 * Escapes value $value for safe insertion into the database.
		 *
		 * @param			$value	The value to escape.
		 * @result		$value	The cleaned value.
		 *	
		 */
   	public function escape_value($value) {
   		if ($this->real_escape_string_exists) { // PHP v4.3.0 or higher
   			// Undo any magic quote effects so mysql_real_escape_string can do the work.
   			if ($this->magic_quotes_active) {
   				$value = stripslashes($value);
   			}
   			$value = mysqli_real_escape_string($this->mysqli, $value);
   			
   		} else { // before PHP v4.3.0
   			// If magic quotes aren't already on then add slashes manually.
   			if(!$this->magic_quotes_active) {
   				$value = addslashes($value);
   			}
   			// If magic quotes are active, then the slashes already exist.
   		}
   		return $value;
   	}
		
		/**
     * Performs a query on the database.
     *
     * @param			$sql				The SQL query to perform.
     * @result		$result			The result object of the query.
     *
     */ 
		public function query($sql, $params) {
			$this->sql = filter_var($sql, FILTER_SANITIZE_STRING);
			$stmt = $this->prepareQuery();
			$stmt = $this->bind_params($stmt, $params);
			$stmt->execute();
			$stmt->store_result();
			print_r($stmt);
			echo "<br><br>";

			$result = $this->bind_results($stmt);
			return $result;
		}
		
		
		/**
		 * Binds parameters to the MySQLi Statement Object. 
		 *
		 * @param			$stmt				The MySQLi Statement Object.
		 * @param			$params			The parameters to bind as an ordered
		 *											  associative array.
		 * @return		$stmt				The modified MySQLi Statement Object.
		 *
		 */
		protected function bind_params($stmt, $params) {			
			// Determine all types.
			$this->determine_types($params);
			
			// Create an array for the user function array's arguments
			// and fill the first index with the parameter type array first.
			$args = array();
			$args[] = $this->paramTypeList;
			
			// Loop over the parameters and insert each value into the
			// argument array.
			foreach ($params as $param)	{
				$args[] = &$param;
			}
			
			// Call the user function array for bind_param.
			call_user_func_array(array($stmt, 'bind_param'), $args);
			
			return $stmt;
		}
		
		/** 
		 * Determines types of all objects in the provided array.
		 *
		 * @param			$params			The parameters whose types we need.
		 *
		 */
		protected function determine_types($params) {
			foreach ($params as $key => $value) {
				$this->paramTypeList .= $this->determineType($value);
			}
		}
		 
		/**
		 * Returns specific data from n rows of a table.
		 *
		 * @param				$table			The name of the table whose rows we want.
		 * @param				$fields			An array of the fields we want to get.
		 * @param				$num_rows		The number of rows to get.
		 *
		 */
  	public function get($table, $fields, $num_rows = NULL) {
			
      $this->sql = "SELECT " . implode(", ", $fields) . " FROM " . $table;
      $stmt = $this->buildQuery($num_rows);
      $stmt->execute();

      $result = $this->bind_results($stmt);
      return $result;
   }

	   /**
	    * Performs an insert query on the database.
	    *
	    * @param			$table			The table which we are manipulating.
	    * @param			$data				The data that we are inserting.
	    * @return			boolean			A boolean set to true if the query was
	    *													completed successfully.
	    *
	    */
	   public function insert($table, $data) {
	   		$success = false;
	   		
	      $this->sql = "INSERT INTO $table ";
	      $stmt = $this->buildQuery(NULL, $data);
	      $stmt->execute();
	
	      if ($stmt->affected_rows) {
	      	$success = true;
	      }
	
				return $success;
	   }
	   
	   /**
	    * Performs an update query on the database.
	    * !! Call where() before using this method.
	    * 
	    * @param			$table			The table which we are manipulating.
	    * @param			$data				The data that we are using to update.
	    *	@return			boolean			A boolean set to true if the query was
	    *											  	completed successfully.
	    *
	    */
	   public function update($table, $data) {
				$success = false;
	
	      $this->sql = "UPDATE $table SET ";
	
	      $stmt = $this->buildQuery(NULL, $data);
	      $stmt->execute();
	
	      if ($stmt->affected_rows) {
	      	$success = true;
	      }
	
				return $success;
	   }
	
		 /**
		 	* Performs a delete query on the database.
		 	* !! Call where() before using this method.
		 	*
		 	* @param			$table			The table which we are manipulating.
		 	* @return			boolean			A boolean set to true if the query was
		 	*													completed successfully.
		 	*		
		 	*/
	   public function delete($table) {
	   		$success = false;
	   		
	      $this->sql = "DELETE FROM $table ";
	
	      $stmt = $this->buildQuery();
	      $stmt->execute();
	
	      if ($stmt->affected_rows) {
	      	$success = true;
	      }
	
				return $success;
	   }
	
	   /**
	    * FIX THIS TO SUPPORT AND AND OR STATEMENTS
	    * Specify a where statement for SQL queries.
	    * 
	    * @param			$whereProp			A string specifying the database field desired.
	    * @param			$whereValue			The value of the field desired.
	    *
	    */
	   public function where($whereProp, $whereValue)  {
	      $this->where[$whereProp] = $whereValue;
	   }
	
	
		/**
		 * This function determines the data type of the field
		 * and updates param_type, to make prepared statements
		 * possible.
		 *
		 * @param				$item			Item whose type needs to be determined.
		 * @return			string		The joined parameter types.
	   */
	   protected function determineType($item) {
	      switch (gettype($item)) {
	         case 'string':
	            $param_type = 's';
	            break;
	
	         case 'integer':
	            $param_type = 'i';
	            break;
	
	         case 'blob':
	            $param_type = 'b';
	            break;
	
	         case 'double':
	            $param_type = 'd';
	            break;
	      }
	      return $param_type;
	   }
	
		/**
		 * This method compiles all the data and builds the SQL query.
		 *
		 * @param			$num_rows			The number of rows to return.
		 * @param			$data					The data from which to build the query.
		 * @return		object				The statement object.
		 */ 
		protected function buildQuery($num_rows = NULL, $data = false) {
			$hasData = null;
			
			if (gettype($data) === 'array') {
				$hasData = true;
			}
			
			// Did the user call the "where" method?
			if (!empty($this->where)) {
				$keys = array_keys($this->where);
				$where_prop = $keys[0];
				$where_value = $this->where[$where_prop];

				// If data was passed, filter through and create the SQL query.
				if ($hasData) {
					$i = 1;
					foreach ($data as $key => $value) {
						// Determine data type.
						$this->paramTypeList .= $this->determineType($value);
						
						// If we've reached the last binding, then we need to 
						// append the where clause.
						if ($i === count($data)) {
							$this->sql .= $key . " = ? WHERE " . $where_prop . "= " . $where_value;
						} else {
							$this->sql .= $key . ' = ?, ';
						}
				
						$i++;
					}
				} else {
					// If no table data was passed, then this might be a 
					// select statement.
					$this->paramTypeList = $this->determineType($where_value);
					$this->sql .= " WHERE " . $where_prop . "= ?";
				}
			}
			
			// If we find "INSERT" in the query text, then we have
			// an insert query.
			if ($hasData) {
				$pos = strpos($this->sql, 'INSERT');
				
				if ($pos !== false) {
					// Separate out the keys and values.
					$keys = array_keys($data);
					$values = array_values($data);
					$num = count($keys);
					
					// Prepare values for database insertion.
					foreach ($values as $key => $val) {
						$values[$key] = "'{$val}'";
						$this->paramTypeList .= $this->determineType($val);
					}
					
					// Put the keys and values into the query string.
					$this->sql .= '(' . implode($keys, ', ') . ')';
					$this->sql .= ' VALUES(';
					while ($num !== 0) {
						($num !== 1) ? $this->sql .= '?, ' : $this->sql .= '?)';
						$num--;
					}
				}
			}
			
			// If the user set a limit, then set it.
			if (isset($num_rows)) {
				$this->sql .= " LIMIT " . (int) $num_rows . ";";
			} else $this->sql .= ";";
			// Prepare query
			$stmt = $this->prepareQuery();
	
			// Bind parameters
			if ($hasData) {
				$args = array();
				$args[] = $this->paramTypeList;
				foreach ($data as $key => $val) {
					$args[] = &$data[$key];
				}
				call_user_func_array(array($stmt, 'bind_param'), $args);
			} else {
				if ($this->where) {
					$stmt->bind_param($this->paramTypeList, $where_value);
				}
			}
			$this->paramTypeList = "";
			$this->where = array();
			return $stmt;
		}
		
		/** 
		 * Binds results for prepared statements in cases where we don't know
		 * how many variables to pass.
		 *
		 * @param				$stmt			The MySQLi prepared statement object
		 * @return			array			The MySQL result
		 *
		 */
	   protected function bind_results($stmt) {
	   		$data   = array();
	      $params = array();
	      $result = array();
	
	      $meta = $stmt->result_metadata();
	
	      while ($field = $meta->fetch_field()) {
	      	$params[] = &$data[$field->name];
	      }

	      $result = call_user_func_array(array($stmt, 'bind_result'), $params);

				if (!$result) {
					echo "bind_result() failed: " . $mysqli->error . "\n" ; 
				} else {
		      while ($stmt->fetch()) {
		         $x = array();
		         foreach ($data as $key => $val) {
		            $x[$key] = $val;
		         }
		         $result[] = $x;
		      }
		      return $result;
				}
	     }
	
		/**
		 * Prepares the SQL query if possible, otherwise throws an error.
		 *
		 */
	   protected function prepareQuery() {
	      if (!$stmt = $this->mysqli->prepare($this->sql)) {
	         trigger_error("Problem preparing query", E_USER_ERROR);
	      }
	      return $stmt;
	   }
	
	
	   public function __destruct() {
	   		$this->mysqli->close();
	   }
	   
	 	/** 
		 * Returns an array that corresponds to the fetched row or NULL if 
		 * there are no more rows for the resultset represented by the 
		 * result parameter.
		 *
		 * @param			$result				The SQL result object.
		 * @param			$resulttype		The type of result to return: MYSQLI_ASSOC, MYSQLI_NUM, or MYSQLI_BOTH
		 * @return		$result				The SQL result object as an array.
		 *
		 */
		public function fetch_array($result, $resulttype="MYSQLI_BOTH") {
	 		return mysqli_fetch_array($result);
	 	}
	  
	  /**
	   * Fetches one row of data from the result set and returns it as 
	   * an enumerated array, where each column is stored in an array 
	   * offset starting from 0 (zero). Each subsequent call to this 
	   * function will return the next row within the result set, or 
	   * NULL if there are no more rows.
	   *
	   * @param			$result				The SQL result object.
	   * @return		$result				The SQL object as an array.
	   *
	   */     	
		public function fetch_row($result) {
			return mysqli_fetch_row($result);
		}
	 	
	  /**
	   * Returns the number of rows in the result set.
	   *
	   * @param			$result				The SQL result object.
	   * @return		$num_rows			The number of returned rows.
	   *
	   */  
		public function num_rows($result) {
			return mysqli_num_rows($result);
		}
		
	  /**
	   * Retrieves the ID generated for an AUTO_INCREMENT column by the 
	   * previous query (usually INSERT).
	   *
	   * @return		$id						The generated ID for the previous query.
	   *
	   */
	 	public function insert_id() {
	 		return mysqli_insert_id($this->mysqli);
	 	}
	 	
	  /**
	   * Get the number of affected rows by the last INSERT, UPDATE, 
	   * REPLACE or DELETE query associated with link_identifier.
	   *
	   * @return		$num_rows			The number of affected rows.
	   *
	   */     
	 	public function affected_rows() {
	 		return mysqli_affected_rows($this->mysqli);
	 	}
	 	
	  /**
	   * Get the current time and format it for quick database entry.
	   *
	   * @return		$dbtime				The current time, formatted for MySQL.
	   *
	   */  
	 	public static function dbtime() {
	 		$time = time();
	 		return $dbtime = strftime("%Y-%m-%d %H:%M:%S", $time);
	 	}
	 	
	  /**
	   * Transfers the result set from the last query on the database 
	   * connection represented by the link parameter to be used with 
	   * the mysqli_data_seek() function.
	   *
	   * @return		$result				Returns a buffered result object or FALSE if an error occurred.
	   *
	   */
	 	public function store_result() {
	 		return mysqli_store_result($this->mysqli);
	 	}
	 	
	 	/**
	   * Returns the number of columns for the most recent query on the 
	   * connection represented by the link parameter. This function can 
	   * be useful when using the mysqli_store_result() function to 
	   * determine if the query should have produced a non-empty result 
	   * set or not without knowing the nature of the query.
	   *
	   * @return		$result				An integer representing the number of fields in a result set.
	   *
	   */
	 	public function field_count() {
	 		return mysqli_field_count($this->mysqli);
	 	}
	 	
	  /**
	   * Frees the memory associated with a result.
	   *
	   */
	 	public function free_result($result) {
	 		return mysqli_free_result($result);
	 	}
	 	
	 	/**
	 	 * Used to initiate the retrieval of a result set from the last 
	 	 * query executed using the mysqli_real_query() function on the 
	 	 * database connection.
	 	 *
	 	 * @return		$result				Returns an unbuffered result object or FALSE if an error occurred.
	 	 *
	 	 */
	 	public function use_result($result) {
			return mysqli_use_result($result);
	  }
	}

	$database = new Database(DB_NAME);
	$db =& $database;
?>