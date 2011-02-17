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
		private $connection;
		private $magic_quotes_active;
		private $real_escape_string_exists;
		
		public $last_query;
			
			
		/**
		 * Constructor for the database object.
		 *
		 */	 
		function __construct($dbname) {
			$this->open_connection($dbname);
			$this->magic_quotes_active = get_magic_quotes_gpc();
			$this->real_escape_string_exists = function_exists("mysql_real_escape_string");
		}
     	
    /**
     * Opens a MySQLi database connection and connects to 
     * the database.
     *
     */
	 	public function open_connection($dbname) {
	 		$this->connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS);
	 		if (!$this->connection) {
	 			die("Database connection failed: " . mysqli_error($this->connection));
	 		} else {
	 			$db_select = mysqli_select_db($this->connection, $dbname);
	 			if (!$db_select) {
	 				die("Database selection failed: " . mysqli_error($this->connection));
	 			}
	 		}
	 	}

 		/** 
 		 * Closes the MySQLi database connection.
 		 *
 		 */    	
   	public function close_connection() {
   		if (isset($this->connection)) {
   			mysqli_close($this->connection);
   			unset($this->connection);
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
   			$value = mysqli_real_escape_string($this->connection, $value);
   			
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
   	public function query($sql) {
   		$this->last_query = $sql;
   		$result = mysqli_query($this->connection, $sql);
   		$this->confirm_query($result);
   		return $result;
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
     * @return		$numrows			The number of returned rows.
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
   		return mysqli_insert_id($this->connection);
   	}
   	
    /**
     * Get the number of affected rows by the last INSERT, UPDATE, 
     * REPLACE or DELETE query associated with link_identifier.
     *
     * @return		$numrows			The number of affected rows.
     *
     */     
   	public function affected_rows() {
   		return mysqli_affected_rows($this->connection);
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
   	    return mysqli_store_result($this->connection);
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
   	    return mysqli_field_count($this->connection);
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
   	
   	/**
   	 * Confirms whether or not a query was performed successfully.
   	 * If the query failed, it echoes the database error and query.
   	 *
   	 * @param		$result				The MySQL result object.
   	 *
   	 */
   	private function confirm_query($result) {
   		if (!$result) {
   			$output = "Database query failed: " . mysqli_error($this->connection);
   			$output .= "<br /><br />";
   			$output .= "Last SQL query: " . $this->last_query;
   			$output .= "<br /><br />";
   			//$output = "Oops! Something went wrong! We're looking into it though, so hang tight!";
   			die($output);
   		}
   	}
 	}
	
	$database = new Database(DB_NAME);
  $db =& $database;
?>