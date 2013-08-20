jQuery(document).ready(function() {
	var baseURL="http://"+window.location.hostname;
	var currentURL=window.location;
	
	if (jQuery("#wpe_left").length>0) {
		jQuery("#wpe_left").find('.postbox h3').each(function(){
			jQuery(jQuery(this).parent().get(0)).addClass('closed');
		}).click(function() {
			jQuery(jQuery(this).parent().get(0)).toggleClass('closed');
		});
		
		var rightPos = jQuery("#wpe_right").position();
		jQuery("#wpe_right").css("width",jQuery("#wpe_right").width());
		jQuery(window).scroll(function(){
			if (jQuery(document).scrollTop()>rightPos.top) {
				jQuery("#wpe_right").addClass("sticky");
			} else {
				jQuery("#wpe_right").removeClass("sticky");
			}
		}).on("resize",function(){
			jQuery("#wpe_right").css("width","17%").css("width",jQuery("#wpe_right").width());
		});
		jQuery(".pro_version").each(function(){
			var thisPostbox = jQuery(this);
			thisPostbox.find("input").attr("disabled","disabled");
			thisPostbox.find("h3").append(' <sup>Premium License Required</sup>');
		});
		
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