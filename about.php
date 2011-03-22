<?php
	require_once('lib/init/config.php');

	// Find a better way to get this.
	$user = User::find_by_id(10);

	// On error, we should render a 404, since we won't have conditionals.
	$setting = Setting::find_by_name('layout');
	$layout = $setting->get_value();

	$page = array_shift(explode(".", basename(__FILE__)));

	// On error, we should render a 404, since we won't have conditionals.
	$setting = Setting::find_by_name('layout');
	$layout = $setting->get_value();

	$page = array_shift(explode(".", basename(__FILE__)));

	$parsel = new Parsel($layout, $page, $user->id);
	$parsel->render();
?>