<?php
	include_once "Mobile_Detect.php";
	function wpe_load_responsive() {
		global $wpe_responsive;
		$wpe_responsive = new Mobile_Detect();
	}
	add_action('init','wpe_load_responsive');
	
	if (!function_exists('wpe_responsive')) {
		function wpe_responsive($atts,$content) {
			global $wpe_responsive;
			extract(
				shortcode_atts(
					array(
						'not' => null
					),
					$atts
				 )
			);
			
			if (!$not) {
				if ($wpe_responsive->isMobile()) {
					return $content;
				}
			} else if ($not == "mobile") {
				if ($wpe_responsive->isTablet()) {
					return $content;
				}
			} else if ($not == "tablet") {
				if ($wpe_responsive->isMobile() && !$wpe_responsive->isTablet()) {
					return $content;
				}
			}
		}
	}
	add_shortcode('wpe_responsive','wpe_responsive');