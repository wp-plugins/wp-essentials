<?php
	if (!function_exists('wpe_footer_link')) {
		function wpe_footer_link() {
			echo '<p style="font-size:10px;text-align:center;margin:13px 0px;">Powered by <a href="http://www.wp-essentials.net" target="_blank">WP Essentials</a></p>';	
		}
		add_action('wp_footer', 'wpe_footer_link');
	}