<!DOCTYPE html>
<html>
<head>
<title>Test 01</title>
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

<script type="text/javascript">
function initMap()
{
}
</script>
<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDmXOTrEX3FkSkDLLtIzw3Gqc5e2tDFmyI&callback=initMap" type="text/javascript"></script>
<script type="text/javascript">// <![CDATA[
var markers = 
[{
	"lat": "17.454000",
	"lng": "78.434952"
},{
	"title": "shilparamam",
	"lat": "17.452665",
	"lng": "78.435608",
	"description": "Mumbai formerly Bombay, is the capital city of the Indian state of Maharashtra."
},{
	"title": "image hospitals",
	"lat":"17.452421",
	"lng":"78.435715",
	"description":"Pune is the seventh largest metropolis in India, the second largest in the state of Maharashtra after Mumbai."
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
