<?php

	$sql = array();
	
	/** 
	 * Create tables
	 *
	 * System Tables
	 *** collaborations, fields, foundations, ideas, images, notices, 
	 *** pages, projects, queue, settings, tags, tag_associations, users
	 *
	 * Foundation tables
	 *** f_apparel, f_building_type, f_camers, f_fabric, f_lens, 
	 *** f_manufacturing_process, f_medium, f_paper, f_print_format,
	 *** f_print_process, f_product_materials, f_product_type, 
	 *** f_programming_language, f_software, f_usage
	 *
	 */
	$sql[] = "CREATE TABLE `collaborations` (
						  `pid` int(11) NOT NULL DEFAULT '0',
						  `uid` int(11) NOT NULL DEFAULT '0'
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
						
	
	$sql[] = "CREATE TABLE `f_apparel` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `value` varchar(300) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `f_building_type` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `value` varchar(300) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `f_cameras` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `value` varchar(300) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `f_fabric` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `value` varchar(300) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `f_lens` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `value` varchar(300) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `f_manufacturing_process` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `value` varchar(300) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `f_medium` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `value` varchar(300) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `f_paper` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `value` varchar(300) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `f_print_format` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `value` varchar(300) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `f_printing_process` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `value` varchar(300) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `f_product_materials` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `value` varchar(300) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `f_product_type` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `value` varchar(300) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `f_programming_languages` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `value` varchar(300) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `f_software` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `value` varchar(300) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `f_usage` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `value` varchar(300) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `fields` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `name` varchar(200) DEFAULT NULL,
						  `label` varchar(200) DEFAULT NULL,
						  `placeholder` text,
						  `type` varchar(100) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `foundations` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `uid` int(11) NOT NULL DEFAULT '1',
						  `name` varchar(80) NOT NULL DEFAULT '',
						  `props` text NOT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `ideas` (
						  `id` int(11) DEFAULT NULL,
						  `uid` int(11) DEFAULT '0',
						  `archived` tinyint(1) DEFAULT '0',
						  `text` text COMMENT '	',
						  `date_created` varchar(128) DEFAULT NULL,
						  `last_modified` varchar(128) DEFAULT NULL
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `images` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `pid` int(11) DEFAULT '0' COMMENT '	',
						  `sequence` int(11) DEFAULT '0',
						  `full` varchar(400) DEFAULT NULL,
						  `thumb` varchar(400) DEFAULT NULL,
						  `small` varchar(400) DEFAULT NULL,
						  `medium` varchar(400) DEFAULT NULL,
						  `large` varchar(400) DEFAULT NULL,
						  `xlarge` varchar(400) DEFAULT NULL,
						  `caption` varchar(140) DEFAULT NULL,
						  `link` varchar(400) DEFAULT NULL,
						  `default_image` tinyint(1) DEFAULT '0' COMMENT '	',
						  `coords` varchar(400) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `notices` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `type` tinyint(2) DEFAULT '0' COMMENT '		',
						  `text` text,
						  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
						  `viewed` tinyint(1) DEFAULT '0',
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `pages` (
						  `id` int(11) DEFAULT NULL,
						  `uid` int(11) DEFAULT NULL,
						  `title` varchar(300) DEFAULT NULL,
						  `body` text
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `projects` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `fid` int(11) DEFAULT '0',
						  `uid` int(11) DEFAULT '0',
						  `title` varchar(80) DEFAULT NULL,
						  `blurb` varchar(500) DEFAULT NULL,
						  `description` text,
						  `sequence` int(11) NOT NULL DEFAULT '0',
						  `completed` datetime DEFAULT '0000-00-00 00:00:00',
						  `client` varchar(140) DEFAULT NULL,
						  `slug` varchar(80) DEFAULT NULL,
						  `thumb` varchar(300) NOT NULL DEFAULT '/admin/system/img/nothumb.png',
						  `archived` tinyint(1) DEFAULT '0',
						  `template` varchar(200) NOT NULL DEFAULT 'default',
						  `path` varchar(400) DEFAULT NULL,
						  `print_format` text,
						  `paper` text,
						  `printing_process` text,
						  `software` text,
						  `website` varchar(200) DEFAULT NULL,
						  `programming_languages` text,
						  `cameras` text,
						  `film` text,
						  `music` text,
						  `apparel` text,
						  `fabric` text,
						  `product_type` varchar(200) DEFAULT NULL,
						  `manufacturing_process` text,
						  `product_materials` text,
						  `lens` text,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `queue` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `priority` tinyint(2) DEFAULT '1' COMMENT '		',
						  `script` varchar(200) DEFAULT NULL,
						  `params` text,
						  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
						  `date_execute` timestamp NULL DEFAULT NULL,
						  `date_completed` timestamp NULL DEFAULT NULL,
						  `completed` tinyint(2) DEFAULT '0',
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `settings` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `name` varchar(128) NOT NULL DEFAULT '',
						  `value` longtext NOT NULL,
						  `autoload` tinyint(1) DEFAULT '0',
						  PRIMARY KEY (`id`),
						  UNIQUE KEY `name` (`name`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `tag_associations` (
						  `tid` int(11) DEFAULT NULL,
						  `pid` int(11) DEFAULT '0'
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `tags` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `name` varchar(40) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	
	$sql[] = "CREATE TABLE `users` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `fbid` int(20) DEFAULT '0',
						  `oauth_token` varchar(30) DEFAULT NULL,
						  `oauth_secret` varchar(30) DEFAULT NULL,
						  `username` varchar(50) NOT NULL DEFAULT '',
						  `password` varchar(50) NOT NULL DEFAULT '' COMMENT '		',
						  `email` varchar(100) NOT NULL DEFAULT '',
						  `first_name` varchar(50) DEFAULT NULL,
						  `last_name` varchar(50) DEFAULT NULL,
						  `birthday` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '		',
						  `city` varchar(80) DEFAULT NULL,
						  `country` varchar(80) DEFAULT NULL,
						  `level` int(5) NOT NULL DEFAULT '1',
						  `last_active` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						  `bio` text,
						  `social` text,
						  `resume` varchar(400) DEFAULT NULL,
						  `portfolio` varchar(400) DEFAULT NULL,
						  `photo` varchar(400) DEFAULT NULL,
						  `status` varchar(400) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;";


	// Insert default data
	$sql[] = "INSERT INTO `f_apparel` (`id`, `value`) VALUES (1, 'Sportswear');";
	$sql[] = "INSERT INTO `f_apparel` (`id`, `value`) VALUES (2, 'Resortwear');";
	$sql[] = "INSERT INTO `f_apparel` (`id`, `value`) VALUES (3, 'Nightwear');";
	$sql[] = "INSERT INTO `f_apparel` (`id`, `value`) VALUES (4, 'Underwear');";
	$sql[] = "INSERT INTO `f_apparel` (`id`, `value`) VALUES (5, 'Costume Design');";
	$sql[] = "INSERT INTO `f_apparel` (`id`, `value`) VALUES (6, 'Workwear');";
	$sql[] = "INSERT INTO `f_apparel` (`id`, `value`) VALUES (7, 'Accessories');";
	$sql[] = "INSERT INTO `f_apparel` (`id`, `value`) VALUES (8, 'Apparel');";
	
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (1, 'Nikon D60');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (2, 'Nikon D70');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (3, 'Nikon D80');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (4, 'Nikon D90');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (6, 'Canon EOS-1Ds Mark IV');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (5, 'Canon EOS-1Ds Mark III');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (7, 'Nikon D3');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (9, 'Nikon D3x');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (8, 'Nikon D3S');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (11, 'D300S');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (12, 'D700');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (10, 'D300');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (13, 'D3000');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (14, 'D5000');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (15, 'D7000');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (16, 'D3100');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (17, 'Canon EOS 5D Mark II');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (18, 'Canon EOS 5D');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (19, 'Canon EOS 7D');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (20, 'Canon EOS 60D');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (21, 'Canon EOS 50D');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (22, 'Leica M9');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (23, 'Leica M8');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (24, 'Leica M7');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (25, 'Leica MP');";
	$sql[] = "INSERT INTO `f_cameras` (`id`, `value`) VALUES (26, 'Leica S2');";
	
	$sql[] = "INSERT INTO `f_paper` (`id`, `value`) VALUES (1, 'White');";
	$sql[] = "INSERT INTO `f_paper` (`id`, `value`) VALUES (2, 'Inkpress Duo Matte');";
	$sql[] = "INSERT INTO `f_paper` (`id`, `value`) VALUES (3, 'Vinyl');";
	$sql[] = "INSERT INTO `f_paper` (`id`, `value`) VALUES (4, 'Trace Vellum');";
	$sql[] = "INSERT INTO `f_paper` (`id`, `value`) VALUES (5, 'Coated Canvas');";
	$sql[] = "INSERT INTO `f_paper` (`id`, `value`) VALUES (6, 'Chalk Paper');";
	
	$sql[] = "INSERT INTO `f_print_format` (`id`, `value`) VALUES (1, 'Brochure');";
	$sql[] = "INSERT INTO `f_print_format` (`id`, `value`) VALUES (2, 'Clothing');";
	$sql[] = "INSERT INTO `f_print_format` (`id`, `value`) VALUES (3, 'Poster');";
	$sql[] = "INSERT INTO `f_print_format` (`id`, `value`) VALUES (4, 'Original Goods');";
	$sql[] = "INSERT INTO `f_print_format` (`id`, `value`) VALUES (5, 'Video');";
	$sql[] = "INSERT INTO `f_print_format` (`id`, `value`) VALUES (6, 'Book');";
	$sql[] = "INSERT INTO `f_print_format` (`id`, `value`) VALUES (7, 'Magazine');";
	$sql[] = "INSERT INTO `f_print_format` (`id`, `value`) VALUES (8, 'Installation');";
	$sql[] = "INSERT INTO `f_print_format` (`id`, `value`) VALUES (9, 'Hang Tags');";
	$sql[] = "INSERT INTO `f_print_format` (`id`, `value`) VALUES (10, 'Business Cards');";
	$sql[] = "INSERT INTO `f_print_format` (`id`, `value`) VALUES (11, 'Website');";
	$sql[] = "INSERT INTO `f_print_format` (`id`, `value`) VALUES (12, 'Newspaper');";
	
	$sql[] = "INSERT INTO `f_printing_process` (`id`, `value`) VALUES (1, 'Letterpress');";
	$sql[] = "INSERT INTO `f_printing_process` (`id`, `value`) VALUES (2, 'Offset Printing');";
	$sql[] = "INSERT INTO `f_printing_process` (`id`, `value`) VALUES (3, 'Laser Printing');";
	$sql[] = "INSERT INTO `f_printing_process` (`id`, `value`) VALUES (4, 'Screenprinting');";
	$sql[] = "INSERT INTO `f_printing_process` (`id`, `value`) VALUES (5, 'Inkjet');";
	
	$sql[] = "INSERT INTO `f_programming_languages` (`id`, `value`) VALUES (1, 'HTML');";
	$sql[] = "INSERT INTO `f_programming_languages` (`id`, `value`) VALUES (2, 'CSS');";
	$sql[] = "INSERT INTO `f_programming_languages` (`id`, `value`) VALUES (3, 'Javascript');";
	$sql[] = "INSERT INTO `f_programming_languages` (`id`, `value`) VALUES (4, 'jQuery');";
	$sql[] = "INSERT INTO `f_programming_languages` (`id`, `value`) VALUES (5, 'C++');";
	$sql[] = "INSERT INTO `f_programming_languages` (`id`, `value`) VALUES (6, 'Java');";
	$sql[] = "INSERT INTO `f_programming_languages` (`id`, `value`) VALUES (7, 'Processing');";
	$sql[] = "INSERT INTO `f_programming_languages` (`id`, `value`) VALUES (8, 'openFrameworks');";
	$sql[] = "INSERT INTO `f_programming_languages` (`id`, `value`) VALUES (9, 'Objective-C');";
	$sql[] = "INSERT INTO `f_programming_languages` (`id`, `value`) VALUES (10, 'Ruby on Rails');";
	$sql[] = "INSERT INTO `f_programming_languages` (`id`, `value`) VALUES (11, 'Python');";
	$sql[] = "INSERT INTO `f_programming_languages` (`id`, `value`) VALUES (12, 'PHP');";
	$sql[] = "INSERT INTO `f_programming_languages` (`id`, `value`) VALUES (13, 'MySQL');";
	$sql[] = "INSERT INTO `f_programming_languages` (`id`, `value`) VALUES (14, 'Ruby');";
	
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (1, 'Adobe Photoshop');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (2, 'Adobe Illustrator');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (3, 'Adobe InDesign');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (4, 'Adobe AfterEffects');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (5, 'Adobe Dreamweaver');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (6, 'Adobe Premiere');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (7, 'Adobe Fireworks');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (8, 'Adobe Soundbooth');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (9, 'SolidWorks');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (10, 'Autodesk Maya');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (11, 'Autodesk Sketchbook Pro');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (12, 'Autodesk 3ds Max');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (13, 'Coda');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (14, 'Textmate');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (15, 'MAXON CINEMA4D');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (16, 'Vray');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (17, 'Maxwell Render');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (18, 'Final Cut Pro');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (19, 'Motion');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (20, 'Boinx iStopMotion');";
	$sql[] = "INSERT INTO `f_software` (`id`, `value`) VALUES (21, 'Querious');";
	
	$sql[] = "INSERT INTO `f_usage` (`id`, `value`) VALUES (1, 'Newspaper');";
	$sql[] = "INSERT INTO `f_usage` (`id`, `value`) VALUES (2, 'Poster');";
	$sql[] = "INSERT INTO `f_usage` (`id`, `value`) VALUES (3, 'Magazine');";
	$sql[] = "INSERT INTO `f_usage` (`id`, `value`) VALUES (4, 'Brochure');";
	
	$sql[] = "INSERT INTO `fields` (`id`, `name`, `label`, `placeholder`, `type`) VALUES (1, 'website', 'Website', 'What is this project\'s web address?', 'finput');";
	$sql[] = "INSERT INTO `fields` (`id`, `name`, `label`, `placeholder`, `type`) VALUES (2, 'software', 'Software', 'What software was used?', 'fstinput');";
	$sql[] = "INSERT INTO `fields` (`id`, `name`, `label`, `placeholder`, `type`) VALUES (3, 'programming_languages', 'Programming Languages', 'What programming languages were used?', 'fstinput');";
	$sql[] = "INSERT INTO `fields` (`id`, `name`, `label`, `placeholder`, `type`) VALUES (4, 'product_type', 'Product', 'What kind of product is this?', 'fstinput');";
	$sql[] = "INSERT INTO `fields` (`id`, `name`, `label`, `placeholder`, `type`) VALUES (5, 'paper', 'Paper', 'What paper was used?', 'fstinput');";
	$sql[] = "INSERT INTO `fields` (`id`, `name`, `label`, `placeholder`, `type`) VALUES (6, 'print_format', 'Format', 'What formats were used?', 'fstinput');";
	$sql[] = "INSERT INTO `fields` (`id`, `name`, `label`, `placeholder`, `type`) VALUES (7, 'product_materials', 'Materials', 'What was it made of?', 'fstinput');";
	$sql[] = "INSERT INTO `fields` (`id`, `name`, `label`, `placeholder`, `type`) VALUES (8, 'manufacturing_process', 'Manufacturing Process', 'How was this manufactured?', 'fstinput');";
	$sql[] = "INSERT INTO `fields` (`id`, `name`, `label`, `placeholder`, `type`) VALUES (9, 'printing_process', 'Printing Process', 'How was this printed?', 'fstinput');";
	$sql[] = "INSERT INTO `fields` (`id`, `name`, `label`, `placeholder`, `type`) VALUES (10, 'fabric', 'Fabric', 'What fabric was used?', 'fstinput');";
	$sql[] = "INSERT INTO `fields` (`id`, `name`, `label`, `placeholder`, `type`) VALUES (11, 'apparel', 'Apparel', 'What kind of apparel is this?', 'fstinput');";
	$sql[] = "INSERT INTO `fields` (`id`, `name`, `label`, `placeholder`, `type`) VALUES (12, 'cameras', 'Cameras', 'What kind of cameras were used?', 'fstinput');";
	$sql[] = "INSERT INTO `fields` (`id`, `name`, `label`, `placeholder`, `type`) VALUES (13, 'lens', 'Lens', 'What lenses were used?', 'fstinput');";
	$sql[] = "INSERT INTO `fields` (`id`, `name`, `label`, `placeholder`, `type`) VALUES (14, 'medium', 'Medium', 'What mediums were used?', 'fstinput');";
	$sql[] = "INSERT INTO `fields` (`id`, `name`, `label`, `placeholder`, `type`) VALUES (15, 'usage', 'Usage', 'What was the project used for?', 'fstinput');";
	$sql[] = "INSERT INTO `fields` (`id`, `name`, `label`, `placeholder`, `type`) VALUES (16, 'building_type', 'Building Type', 'What kind of building is this?', 'fstinput');";

	$sql[] = "INSERT INTO `foundations` (`id`, `uid`, `name`, `props`) VALUES (1, 1, 'Graphic Design', '[2,5,6,9]');";
	$sql[] = "INSERT INTO `foundations` (`id`, `uid`, `name`, `props`) VALUES (2, 1, 'Product Design', '[2,4,7,8]');";
	$sql[] = "INSERT INTO `foundations` (`id`, `uid`, `name`, `props`) VALUES (3, 1, 'Interaction Design', '[1,2,3]');";
	$sql[] = "INSERT INTO `foundations` (`id`, `uid`, `name`, `props`) VALUES (4, 1, 'Fashion Design', '[10,11]');";
	$sql[] = "INSERT INTO `foundations` (`id`, `uid`, `name`, `props`) VALUES (5, 1, 'Interior Design', '');";
	$sql[] = "INSERT INTO `foundations` (`id`, `uid`, `name`, `props`) VALUES (6, 1, 'Motion Design', '[2,12,13]');";
	$sql[] = "INSERT INTO `foundations` (`id`, `uid`, `name`, `props`) VALUES (7, 1, 'Web Design', '[1,2,3]');";
	$sql[] = "INSERT INTO `foundations` (`id`, `uid`, `name`, `props`) VALUES (8, 1, 'Fine Art', '[14,15]');";
	$sql[] = "INSERT INTO `foundations` (`id`, `uid`, `name`, `props`) VALUES (9, 1, 'Illustration', '[14,15]');";
	$sql[] = "INSERT INTO `foundations` (`id`, `uid`, `name`, `props`) VALUES (10, 1, 'Photography', '[2,12,13]');";
	$sql[] = "INSERT INTO `foundations` (`id`, `uid`, `name`, `props`) VALUES (11, 1, 'Architecture', '[16]');";

	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (1, 'title', 'My Portfolio', 1);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (2, 'header', '', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (3, 'favicon', '', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (4, 'start_page', 'first project', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (5, 'pagination', '10', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (6, 'thumbnail_width', '300', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (7, 'thumbnail_height', '200', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (8, 'image_sm_width', '300', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (9, 'image_md_width', '600', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (10, 'image_lg_width', '900', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (11, 'image_xl_width', '1200', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (12, 'slideshow_autoplay', '0', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (13, 'slideshow_delay', '2000', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (14, 'slideshow_text_nav', '1', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (15, 'slideshow_text_nav_prev', 'Previous', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (16, 'slideshow_text_nav_next', 'Next', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (17, 'slideshow_transition', 'fade', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (18, 'slideshow_pager', '0', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (19, 'layout', 'default', 0);";
	$sql[] = "INSERT INTO `settings` (`id`, `name`, `value`, `autoload`) VALUES (20, 'title_delimiter', 'on', 0);";