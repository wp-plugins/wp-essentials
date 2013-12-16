<?php
	// Database Set up
		if (get_option('wpe_twitter_username')) {
			global $wpdb;
			
			$table_name = $wpdb->prefix."wpe_twitter";
			
			$sql = "CREATE TABLE ".$table_name." (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				name VARCHAR(255) DEFAULT '' NOT NULL,
				content VARCHAR(255) DEFAULT '' NOT NULL,
				status VARCHAR(255) DEFAULT '' NOT NULL,
				posted VARCHAR(255) DEFAULT '' NOT NULL,
				added VARCHAR(255) DEFAULT '' NOT NULL,
				UNIQUE KEY id (id)
			);";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
		
	// Shortcode Setup
		// Twitter
			if (!function_exists('twitter')) {
				function twitter($atts) {
					global $wpdb;
					
					$table_name = $wpdb->prefix."wpe_twitter";
					
					extract(
						shortcode_atts(
							array(
								'count' => 3,
								'order' => null,
								'class' => 'wpe_twitter'
							),
							$atts
						 )
					);
					
					// Cache Check
					$check = $wpdb->get_row('SELECT * FROM '.$table_name.' LIMIT 1');
					$now = strtotime('-15 minutes');
					$last_update = strtotime($check->added);
					if ($now > $last_update) {
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
					}
					
					// Get Tweets
					if ($order=="random") {
						$twitter = $wpdb->get_results('SELECT * FROM '.$table_name.' ORDER BY RAND() LIMIT '.$count);
					} else {
						$twitter = $wpdb->get_results('SELECT * FROM '.$table_name.' LIMIT '.$count);
					}
					
					$tweets = '<ul class="'.$class.'">';
					foreach($twitter as $tweet) {
						$tweets .= '<li><span class="wpe_twitter_author"><a href="http://twitter.com/'.get_option('wpe_twitter_username','').'" target="_blank" rel="nofollow" title="'.get_option('twitter_username','').'">'.get_option('wpe_twitter_username','').'</a></span> '.link_tweet($tweet->content).' <span class="wpe_twitter_date"><a href="http://twitter.com/'.get_option('wpe_twitter_username','').'/status/'.$tweet->status.'" target="_blank" rel="nofollow" title="'.date('D, j M Y h:i:s T',$tweet->posted).'">'.relative_time($tweet->posted).'</a></span></li>
						';
					}
					$tweets .= '</ul>
					';
					return $tweets;
				}
				add_shortcode('twitter','twitter');
			}
			
		// Post Tweets
			if (!function_exists('post_to_twitter')) {
				if (get_option('wpe_twitter_username')) {
					add_action('post_submitbox_misc_actions', 'post_to_twitter');
					add_action('save_post', 'save_post_to_twitter');
					
					function post_to_twitter() {
						global $post;
						if (get_post_type($post) == 'post') {
							echo '<div class="misc-pub-section misc-pub-section-last" style="border-bottom: 1px solid #dfdfdf;">';
							wp_nonce_field( plugin_basename(__FILE__), 'post_to_twitter_nonce' );
							echo '<input type="checkbox" name="post_to_twitter" id="post_to_twitter" value="1" /> <label for="post_to_twitter" class="select-it">Post to Twitter</label>';
							echo '</div>';
						}
					}
					
					function save_post_to_twitter($post_id) {
						if (!isset($_POST['post_type'])) { return $post_id; }
						if (!wp_verify_nonce($_POST['post_to_twitter_nonce'],plugin_basename(__FILE__))) { return $post_id; }
						if (defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE) { return $post_id; }
						if ('post'==$_POST['post_type']&&!current_user_can('edit_post',$post_id)) { return $post_id; }
						if (!isset($_POST['post_to_twitter'])) { return $post_id; }
						else {
							$the_post = get_post($post_id);
							
							if ($the_post->post_type=='post') {							
								$settings = array(
									'consumer_key' => get_option('wpe_twitter_consumer_key',''),
									'consumer_secret' => get_option('wpe_twitter_consumer_secret',''),
									'oauth_access_token' => get_option('wpe_twitter_oauth_access_token',''),
									'oauth_access_token_secret' => get_option('wpe_twitter_oauth_access_token_secret','')
								);
								
								$url = 'https://api.twitter.com/1.1/statuses/update.json';
								$requestMethod = 'POST';
								
								$postfields = array(
									'status' => $the_post->post_title.' '.get_permalink($the_post->ID)
								);
								
								$twitter = new TwitterAPIExchange($settings);
								$twitter->buildOauth($url, $requestMethod)->setPostfields($postfields)->performRequest();
							}
						}
					}
				}
			}
			
	// Widget Set up
		if (get_option('wpe_twitter_username')) {
			class Twitter extends WP_Widget {
				function __construct() {
					parent::WP_Widget('twitter', 'Twitter', array( 'description' => 'Add a Twitter feed to your page.' ) );
				}
				
				function widget($args,$instance) {
					extract($args);
					$title = apply_filters('title',$instance['title']);
					$count = apply_filters('count',$instance['count']);
						if ($count) { $args = 'count="'.$count.'"'; }
					$order = apply_filters('order',$instance['order']);
						if ($order) { $args .= ' order="random"'; }
					$class = apply_filters('class',$instance['class']);
						if ($class) { $args .= ' class="'.$class.'"'; }
					echo $before_widget;
					if ($title) { echo '<h3 class="widget-title">'.$title.'</h3>'; }
					echo do_shortcode('[twitter '.$args.']');
					echo $after_widget;
				}
				
				function update($new_instance,$old_instance) {
					$instance = $old_instance;
					$instance['title'] = strip_tags($new_instance['title']);
					$instance['count'] = strip_tags($new_instance['count']);
					$instance['order'] = strip_tags($new_instance['order']);
					$instance['class'] = strip_tags($new_instance['class']);
					return $instance;
				}
				
				function form($instance) {
					if ($instance) {
						$title = esc_attr($instance['title']);
						$count = esc_attr($instance['count']);
						$order = esc_attr($instance['order']);
						$class = esc_attr($instance['class']);
					}
					?>
					<p>
						<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Sidebar Title'); ?></label> 
						<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>">
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Number of Tweets'); ?></label> 
						<input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo $count; ?>">
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Random'); ?></label> 
						<input id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>" type="checkbox" value="1" <?php if ($order) { echo 'checked="checked"'; } ?>>
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('class'); ?>"><?php _e('Class name (Optional)'); ?></label> 
						<input class="widefat" id="<?php echo $this->get_field_id('class'); ?>" name="<?php echo $this->get_field_name('class'); ?>" type="text" value="<?php echo $class; ?>">
					</p>
					<?php 
				}
			}
			register_widget('Twitter');
		}
		
	// Cache set up
		add_action('wp_ajax_wpe_twitter_cache', 'wpe_twitter_cache');
		add_action('wp_ajax_nopriv_wpe_twitter_cache', 'wpe_twitter_cache');
	
		function wpe_twitter_cache() {
			global $wpdb;
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
			die();
		}
		
		add_action('admin_footer', 'wpe_twitter_javascript');
		function wpe_twitter_javascript() { ?>
			<script type="text/javascript" >
				jQuery(document).ready(function() {
					jQuery("#wpe_cache_twitter").on("click",function(e){
						e.preventDefault();
						jQuery(this).hide().after('<img src="'+path_url+'/images/loading.gif" id="wpe_twitter_cache_loading">');
						var data = {
							action: 'wpe_twitter_cache'
						};
						
						jQuery.post(ajaxurl,data,function(response) {
							jQuery("#wpe_twitter_cache_loading").hide();
							jQuery("#wpe_twitter_cache_message").fadeIn();
							setTimeout(function(){
								jQuery("#wpe_twitter_cache_message").hide();
								jQuery("#wpe_cache_twitter").fadeIn();
							},5000);
						});
					}).after('<span id="wpe_twitter_cache_message">The twitter cache has been cleared.</span>');
					jQuery("#wpe_twitter_cache_message").hide();
				});
			</script>
		<?php }
		
	// Functions
		if (!function_exists('link_tweet')) {
			function link_tweet($tweet) {
				$tweet = preg_replace('/(^|\s)@(\w+)/','\1<a href="http://www.twitter.com/\2" target="_blank" rel="nofollow">@\2</a>',$tweet);
				$tweet = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t<]*)#ise", "'\\1<a href=\"\\2\" rel=\"nofollow\" target=\"_blank\">\\2</a>'", $tweet);
				$tweet = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)#ise", "'\\1<a href=\"http://\\2\" rel=\"nofollow\" target=\"_blank\">\\2</a>'", $tweet);
				$tweet = preg_replace('/(^|\s)#(\w+)/','\1<a href="https://twitter.com/search?q=%23\2&mode=realtime" rel="nofollow" target="_blank">#\2</a>',$tweet);
				return $tweet;
			}
		}
		
	// Twitter Class
		if (!function_exists('twitter')) {
			class TwitterAPIExchange 
			{
				private $oauth_access_token;
				private $oauth_access_token_secret;
				private $consumer_key;
				private $consumer_secret;
				private $postfields;
				private $getfield;
				protected $oauth;
				public $url;
			
				public function __construct(array $settings)
				{
					if (!in_array('curl', get_loaded_extensions())) 
					{
						throw new Exception('You need to install cURL, see: http://curl.haxx.se/docs/install.html');
					}
					
					if (!isset($settings['oauth_access_token'])
						|| !isset($settings['oauth_access_token_secret'])
						|| !isset($settings['consumer_key'])
						|| !isset($settings['consumer_secret']))
					{
						throw new Exception('Make sure you are passing in the correct parameters');
					}
			
					$this->oauth_access_token = $settings['oauth_access_token'];
					$this->oauth_access_token_secret = $settings['oauth_access_token_secret'];
					$this->consumer_key = $settings['consumer_key'];
					$this->consumer_secret = $settings['consumer_secret'];
				}
			
				public function setPostfields(array $array)
				{
					if (!is_null($this->getGetfield())) 
					{ 
						throw new Exception('You can only choose get OR post fields.'); 
					}
					
					if (isset($array['status']) && substr($array['status'], 0, 1) === '@')
					{
						$array['status'] = sprintf("\0%s", $array['status']);
					}
					
					$this->postfields = $array;
					
					return $this;
				}
			
				public function setGetfield($string)
				{
					if (!is_null($this->getPostfields())) 
					{ 
						throw new Exception('You can only choose get OR post fields.'); 
					}
					
					$search = array('#', ',', '+', ':');
					$replace = array('%23', '%2C', '%2B', '%3A');
					$string = str_replace($search, $replace, $string);  
					
					$this->getfield = $string;
					
					return $this;
				}
			
				public function getGetfield()
				{
					return $this->getfield;
				}
			
				public function getPostfields()
				{
					return $this->postfields;
				}
			
				public function buildOauth($url, $requestMethod)
			
				{
					if (!in_array(strtolower($requestMethod), array('post', 'get')))
					{
						throw new Exception('Request method must be either POST or GET');
					}
					
					$consumer_key = $this->consumer_key;
					$consumer_secret = $this->consumer_secret;
					$oauth_access_token = $this->oauth_access_token;
					$oauth_access_token_secret = $this->oauth_access_token_secret;
					
					$oauth = array( 
						'oauth_consumer_key' => $consumer_key,
						'oauth_nonce' => time(),
						'oauth_signature_method' => 'HMAC-SHA1',
						'oauth_token' => $oauth_access_token,
						'oauth_timestamp' => time(),
						'oauth_version' => '1.0'
					);
					
					$getfield = $this->getGetfield();
					
					if (!is_null($getfield))
					{
						$getfields = str_replace('?', '', explode('&', $getfield));
						foreach ($getfields as $g)
						{
							$split = explode('=', $g);
							$oauth[$split[0]] = $split[1];
						}
					}
					
					$base_info = $this->buildBaseString($url, $requestMethod, $oauth);
					$composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
					$oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
					$oauth['oauth_signature'] = $oauth_signature;
					
					$this->url = $url;
					$this->oauth = $oauth;
					
					return $this;
				}
			
				public function performRequest($return = true)
				{
					if (!is_bool($return)) 
					{ 
						throw new Exception('performRequest parameter must be true or false'); 
					}
					
					$header = array($this->buildAuthorizationHeader($this->oauth), 'Expect:');
					
					$getfield = $this->getGetfield();
					$postfields = $this->getPostfields();
			
					$options = array( 
						CURLOPT_HTTPHEADER => $header,
						CURLOPT_HEADER => false,
						CURLOPT_URL => $this->url,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_SSL_VERIFYPEER => false
					);
			
					if (!is_null($postfields))
					{
						$options[CURLOPT_POSTFIELDS] = $postfields;
					}
					else
					{
						if ($getfield !== '')
						{
							$options[CURLOPT_URL] .= $getfield;
						}
					}
			
					$feed = curl_init();
					curl_setopt_array($feed, $options);
					$json = curl_exec($feed);
					curl_close($feed);
			
					if ($return) { return $json; }
				}
			
				private function buildBaseString($baseURI, $method, $params) 
				{
					$return = array();
					ksort($params);
					
					foreach($params as $key=>$value)
					{
						$return[] = "$key=" . $value;
					}
					
					return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $return)); 
				}
				
				private function buildAuthorizationHeader($oauth) 
				{
					$return = 'Authorization: OAuth ';
					$values = array();
					
					foreach($oauth as $key => $value)
					{
						$values[] = "$key=\"" . rawurlencode($value) . "\"";
					}
					
					$return .= implode(', ', $values);
					return $return;
				}
			}
		}