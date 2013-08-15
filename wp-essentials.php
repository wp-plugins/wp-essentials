<?php
	/*
		Plugin Name: WP Essentials
		Plugin URI: http://wordpress.iprogress.co.uk
		Description: All-in-one bundle of essential plugins and functions for all WordPress websites.
		Version: 1.0
		Author: iprogress
		Author URI: http://wordpress.iprogress.co.uk/plugins/wp-essentials/
	*/
	
	// Essentails Setup
		define('ESSENTIALS_VERSION', '1.0');
		define('ESSENTIALS_DIR', dirname(__FILE__));
		define('ESSENTIALS_PATH', plugins_url().'/wp-essentials');
		
	// Add Options		
		function add_options() {
			add_option('wpe_cleanup',1);
			add_option('wpe_client_role',1);
			add_option('wpe_custom_image_sizes',1);
			add_option('wpe_error_reports',1);
			add_option('wpe_error_reports_google_analytics',1);
			add_option('wpe_error_reports_search_engines',1);
			add_option('wpe_footer_link',0);
			add_option('wpe_image_quality',75);
			add_option('wpe_javascript',1);
			add_option('wpe_google_analytics','');
			add_option('wpe_php_date',1);
			add_option('wpe_debug_mode',0);
			add_option('wpe_facebook',1);
			add_option('wpe_flickr_username','');
			add_option('wpe_flickr_api','');
			add_option('wpe_google_maps',1);
			add_option('wpe_twitter_username','');
			add_option('wpe_twitter_consumer_key','');
			add_option('wpe_twitter_consumer_secret','');
			add_option('wpe_twitter_oauth_access_token','');
			add_option('wpe_twitter_oauth_access_token_secret','');
			add_option('wpe_style','css');
			add_option('wpe_email',1);
			add_option('wpe_video',1);
			add_option('wpe_excerpt',1);
			add_option('wpe_get_image_source',1);
			add_option('wpe_link_it',1);
			add_option('wpe_relative_time',1);
		}
		
		register_activation_hook(__FILE__,'add_options');
		
	// Load Plugins
		function essentials_load(){			
			// System
				if (get_option('wpe_cleanup')==1) { include("system/cleanup.php"); }
				if (get_option('wpe_client_role')==1) { include("system/client-role.php"); }
				include("system/custom-image-sizes.php");
				if (get_option('wpe_debug_mode')==1) { include("system/debug-mode.php"); }
				if (get_option('wpe_error_reports')==1) { include("system/error-reports.php"); }
				if (get_option('wpe_footer_link')==1) { include("system/footer-link.php"); }
				include("system/image-quality.php");
				if (get_option('wpe_javascript')==1) { include("system/javascript.php"); }
			
			// Plugins
				if (get_option('wpe_email')==1) { include("plugins/email.php"); }
				include("plugins/analytics.php");
				if (get_option('wpe_facebook')==1) { include("plugins/facebook.php"); }
				include("plugins/flickr.php");
				if (get_option('wpe_google_maps')==1) { include("plugins/google-maps.php"); }
				include("plugins/twitter.php");
				
			// Shortcodes
				if (get_option('wpe_php_date')==1) { include("shortcodes/php-date.php"); }
				if (get_option('wpe_video')==1) { include("shortcodes/video.php"); }
				
			// Custom Functions
				if (get_option('wpe_excerpt')==1) { include("functions/excerpt.php"); }
				if (get_option('wpe_get_image_source')==1) { include("functions/get-image-source.php"); }
				if (get_option('wpe_link_it')==1) { include("functions/link-it.php"); }
				if (get_option('wpe_relative_time')==1) { include("functions/relative-time.php"); }
		}
		add_action('widgets_init', 'essentials_load');
		
	// Load Scripts & Styles
		function wpe_scripts() {
			$screen = get_current_screen();
			if ($screen->base == 'toplevel_page_wp-essentials') {
				wp_enqueue_script('wpe_slider',ESSENTIALS_PATH.'/scripts/wpe-slider.js',false);
				wp_enqueue_script('wpe_settings',ESSENTIALS_PATH.'/scripts/wpe-settings.js',false);
				wp_enqueue_style('wpe_styles',ESSENTIALS_PATH.'/styles/wpe-style.css',false);
			}
		}
		add_action('admin_enqueue_scripts','wpe_scripts');
		
	// Load jQuery
		if (!is_admin()) wp_enqueue_script("jquery");
	
	// Add Essentails Menu
		function wpe_menu() {
			add_menu_page('WP Essentials', 'WP Essentials', 'manage_options', 'wp-essentials', 'wpe_function',plugins_url('wp-essentials/images/icon.png'));
		}
		
		function wpe_function() {
			if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			include("settings.php");
		}
		add_action('admin_menu', 'wpe_menu');