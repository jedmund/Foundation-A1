<?php
	require_once('../lib/init/config.php');

	$template = $twig->loadTemplate('jstest.html');
	echo $template->render(array());
?>