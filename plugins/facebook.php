<?php
	// Shortcode Setup
		// Facebook
			if (!function_exists('facebook')) {
				function facebook($atts) {
					extract(
						shortcode_atts(
							array(
								'page' => '',
								'connections' => '12',
								'width' => '300',
								'height' => '300',
								'stream' => 'false',
								'header' => 'false',
								'border' => 'true',
							),
							$atts
						 )
					);
					
					if (!function_exists('add_fb_script')) {
						function add_fb_script() {
							echo '<div id="fb-root"></div><script>(function(d, s, id) {var js,fjs=d.getElementsByTagName(s)[0];if(d.getElementById(id))return;js=d.createElement(s);js.id=id;js.src="//connect.facebook.net/en_GB/all.js#xfbml=1&appId=171559713032073";fjs.parentNode.insertBefore(js,fjs);}(document,\'script\',\'facebook-jssdk\'));</script>';
						}
						add_action('wp_footer', 'add_fb_script');
					}
					return '<div class="fanbox"><div class="fb-like-box" data-href="'.$page.'" data-connections="'.$connections.'" data-width="'.$width.'" data-height="'.$height.'" data-show-faces="true" data-stream="'.$stream.'" data-header="'.$header.'" data-show-border="'.$border.'"></div></div>
					';
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
					$title = apply_filters('title',$instance['title']);
					$page = apply_filters('page',$instance['page']);
						if ($page) { $args = 'page="'.$page.'"'; }
					$connections = apply_filters('connections',$instance['connections']);
						if ($connections) { $args .= ' connections="'.$connections.'"'; }
					$width = apply_filters('width',$instance['width']);
						if ($width) { $args .= ' width="'.$width.'"'; }
					$height = apply_filters('height',$instance['height']);
						if ($height) { $args .= ' height="'.$height.'"'; }
					$header = apply_filters('header',$instance['header']);
						if ($header==1) { $args .= ' header="true"'; } else { $args .= ' header="false"'; }
					$stream = apply_filters('stream',$instance['stream']);
						if ($stream==1) { $args .= ' stream="true"'; } else { $args .= ' stream="false"'; }
					$border = apply_filters('height',$instance['border']);
						if ($border==1) { $args .= ' border="true"'; } else { $args .= ' border="false"'; }
						
					echo $before_widget;
					if ($title) { echo '<h3 class="widget-title">'.$title.'</h3>'; }
					echo do_shortcode('[facebook '.$args.']');
					echo $after_widget;
				}
				
				function update($new_instance,$old_instance) {
					$instance = $old_instance;
					$instance['title'] = strip_tags($new_instance['title']);
					$instance['page'] = strip_tags($new_instance['page']);
					$instance['connections'] = strip_tags($new_instance['connections']);
					$instance['width'] = strip_tags($new_instance['width']);
					$instance['height'] = strip_tags($new_instance['height']);
					$instance['header'] = strip_tags($new_instance['header']);
					$instance['stream'] = strip_tags($new_instance['stream']);
					$instance['border'] = strip_tags($new_instance['border']);
					return $instance;
				}
				
				function form($instance) {
					if ($instance) {
						$title = esc_attr($instance['title']);
						$page = esc_attr($instance['page']);
						$connections = esc_attr($instance['connections']);
						$width = esc_attr($instance['width']);
						$height = esc_attr($instance['height']);
						$header = esc_attr($instance['header']);
						$stream = esc_attr($instance['stream']);
						$border = esc_attr($instance['border']);
					}
					?>
					<p>
						<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Sidebar Title'); ?></label> 
						<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>">
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('page'); ?>"><?php _e('Facebook Page URL'); ?></label> 
						<input class="widefat" id="<?php echo $this->get_field_id('page'); ?>" name="<?php echo $this->get_field_name('page'); ?>" type="text" value="<?php echo $page; ?>">
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
					<p>
						<label for="<?php echo $this->get_field_id('header'); ?>"><?php _e('Show Header'); ?></label>
						<input class="" id="<?php echo $this->get_field_id('header'); ?>" name="<?php echo $this->get_field_name('header'); ?>" type="checkbox" value="1" <?php if ($header) { echo 'checked="checked"'; } ?>>
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('stream'); ?>"><?php _e('Show Stream'); ?></label>
						<input class="" id="<?php echo $this->get_field_id('stream'); ?>" name="<?php echo $this->get_field_name('stream'); ?>" type="checkbox" value="1" <?php if ($stream) { echo 'checked="checked"'; } ?>>
					</p>
					<p>
						<label for="<?php echo $this->get_field_id('border'); ?>"><?php _e('Show Border'); ?></label>
						<input class="" id="<?php echo $this->get_field_id('border'); ?>" name="<?php echo $this->get_field_name('border'); ?>" type="checkbox" value="1" <?php if ($border) { echo 'checked="checked"'; } ?>>
					</p>
					<?php
				}
			}
			register_widget('Facebook_Fanbox');
		}