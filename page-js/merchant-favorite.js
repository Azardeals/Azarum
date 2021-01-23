function showReviews(page, comapnyId) {
    callAjax(webroot + 'common-ajax.php', 'mode=ShowReviews&pagination=false&page=' + page + '&comapnyId=' + comapnyId, function (t) {
        var ans = parseJsonData(t);
        $('#reviews').html(ans.msg);

    });
}


$(document).ready(function () {
     var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&' + 'callback=initializeTo&key='+CONF_GOOGLE_MAP_KEY;
        document.head.appendChild(script); 
  getalldeals(1);
     
});
$(window).load(function(){
    $('.reviewsdescription').find('p').viewMore({limit: 300});
})
 function initializeTo() {


        var geocoder = new google.maps.Geocoder();
        window.map = new google.maps.Map(document.getElementById('mapCanvas'), {
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            scrollwheel: false,
            zoom: 15,
			center: new google.maps.LatLng(41.056466, -85.3312009),
        });
	
	   var infowindow = new google.maps.InfoWindow();

        var bounds = new google.maps.LatLngBounds();
        var markers = new Array();
        $.each(address, function(i, obj) {
         
           var add = obj.address;
            var marker = new google.maps.Marker({
                map: map,
            });
            var description = obj.html;
            
            geocodeAddress(geocoder, map, add, marker, bounds);
             setMarkerInfo(infowindow, marker, description);
            markers.push(marker);
        });
    
	
   
   function setMarkerInfo(infowindow, marker, data) {
        var html1='<div class="popup__location"><a href="javascript:void(0)" class="link__close"></a><h5>Store</h5>'+data+'</div>';
        
        marker.addListener('click', function () {

            infowindow.setContent(data);
            infowindow.open(map, marker);
        });
      
      infowindow.open(map,marker);
      infowindow.setContent(data);
    }

    function geocodeAddress(geocoder, resultsMap, address, marker, bounds) {

        geocoder.geocode({'address': address}, function (results, status) {
            if (status === google.maps.GeocoderStatus.OK) {
             
                resultsMap.setCenter(results[0].geometry.location);
                marker.setPosition(results[0].geometry.location);
                
                bounds.extend(marker.position);
                
                if (!bounds.isEmpty()) {
                    map.fitBounds(bounds);
                var listener = google.maps.event.addListener(map, "idle", function() { 
                  if (map.getZoom() > 7) map.setZoom(7); 
                  google.maps.event.removeListener(listener); 
                });
                }
            } else {
              console.log('Geocode was not successful for the '+address+' following reason: ' + status);
            }
        });

    }
           
 }
 