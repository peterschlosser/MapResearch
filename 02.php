<!DOCTYPE html>
<html>
<head>
<title>Test 02</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">
<!--
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

#dvMap {
	height: 100%;
}
-->
</style>
</head>
<body>
<div id="dvMap"></div>

<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDmXOTrEX3FkSkDLLtIzw3Gqc5e2tDFmyI" type="text/javascript"></script>
<script type="text/javascript">// <![CDATA[
var markers = 
[{
	"title": "11-777",
	"lat": "38.461009",
	"lng": "-122.717175",
	"description": "Steele Ln @ Mendocino Ave, Santa Rosa, 94501"
},{
	"title": "11-778",
	"lat": "37.432306",
	"lng": "-121.899575",
	"description": "E Calaveras Blvd @ N Milpitas Blvd, Milpitas, 95035"
},{
	"title": "12-002",
	"lat": "37.537158",
	"lng": "-122.297343",
	"description":"S El Camino Real @ E Hillsdale Blvd, San Mateo, 94403"
}];
window.onload = function() 
{
	var mapOptions = 
	{
		center: new google.maps.LatLng(markers[0].lat, markers[0].lng),
		zoom: 10,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	var map = new google.maps.Map(document.getElementById("dvMap"), mapOptions);
	var infoWindow = new google.maps.InfoWindow();
	var lat_lng = new Array();
	var latlngbounds = new google.maps.LatLngBounds();
	for (i = 0; i < markers.length; i++) 
	{
		var data = markers[i]
		var myLatlng = new google.maps.LatLng(data.lat, data.lng);
		lat_lng.push(myLatlng);
		var marker = new google.maps.Marker({
			position: myLatlng,
			map: map,
			title: data.title
		});
		latlngbounds.extend(marker.position);
		(function(marker, data) 
		{
			google.maps.event.addListener(marker, "click", 
			function (e) 
			{
				infoWindow.setContent(data.description);
				infoWindow.open(map, marker);
			});
		})(marker, data);
	}
	map.setCenter(latlngbounds.getCenter());
	map.fitBounds(latlngbounds);
}

// ]]></script>
</body>
</html>
