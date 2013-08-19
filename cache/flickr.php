<?php
	include("../../../../wp-config.php");
	if (current_user_can('administrator')) {
		global $wpdb;
	
		$table_name = $wpdb->prefix."wpe_flickr";
		require_once("../plugins/phpFlickr/phpFlickr.php");
	
		$f = new phpFlickr(get_option('wpe_flickr_api'));
	
		$i = 0;
		$person = $f->people_findByUsername(get_option('wpe_flickr_username',''));
		$photos_url = $f->urls_getUserPhotos($person['id']);
		$photos = $f->people_getPublicPhotos($person['id'], NULL, NULL, 20);
			
		$wpdb->query('TRUNCATE TABLE '.$table_name);
	 
		foreach ((array)$photos['photos']['photo'] as $photo) {
			$wpdb->query('INSERT INTO '.$table_name.' VALUES ("","'.get_option('wpe_flickr_username','').'","'.$photos_url.''.$photo['id'].'","'.$photo[title].'","'.$f->buildPhotoURL($photo, "Square").'",NOW())');
		}
	} else {
		header("location: http://".$_SERVER['SERVER_NAME']);
	}