<?php
	if (file_exists('lib/init/config.php')) {
		echo "Don't run this.";
	} else if (empty($_POST)) {
		$form = file_get_contents('install.htm');
		echo $form;
	} else if (!empty($_POST) && !empty($_POST['submit'])) {
		// Clean the values.
		$dbserver 	= stripslashes($_POST['db_server']);
		$dbusername = stripslashes($_POST['db_username']);
		$dbpassword = stripslashes($_POST['db_password']);
		$dbname 		= stripslashes($_POST['db_name']);

		// Get the structure of the constants.php file and replace its
		// placeholder text with the values we cleaned.
		$constants = file_get_contents('constants.txt');
		$constants = str_replace('dbserver', '"' . $dbserver . '"', $constants);
		$constants = str_replace('dbuser', '"' . $dbusername . '"', $constants);
		$constants = str_replace('dbpass', '"' . $dbpassword . '"', $constants);
		$constants = str_replace('dbname', '"' . $dbname . '"', $constants);

		// Open a file path to where the actual constants.php should be and
		// write our values.
		$fp = "../lib/init/constants.php";
		if ($file = fopen($fp, "w")) {
			fwrite($file, $constants);
			chmod($fp, 0755);
			fclose($file);
		}

		// Explode the results of getcwd() to get the absolute path, then
		// replace with directory separators.
		$path = explode("/", getcwd());
		$key = array_shift($path);
		$key = array_pop($path);
		
		$path = "DS.'" . implode("'.DS.'", $path) . "'";
		
		// Get the domain name and whatnot from the SERVER superglobal.
		$domain = $_SERVER['HTTP_REFERER'];
		$index = strpos($domain, "install");
		$domain = substr($domain, 0, $index);

		// Get the structure of the config.txt file and replace its
		// placeholder text with the values we calculated.
		$config = file_get_contents('config.txt');
		$config = str_replace('siteroot', $path, $config);
		$config = str_replace('sitedomain', $domain, $config);

		// Open a file path to where the actual config.php should be and
		// write our values.
		$fp = "../lib/init/config.php";
		if ($file = fopen($fp, "w")) {
			fwrite($file, $config);
			chmod($fp, 0755);
			fclose($file);
		}

		// Include the newly created config.php file, as well as the
		// database installer file.
		include($fp);
		include('db.php');

		// Fix path so we can use it now.
		$path = str_replace(array(".DS.", "DS."), DIRECTORY_SEPARATOR, $path);
		$path = str_replace("'", "", $path);
		
		// Get the structure of the root .htaccess file and keep it in
		// memory.
		$htaccess = file_get_contents('htaccess.txt');

		// Open a file to where the .htaccess file should be in root and
		// write our values.
		$fp = "../.htaccess";
		if ($file = fopen($fp, "w")) {
			fwrite($file, $htaccess);
			fclose($file);
		}

		// Execute every query, one-by-one.
		for ($i = 0; $i < count($sql); $i++) {
			if (!$db->query($sql[$i])) {
				// Throw proper error
				echo "Error.";
				die();
			}
		}

		// Check to make sure that the user has supplied matching passwords.
		// If they have, create a new user. Explicitly set the UID since
		// Foundation is in forced single-user mode right now.
		if ($_POST['password'] != $_POST['confirmpassword']) {
			// Throw proper error
			echo "Error: Passwords don't match.";
			die();
		} else {
			$user = new User;
			$user->username = $db->escape_value($_POST['username']);
			
			$salt = hash("sha1", strtolower($user->username));
			$password = crypt($_POST['password'], $salt);
			$user->password = $password;
			
			$user->save();
			// Also, create a user folder.
			mkdir($path.'/content/'.$user->username);
			chmod($path.'/content/'.$user->username, 0777);
		}

		// Recursively remove the installer folder and all of its contents.
		rrmdir($path.DS."install");
		unlink($path.DS."router.php");

		// Redirect the user to the login page.
		header('Location: /admin/login.php');
	}

?>
