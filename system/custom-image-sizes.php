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