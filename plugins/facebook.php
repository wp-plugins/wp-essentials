<?php
	// Shortcode Setup
		// Facebook
			if (!function_exists('facebook')) {
				function facebook($atts) {
					extract(
						shortcode_atts(
							array(
								'fanbox_id' => null,
								'connections' => '12',
								'width' => '300',
								'height' => '300',
							),
							$atts
						 )
					);
					
					if (!function_exists('add_fb_script')) {
						function add_fb_script() {
							echo '<script>(function(d, s, id) {var js, fjs = d.getElementsByTagName(s)[0];if (d.getElementById(id)) {return;}js = d.createElement(s); js.id = id;js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1&appId=103952876372022";fjs.parentNode.insertBefore(js, fjs);}(document,\'script\',\'facebook-jssdk\'));</script>';
						}
						add_action('wp_footer', 'add_fb_script');
					}
					return '<div class="fanbox"><div class="fb-like-box" profile_id="'.$fanbox_id.'" data-connections="'.$connections.'" data-width="'.$width.'" data-height="'.$height.'" data-show-faces="true" data-stream="false" data-header="false"></div></div>';
				}
				add_shortcode('facebook','facebook');
			}
			
	// Widget Set up
		if (function_exists('facebook')) {
			class Facebook_Fanbox extends WP_Widget {
				function __construct() {
					parent::WP_Widget('facebook_fanbox', 'Facebook Fanbox', array( 'description' => 'Add a Facebook Fanbox to your page.'));
				}
				
				function widget($args,$instance) {
					extract($args);
					$fanbox_id = apply_filters('fanbox_id',$instance['fanbox_id']);
						if ($fanbox_id) { $args = 'fanbox_id="'.$fanbox_id.'"'; }
					$connections = apply_filters('connections',$instance['connections']);
						if ($connections) { $args .= ' connections="'.$connections.'"'; }
					$width = apply_filters('width',$instance['width']);
						if ($width) { $args .= ' width="'.$width.'"'; }
					$height = apply_filters('height',$instance['height']);
						if ($height) { $args .= ' height="'.$height.'"'; }
					echo $before_widget;
					echo do_shortcode('[facebook '.$args.']');
					echo $after_widget;
				}
				
				function update($new_instance,$old_instance) {
					$instance = $old_instance;
					$instance['fanbox_id'] = strip_tags($new_instance['fanbox_id']);
					$instance['connections'] = strip_tags($new_instance['connections']);
					$instance['width'] = strip_tags($new_instance['width']);
					$instance['height'] = strip_tags($new_instance['height']);
					return $instance;
				}
				
				function form($instance) {
					if ($instance) {
						$fanbox_id = esc_attr($instance['fanbox_id']);
						$connections = esc_attr($instance['connections']);
						$width = esc_attr($instance['width']);
						$height = esc_attr($instance['height']);
					}
					?>
					<p>
						<label for="<?php echo $this->get_field_id('fanbox_id'); ?>"><?php _e('Facebook Page ID'); ?></label> 
						<input class="widefat" id="<?php echo $this->get_field_id('fanbox_id'); ?>" name="<?php echo $this->get_field_name('fanbox_id'); ?>" type="text" value="<?php echo $fanbox_id; ?>">
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('connections'); ?>"><?php _e('Number of Connections'); ?></label>
						<input class="widefat" id="<?php echo $this->get_field_id('connections'); ?>" name="<?php echo $this->get_field_name('connections'); ?>" type="text" value="<?php echo $connections; ?>">
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width'); ?></label>
						<input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>">
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Height'); ?></label>
						<input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo $height; ?>">
					</p>
					<?php
				}
			}
			register_widget('Facebook_Fanbox');
		}