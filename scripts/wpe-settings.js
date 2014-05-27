jQuery(document).ready(function() {
	var baseURL="http://"+window.location.hostname;
	var currentURL=window.location;
	
	if (jQuery("#wpe_left").length>0) {
		jQuery("#wpe_left").find('.postbox h3').each(function(){
			jQuery(jQuery(this).parent().get(0)).addClass('closed');
		}).click(function() {
			jQuery(jQuery(this).parent().get(0)).toggleClass('closed');
		});
		
		jQuery(".pro_version").each(function(){
			var thisPostbox = jQuery(this);
			thisPostbox.find("input").attr("disabled","disabled");
			thisPostbox.find("h3").append(' <sup><span class="wpe-lock"></span> <em>Premium License Required</em></sup>');
		});
		
		if (window.location.hash) {
			jQuery(window.location.hash+" h3").each(function() {
				jQuery(jQuery(this).parent().get(0)).toggleClass('closed');
				jQuery("html, body").animate({ scrollTop: (jQuery(window.location.hash).offset().top-60) }, 1000);
			});
		}
		
		// Image Quality
			jQuery("#wpe-slider").slider({
				value:jQuery("#wpe-slider-label").text(),
				min:0,
				max:100,
				step:1,
				slide:function(event,ui) {
					jQuery("#wpe-slider-label").text(ui.value);
					jQuery("#image_quality").val(ui.value);
					jQuery(".image-1").css("opacity",(ui.value/100));
				}
			});
			jQuery("#image_quality").hide();
			
		// Styling
			jQuery("#none").on("click",function(){
				jQuery(".style_css,.style_sass,.style_less").hide();
			});
			jQuery("#css").on("click",function(){
				jQuery(".style_sass,.style_less").hide();
				jQuery(".style_css").fadeIn();
			});
			jQuery("#sass").on("click",function(){
				jQuery(".style_css,.style_less").hide();
				jQuery(".style_sass").fadeIn();
			});
			jQuery("#less").on("click",function(){
				jQuery(".style_sass,.style_css").hide();
				jQuery(".style_less").fadeIn();
			});
	}
});