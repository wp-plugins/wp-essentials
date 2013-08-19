<?php	
	if (get_option('wpe_google_analytics')) {
		function google_analytics() {
			$analytics = get_option('wpe_google_analytics');
			$codes = explode(",",$analytics);
			$i = 0;
			
			foreach($codes as $code) {
				$commands .= "					_gaq.push(['".$i."._setAccount', '".$code."']);
					_gaq.push(['".$i."._trackPageview']);
";
				$i++;
			}
			
			echo "
				<script type=\"text/javascript\">
					var _gaq = _gaq || [];
".$commands."					(function() {
						var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
						ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
						var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
					})();
				</script>
			";
		}
		add_action('wp_head', 'google_analytics');
	}