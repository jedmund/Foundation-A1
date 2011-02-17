<?php
	require_once('../../lib/init/config.php');
	
	$transitions = array(array("name" => "None", 			  			"value" => "none"),
											 array("name" => "Fade", 			  			"value" => "fade"), 
											 array("name" => "Scroll Up",   			"value" => "scrollUp"),
											 array("name" => "Scroll Down", 			"value" => "scrollDown"),
											 array("name" => "Scroll Left", 			"value"	=> "scrollLeft"),
											 array("name" => "Scroll Right", 			"value" => "scrollRight"),
											 array("name" => "Scroll Horizontal", "value" => "scrollHorz"),
											 array("name" => "Cover", 						"value" => "cover"),
											 array("name" => "Uncover", 					"value" => "uncover")
									 );

	if (!empty($_POST) && !empty($_POST['submit'])) {
		foreach ($_POST as $key => $value) {
			if ($setting = Setting::find_by_name($key)) {
				if ($value == "on") {
					$value = 1;
				}
				
				if ($key == "slideshow_transition") {
					foreach ($transitions as $transition) {
						if ($transition['name'] == $value) {
							$value = $transition['value'];
						}
					}
				}
				
				if ($setting->get_value() != $value) {
					echo $key . ": " . $value . "<br>";
					$setting->set_value($value);
					
					if ($setting->save()) {
						echo "Saved!";
					} else {
						echo "Error.";
					}
				}
			}
		}
				
		if (!isset($_POST['slideshow_autoplay'])) {
			$setting = Setting::find_by_name('slideshow_autoplay');
			$setting->set_value(0);
			$setting->save();
		}
		
		if (!isset($_POST['slideshow_text_nav'])) {
			$setting = Setting::find_by_name('slideshow_text_nav');
			$setting->set_value(0);
			$setting->save();
		}
	}
	
	redirect_to('index.php');
?>