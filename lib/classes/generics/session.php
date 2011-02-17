<?php

/**
 * Session.php
 *
 * This class helps manage sessions. 
 *
 */
 
 	require_once(DATABASE);
	require_once(FLIB_PATH.DS.'user.php');

	class Session {
	
		public $uid;
		public $username;
		public $last_page;
		private $logged_in = false;
	
		/**
		 * Basic constructor method for the Session class.
		 * Starts a session and then checks whether or not
		 * the user is logged in.
		 *
		 */
		function __construct() {
			session_start();
			$this->check_login();
		}
			
		/**
		 * Determines whether the user is logged in.
		 *
		 */
		public function is_logged_in() {
			if ($this->logged_in > 0) {
				$queue = Systask::find_incomplete();
				foreach ($queue as $task) {
					$now = strtotime("+1 hour");
					$exec = strtotime($task->date_execute);
					
					if ($now > $exec) {
						$task->run();
					}
				}
			}
		
			return $this->logged_in;
		}
	
		/**
		 * Attempts to log the user in.
		 *
		 * @param			$user					The user object to log in.
		 * @param			$permanent		A boolean representing whether or not to
		 *													place a permanent cookie.
		 */
		public function login($user, $permanent=false) {
			global $database;
			
			if ($user) {
				// Set the session variables.
				$_SESSION['uid'] = $user->id;
				$_SESSION['username'] = $user->username;
	
				// If we are setting a permanent cookie, set it to expire in ~20 years.
				// Otherwise, we set the cookie to expire in about 12 hours.   
				if ($permanent == "on") {
					$lifetime = 60 * 60 * 24 * 365 * 20;
					setcookie('fauth', $user->id, time()+$lifetime);
				} else {
					$length = 60 * 60 * 12;
					setcookie('fauth', $user->id, time()+$length);
				}
				
				// Update the user's last activity.
				$user->last_active = Database::dbtime();
				
				// If we don't have the user's IP address stored, we want to store that.
				// ------------------------------------------------------------------------
				// NOTE: We should probably set up a MySQL table to store the IP addresses,
				// 			 and change the field in user to "last_ip", so that we can keep 
				//			 track of who is logging in from what account, and make sure that
				//			 there's nothing suspicious happening.
				// ------------------------------------------------------------------------
				
				//if ($user->ip != get_ip_address()) {
				//	$user->ip = get_ip_address();
				//}
				
				$user->save();			
				$this->logged_in = true;
			}
		}
		
		/** 
		 * Logs the currently logged-in user out.
		 *
		 */
		public function logout() {
			// Unset the user ID from the cookie.
			unset($this->uid);
			
			// Set all cookies to expire immediately.
	    setcookie('PHPSESSID', 0);
			setcookie('fauth', 0);
			
			// Log the Session object out.
			$this->logged_in = false;
			
			// Unset all session variables.
			$_SESSION['uid'] = 0;
			$_SESSION['username'] = "";
			
			// Destroy the session.
			session_destroy();
		}
		
		/** 
			* Checks to see whether or not the user is currently logged in.
			*
			*/
		private function check_login() {
			// If we have a cookie, but the session variables are not set,
			// then we need to get the data and set the proper variables.
			if (!empty($_COOKIE['fauth'])) {
				$this->uid = $_COOKIE['fauth'];
			
				if (!isset($_SESSION['uid']) || 
						!isset($_SESSION['username']) || 
						$_SESSION['uid'] != $_COOKIE['fauth']) {
					// Find the user's object.
					$user = User::find_by_id($_COOKIE['fauth']);
					
					// Set the session variables.
					$_SESSION['uid'] = $user->id;
					$_SESSION['username'] = $user->username;
				}
			
				// Log the user in.
				$this->logged_in = true;
			} else {
				unset($this->uid);
				$this->logged_in = false;
			}
		}
		
		/** 
		* Sets the last page visited, so that we can easily send the user 
		* back to where they last were.
		*
		*/
		public function set_last_page() {
			$this->last_page = $_SERVER['PHP_SELF'];
			
			// If the GET array has "add" or "rem" values, we need to remove
			// those.
			if(isset($_GET)) { 
				if (array_key_exists('add', $_GET)) 
					unset($_GET['add']);
				if (array_key_exists('rem', $_GET))
					unset($_GET['rem']);
					
				// Set up an iterator and then loop through GET to get key-value
				// pairs, then append them to our last page.
				$i = 0;
				foreach ($_GET as $key=>$val) {
					$count = count($_GET);
					if ($count > 1) {
						if ($i == 0)
							$this->last_page .= '?' . $key . '=' . $val . '&';
						else if ($i == $count-1)
							$this->last_page .= $key . '=' . $val;
						else	
							$this->last_page .= $key . '=' . $val . '&';		
					} else
						$this->last_page .= '?' . $key . '=' . $val;
					
					$i++;
				}
			}
		$this->last_page = substr($this->last_page, 1);
		$_SESSION['last_page'] = $this->last_page;
		return $this->last_page;
		}
	}
	
	$session = new Session();
	?>