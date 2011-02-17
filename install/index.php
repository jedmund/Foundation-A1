<?php	
	if (file_exists('lib/init/config.php')) {
		echo "Don't run this.";
	} else if (empty($_POST)) {
		$form = file_get_contents('install.htm');
		echo $form;
	} else if (!empty($_POST) && !empty($_POST['submit'])) {
		$dbserver 	= stripslashes($_POST['db_server']);
		$dbusername = stripslashes($_POST['db_username']);
		$dbpassword = stripslashes($_POST['db_password']);
		$dbname 		= stripslashes($_POST['db_name']);
		
		$constants = file_get_contents('constants.txt');		
		$constants = str_replace('dbserver', '"' . $dbserver . '"', $constants);
		$constants = str_replace('dbuser', '"' . $dbusername . '"', $constants);	
		$constants = str_replace('dbpass', '"' . $dbpassword . '"', $constants);
		$constants = str_replace('dbname', '"' . $dbname . '"', $constants);
		
		$fp = "../lib/init/constants.php";
		if ($file = fopen($fp, "w")) {
			fwrite($file, $constants);
			chmod($fp, 0755);
			fclose($file);
		}

		$path = explode("/", getcwd());
		$key = array_shift($path);
		$key = array_pop($path);		
		$path = "DS.'" . implode("'.DS.'", $path) . "'";
		
		$domain = $_SERVER['HTTP_REFERER'];
		$index = strpos($domain, "install");
		$domain = substr($domain, 0, $index);		
		
		$config = file_get_contents('config.txt');
		$config = str_replace('siteroot', $path, $config);
		$config = str_replace('sitedomain', $domain, $config);
		
		$fp = "../lib/init/config.php";
		if ($file = fopen($fp, "w")) {
			fwrite($file, $config);
			chmod($fp, 0755);
			fclose($file);
		}
		
		include($fp);
		include('db.php');
		
		for ($i = 0; $i < count($sql); $i++) {
			if (!$db->query($sql[$i])) {
				// Throw proper error
				echo "Error.";
				die();
			}
		}
		
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
		}
		
		// Remove installer and root .htaccess
		unlink($path."/install", "*.php");
		unlink($path."/.htaccess");
		rmdir($path."/install");

		echo "Done!";
	}

?>