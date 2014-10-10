<?php
	// Database Set up
		if (get_option('wpe_instagram_username')) {
			global $wpdb;
			
			$table_name = $wpdb->prefix."wpe_instagram";
			
			$sql = "CREATE TABLE ".$table_name." (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				image VARCHAR(255) DEFAULT '' NOT NULL,
				thumbnail VARCHAR(255) DEFAULT '' NOT NULL,
				title VARCHAR(255) DEFAULT '' NOT NULL,
				added VARCHAR(255) DEFAULT '' NOT NULL,
				UNIQUE KEY id (id)
			);";
			
			require_once(ABSPATH.'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
		
	// Shortcode Setup
		// Instagram
			if (!function_exists('instagram')) {
				function instagram($atts) {
					global $wpdb;
					
					$table_name = $wpdb->prefix."wpe_instagram";
					
					extract(
						shortcode_atts(
							array(
								'count' => 3,
								'order' => null,
								'class' => 'instagram'
							),
							$atts
						 )
					);
					
					// Cache Check
					$check = $wpdb->get_row('SELECT * FROM '.$table_name.' LIMIT 1');
					$now = strtotime('-15 minutes');
					$last_update = strtotime($check->added);
					if ($now > $last_update) {
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
								$wpdb->query('INSERT INTO '.$table_name.' VALUES ("","'.$post->link.'","'.str_replace("http:","https:",$post->images->thumbnail->url).'","'.$post->caption->text.'",NOW())');
							}
						}
					}
					
					// Get Photos
					if ($order=="random") {
						$photo_query = $wpdb->get_results('SELECT * FROM '.$table_name.' ORDER BY RAND() LIMIT '.$count);
					} else {
						$photo_query = $wpdb->get_results('SELECT * FROM '.$table_name.' LIMIT '.$count);
					}
					
					$photos = '<ul class="'.$class.'">';
					foreach($photo_query as $photo) {
						$photos .= '<li><a href="'.$photo->image.'" target="_blank" rel="nofollow" title="'.$photo->title.'"><img src="'.$photo->thumbnail.'" alt="'.$photo->title.'"></a></li>';
					}
					$photos .= '</ul>';
					return $photos;
				}
				add_shortcode('instagram','instagram');
			}
			
	// Widget Set up
		if (get_option('wpe_instagram_username')) {
			class Instagram extends WP_Widget {
				function __construct() {
					parent::WP_Widget('instagram', 'Instagram', array( 'description' => 'Add an Instagram feed to your page.' ) );
				}
				
				function widget($args,$instance) {
					extract($args);
					$count = apply_filters('count',$instance['count']);
						if ($count) { $args = 'count="'.$count.'"'; }
					$order = apply_filters('order',$instance['order']);
						if ($order) { $args .= ' order="random"'; }
					$class = apply_filters('class',$instance['class']);
						if ($class) { $args .= ' class="'.$class.'"'; }
					echo $before_widget;
					echo do_shortcode('[instagram '.$args.']');
				}
				
				function update($new_instance,$old_instance) {
					$instance = $old_instance;
					$instance['count'] = strip_tags($new_instance['count']);
					$instance['order'] = strip_tags($new_instance['order']);
					$instance['class'] = strip_tags($new_instance['class']);
					return $instance;
				}
				
				function form($instance) {
					if ($instance) {
						$count = esc_attr($instance['count']);
						$order = esc_attr($instance['order']);
						$class = esc_attr($instance['class']);
					}
					?>
					<p>
						<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Number of Photos'); ?></label> 
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
			register_widget('Instagram');
		}
		
	// Instagram Callback
		if ($_GET['code']) {
			$url = 'https://api.instagram.com/oauth/access_token';
			$fields = array(
				'client_id' => get_option('wpe_instagram_client_id'),
				'client_secret' => get_option('wpe_instagram_client_secret'),
				'grant_type' => 'authorization_code',
				'redirect_uri' => get_bloginfo('wpurl').'/wp-admin/index.php',
				'code' => $_GET['code']
			);
			
			foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
			rtrim($fields_string, '&');
			
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$objResult = json_decode($result,true);
			update_option('wpe_instagram_user_id',$objResult['user']['id']);
			update_option('wpe_instagram_access_token',$objResult['access_token']);
			curl_close($ch);
			wp_redirect('/wp-admin/admin.php?page=wp-essentials');
		}
		
	// Cache set up
		add_action('wp_ajax_wpe_instagram_cache', 'wpe_instagram_cache');
		add_action('wp_ajax_nopriv_wpe_instagram_cache', 'wpe_instagram_cache');
	
		function wpe_instagram_cache() {
			global $wpdb;
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
					$wpdb->query('INSERT INTO '.$table_name.' VALUES ("","'.$post->link.'","'.str_replace("http:","https:",$post->images->thumbnail->url).'","'.$post->caption->text.'",NOW())');
				}
			}
			die();
		}
		
		add_action('admin_footer', 'wpe_instagram_javascript');
		function wpe_instagram_javascript() { ?>
			<script type="text/javascript" >
				jQuery(document).ready(function() {
					jQuery("#wpe_cache_instagram").on("click",function(e){
						e.preventDefault();
						jQuery(this).hide().after('<img src="'+path_url+'/images/loading.gif" id="wpe_instagram_cache_loading">');
						var data = {
							action: 'wpe_instagram_cache'
						};
						
						jQuery.post(ajaxurl,data,function(response) {
							jQuery("#wpe_instagram_cache_loading").hide();
							jQuery("#wpe_instagram_cache_message").fadeIn();
							setTimeout(function(){
								jQuery("#wpe_instagram_cache_message").hide();
								jQuery("#wpe_cache_instagram").fadeIn();
							},5000);
						});
					}).after('<span id="wpe_instagram_cache_message">The Instagram cache has been cleared.</span>');
					jQuery("#wpe_instagram_cache_message").hide();
				});
			</script>
		<?php }