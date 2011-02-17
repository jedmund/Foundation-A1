<?php 
	require_once('lib/init/config.php');

	// On error, we should render a 404, since we won't have conditionals. 
	$setting = Setting::find_by_name('layout');
	$layout = $setting->get_value();
	
	$page = array_shift(explode(".", basename(__FILE__)));
	
	// On error, we should render a 404, since we won't have conditionals. 
	$setting = Setting::find_by_name('layout');
	$layout = $setting->get_value();
	
	$page = array_shift(explode(".", basename(__FILE__)));
	
	$parsel = new Parsel($layout, $page, 0, $_SERVER['REQUEST_URI']);		
	$parsel->render();