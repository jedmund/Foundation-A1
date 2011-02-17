<?php
	require_once('../lib/init/config.php');
	
	if ($session->is_logged_in()) 
		$session->logout();
		
	redirect_to("../index.php");
?>