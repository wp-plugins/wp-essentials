<?php
	if (current_user_can('administrator')) {
		$table_name = $wpdb->prefix."wpe_instagram";
		function fetchData($url){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
			return $result;
		}
		$result = fetchData("https://api.instagram.com/v1/users/".get_option('wpe_instagram_user_id')."/media/recent/?access_token=".get_option('wpe_instagram_access_token'));
		$result = json_decode($result);
		
		if ($result) {
			$wpdb->query('TRUNCATE TABLE '.$table_name);
			
			foreach ($result->data as $post) {
				$wpdb->query('INSERT INTO '.$table_name.' VALUES ("","'.$post->link.'","'.$post->images->thumbnail->url.'","'.$post->caption->text.'",NOW())');
			}
		}
	} else {
		header("location: http://".$_SERVER['SERVER_NAME']);
	}