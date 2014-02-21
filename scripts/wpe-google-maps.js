jQuery(document).ready(function() {
	if (jQuery(".google_map").length>0) {
		jQuery(".google_map").each(function(){
			var thisMap = jQuery(this);
			var thisAddress = thisMap.text();
			var thisId = thisMap.attr("id");
			var thisZoom = thisMap.attr("data-zoom")*1;
			var thisControls = thisMap.attr("data-controls");
				if (thisControls=="true") { thisControls=false; } else { thisControls=true; }
			var thisMarker = thisMap.attr("data-marker");
				if (thisMarker=="true") { thisMarker=true; } else { thisMarker=false; }
			var thisIcon = thisMap.attr("data-icon");
			
			geocoder=new google.maps.Geocoder();
			geocoder.geocode({address:thisAddress},
				function(results,status){
					if(status==google.maps.GeocoderStatus.OK){
						var center=results[0].geometry.location;
						var myOptions={zoom:thisZoom,center:center,mapTypeId:google.maps.MapTypeId.ROADMAP,disableDefaultUI:thisControls,mapTypeControl:false};
						var map=new google.maps.Map(document.getElementById(thisId),myOptions);
						if (thisMarker) { var marker=new google.maps.Marker({position:center,map:map,icon:thisIcon}); }
					}
				}
			);
		});	
	}
});