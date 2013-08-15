<?php
	if (!function_exists('error_report')) {
		function error_report() {
			$errors = '';
			
			if (!get_option('wpe_google_analytics')&&get_option('wpe_error_reports_google_analytics')) {
				$errors .= '<li>Your website does not have Google Analytics tracking installed. <a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=wp-essentials">Fix &raquo;</a></li>';
			}
			
			if (!get_option('blog_public')&&get_option('wpe_error_reports_search_engines')) {
				$errors .= '<li>Search engines are blocked from this website. <a href="'.get_bloginfo('wpurl').'/wp-admin/options-reading.php">Fix &raquo;</a></li>';
			}
			
			if (get_option('wpe_instagram_username') && get_option('wpe_instagram_client_id') && !get_option('wpe_instagram_user_id') && !get_option('wpe_instagram_access_token')) {
				$errors .= '<li>Your Instagram set up is almost complete. <a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=wp-essentials">Fix &raquo;</a></li>';
			}
			
			if ($errors) {
				echo '
					<div class="error">
						<p>The following issues have been found:</p>
						<ul style="margin:0px 20px;list-style:disc;">
							'.$errors.'
						</ul>
					</div>
				';
			}
		}
		add_filter('admin_notices', 'error_report');
	}