<?php
	if (!file_exists('lib/init/config.php') && !file_exists('lib/init/constants.php')) {
		header('Location: install');
	} else {
		if (is_dir('install')) {
			require_once('lib'.DS.'classes'.DS.'generics'.DS.'functions.php');
			
			if (rrmdir('install')) {
				unlink('router.php');
				header('Location: index.php');
			} else {
				echo "Error. Please manually delete the install folder and router.php";
			}
		}
	}
?>