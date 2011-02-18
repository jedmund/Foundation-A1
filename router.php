<?php
	if (!file_exists('lib/init/config.php') && !file_exists('lib/init/constants.php')) {
		header('Location: install');
	} else {
		if (is_dir('install')) {
			$ds = DIRECTORY_SEPARATOR;
			require_once('lib'.$ds.'classes'.$ds.'generics'.$ds.'functions.php');
			
			$res = rrmdir('install');
			if (!is_dir('install')) {
				chmod('router.php', 0777);
				if (unlink('router.php')) {
					header('Location: index.php');
				} else {
					echo "Error. Please manually delete router.php";
				}
			} else {
				echo "Error. Please manually delete the install folder and router.php";
			}
		}
	}
?>