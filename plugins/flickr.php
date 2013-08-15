<?php
	// Database Set up
		if (get_option('wpe_flickr_username')) {
			global $wpdb;
			
			$table_name = $wpdb->prefix."wpe_flickr";
			
			$sql = "CREATE TABLE ".$table_name." (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				user_id VARCHAR(255) DEFAULT '' NOT NULL,
				photo_id VARCHAR(255) DEFAULT '' NOT NULL,
				photo_title VARCHAR(255) DEFAULT '' NOT NULL,
				photo_src VARCHAR(255) DEFAULT '' NOT NULL,
				added VARCHAR(255) DEFAULT '' NOT NULL,
				UNIQUE KEY id (id)
			);";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
		
	// Shortcode Setup
		// Flickr
			if (!function_exists('flickr')) {
				function flickr($atts) {
					global $wpdb;
					
					$table_name = $wpdb->prefix."wpe_flickr";
					
					extract(
						shortcode_atts(
							array(
								'count' => 3,
								'order' => null,
								'class' => 'flickr'
							),
							$atts
						 )
					);
					
					// Cache Check
					$check = $wpdb->get_row('SELECT * FROM '.$table_name.' LIMIT 1');
					$now = strtotime('-15 minutes');
					$last_update = strtotime($check->added);
					if ($now > $last_update) {
						require_once("phpFlickr/phpFlickr.php");
					
						$f = new phpFlickr(get_option('wpe_flickr_api'));
	
						$i = 0;
						$person = $f->people_findByUsername(get_option('wpe_flickr_username',''));
						$photos_url = $f->urls_getUserPhotos($person['id']);
						$photos = $f->people_getPublicPhotos($person['id'], NULL, NULL, 20);
							
						$wpdb->query('TRUNCATE TABLE '.$table_name);
					 
						foreach ((array)$photos['photos']['photo'] as $photo) {
							$wpdb->query('INSERT INTO '.$table_name.' VALUES ("","'.get_option('wpe_flickr_username','').'","'.$photos_url.''.$photo['id'].'","'.$photo[title].'","'.$f->buildPhotoURL($photo, "Square").'",NOW())');
						}
					}
					
					// Get Feed
					if ($order=="random") {
						$flickr = $wpdb->get_results('SELECT * FROM '.$table_name.' ORDER BY RAND() LIMIT '.$count);
					} else {
						$flickr = $wpdb->get_results('SELECT * FROM '.$table_name.' LIMIT '.$count);
					}
					
					$photos = '<ul class="'.$class.'">';
					foreach($flickr as $photo) {
						$photos .= '<li><a href="'.$photo->photo_id.'" target="_blank" rel="nofollow"><img border="0" alt="'.$photo->photo_title.'" title="'.$photo->photo_title.'" src="'.$photo->photo_src.'"></a></li>';
					}
					$photos .= '</ul>';
					return $photos;
				}
				add_shortcode('flickr','flickr');
			}
			
	// Widget Set up
		if (get_option('wpe_flickr_username')) {
			class Flickr extends WP_Widget {
				function __construct() {
					parent::WP_Widget('flickr', 'Flickr', array( 'description' => 'Add a Flickr feed to your page.' ) );
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
					echo do_shortcode('[flickr '.$args.']');
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
						<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Number of Images'); ?></label> 
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
			register_widget('Flickr');
		}
		
	// Cache set up
		add_action('wp_ajax_wpe_flickr_cache', 'wpe_flickr_cache');
		add_action('wp_ajax_nopriv_wpe_flickr_cache', 'wpe_flickr_cache');
	
		function wpe_flickr_cache() {
			global $wpdb;
			$table_name = $wpdb->prefix."wpe_flickr";
			require_once(ESSENTIALS_DIR."/plugins/phpFlickr/phpFlickr.php");
		
			$f = new phpFlickr(get_option('wpe_flickr_api'));
		
			$i = 0;
			$person = $f->people_findByUsername(get_option('wpe_flickr_username',''));
			$photos_url = $f->urls_getUserPhotos($person['id']);
			$photos = $f->people_getPublicPhotos($person['id'], NULL, NULL, 20);
				
			$wpdb->query('TRUNCATE TABLE '.$table_name);
		 
			foreach ((array)$photos['photos']['photo'] as $photo) {
				$wpdb->query('INSERT INTO '.$table_name.' VALUES ("","'.get_option('wpe_flickr_username','').'","'.$photos_url.''.$photo['id'].'","'.$photo[title].'","'.$f->buildPhotoURL($photo, "Square").'",NOW())');
			}
			die();
		}
		
		add_action('admin_footer', 'wpe_flickr_javascript');
		function wpe_flickr_javascript() { ?>
			<script type="text/javascript" >
				jQuery(document).ready(function() {
					jQuery("#wpe_cache_flickr").on("click",function(e){
						e.preventDefault();
						jQuery(this).hide().after('<img src="'+path_url+'/images/loading.gif" id="wpe_flickr_cache_loading">');
						var data = {
							action: 'wpe_flickr_cache'
						};
						
						jQuery.post(ajaxurl,data,function(response) {
							jQuery("#wpe_flickr_cache_loading").hide();
							jQuery("#wpe_flickr_cache_message").fadeIn();
							setTimeout(function(){
								jQuery("#wpe_flickr_cache_message").hide();
								jQuery("#wpe_cache_flickr").fadeIn();
							},5000);
						});
					}).after('<span id="wpe_flickr_cache_message">The flickr cache has been cleared.</span>');
					jQuery("#wpe_flickr_cache_message").hide();
				});
			</script>
		<?php }