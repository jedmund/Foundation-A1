<?php
	require_once('../lib/init/config.php');
	
	$images = Image::find_all();
	foreach ($images as $image) {
		// Get the image sizes from settings.
		$setting = Setting::find_by_name('image_sm_width');
		$sizes['sm'] = $setting->get_value();
		
		$setting = Setting::find_by_name('image_md_width');
		$sizes['md'] = $setting->get_value();
		
		$setting = Setting::find_by_name('image_lg_width');
		$sizes['lg'] = $setting->get_value();
		
		$setting = Setting::find_by_name('image_xl_width');
		$sizes['xl'] = $setting->get_value();
	
		$path = PUBLIC_PATH.$image->full;
		echo $path . "<br>";

		list($width, $height) = getimagesize($path);
						
		if ($width > $sizes['sm']) {
			$image->small = $image->scale('sm', $sizes['sm']);
			$image->small = substr($image->small, strpos($image->small, '/content'));
		}
		
		if ($width > $sizes['md']) {
			$image->medium = $image->scale('md', $sizes['md']);
			$image->medium = substr($image->medium, strpos($image->medium, '/content'));
		}
		
		if ($width > $sizes['lg']) {
			$image->large = $image->scale('lg', $sizes['lg']);
			$image->large = substr($image->large, strpos($image->large, '/content'));
		}
		
		if ($width > $sizes['xl']) {
			$image->xlarge = $image->scale('xl', $sizes['xl']);
			$image->xlarge = substr($image->xlarge, strpos($image->xlarge, '/content'));
		}
		
		$image->save();
	}