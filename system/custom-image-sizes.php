<?php
	if (!function_exists('add_image_sizes')) {
		function add_image_sizes($sizes) {
			global $_wp_additional_image_sizes;
			if (empty($_wp_additional_image_sizes))
				return $sizes;
			
			foreach ($_wp_additional_image_sizes as $id => $data) {
				if (!isset($sizes[$id]))
				$sizes[$id] = ucwords(str_replace('-', ' ',str_replace('_',' ',$id)));
			}
			
			return $sizes;
		}
		add_filter('image_size_names_choose','add_image_sizes');
	}
	
	if (get_option('wpe_custom_image_sizes')>0) {
		for($wpe_s=1;$wpe_s<=get_option('wpe_custom_image_sizes');$wpe_s++) {
			$wpe_size = get_option('wpe_image_size_'.$wpe_s);
			$wpe_sizes = explode(';',$wpe_size);
			if ($wpe_sizes[3]==1) { $wpe_crop = true; } else { $wpe_crop = false; }
			
			add_image_size($wpe_sizes[0],$wpe_sizes[1],$wpe_sizes[2],$wpe_crop);
		}
	}