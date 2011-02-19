<?php
	if (!file_exists('lib/init/config.php') && !file_exists('lib/init/constants.php')) {
		header('Location: install');
	} else {
		// If the install folder exists, then we include our generic 
		// functions so that we can use rrmdir().
		if (is_dir('install')) {
			$ds = DIRECTORY_SEPARATOR;
			require_once('lib'.$ds.'classes'.$ds.'generics'.$ds.'functions.php');
			
			// We should first try to recursively try to remove the directory.
			// Suppressed so the user doesn't see the warnings. We'll catch 
			// potential failures in the next step.
			$res = @rrmdir('install');
			
			// If after deleting the directory and its contents via rrmdir(),
			// the install folder still exists, we error out. 
			if (!is_dir('install')) {
				// We should try to fix the .htaccess file so that the server 
				// never tries to access router.php again, but if we can't it
				// isn't a fatal error.
				$fh = fopen('.htaccess', 'w');
				$fr = fwrite($fh, "DirectoryIndex index.php");
				
				// If we do succeed in writing, we should change the permissions
				// so that anyone malicious cannot modify our .htaccess to point
				// to bad places.
				if ($fr) {
					@chmod('.htaccess', 0755);
				}
				
				fclose($fh);
			
				// It is however, crucial that we remove this file. If we cannot,
				// then we error out. Otherwise, we redirect the user home.
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