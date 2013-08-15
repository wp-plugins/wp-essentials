<?php
	include("../../../../wp-config.php");
	if (current_user_can('administrator')) {
		$table_name = $wpdb->prefix."wpe_twitter";
		
		function buildBaseString($baseURI, $method, $params) {
			$r = array(); ksort($params);
			foreach($params as $key=>$value){
				$r[] = "$key=" . rawurlencode($value);
			}
			return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
		}
		
		function buildAuthorizationHeader($oauth) {
			$r = 'Authorization: OAuth ';
			$values = array();
			foreach($oauth as $key=>$value) $values[] = "$key=\"" . rawurlencode($value) . "\""; $r .= implode(', ', $values); return $r;
		}
		
		$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
		
		$consumer_key = get_option('wpe_twitter_consumer_key','');
		$consumer_secret = get_option('wpe_twitter_consumer_secret','');
		$oauth_access_token = get_option('wpe_twitter_oauth_access_token','');
		$oauth_access_token_secret = get_option('wpe_twitter_oauth_access_token_secret','');
		
		$oauth = array(
			'oauth_consumer_key' => $consumer_key,
			'oauth_nonce' => time(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_token' => $oauth_access_token,
			'oauth_timestamp' => time(),
			'oauth_version' => '1.0'
		);
		
		$base_info = buildBaseString($url, 'GET', $oauth);
		$composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
		$oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
		$oauth['oauth_signature'] = $oauth_signature;
		
		// Make Requests
		$header = array(buildAuthorizationHeader($oauth), 'Expect:');
		$options = array(
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_HEADER => false,
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false
		);
		
		$feed = curl_init();
		curl_setopt_array($feed, $options);
		$json = curl_exec($feed);
		curl_close($feed);
		
		$data = json_decode($json);
		$total=count($data);
		$errors = $data->errors[0];
		if ($errors) {
			echo '<p>Twitter Error: '.$errors->message.'</p>';
		} else if ($total>0) {
			$wpdb->query('TRUNCATE TABLE '.$table_name);
			
			for($i=0;$i<$total;$i++){
				$name=$data[$i]->user->name;
				$content=$data[$i]->text;
				$status=$data[$i]->id_str;
				$posted=strtotime($data[$i]->created_at);
				
				$wpdb->query('INSERT INTO '.$table_name.' VALUES ("","'.$name.'","'.$content.'","'.$status.'","'.$posted.'",NOW())');
			}
		}
	} else {
		header("location: http://".$_SERVER['SERVER_NAME']);
	}