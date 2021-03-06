<!DOCTYPE html>
<html>
<head>
<title>Test 07</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<style type="text/css">
<!--
.mapinfo {
	width: 95%;	/* kill horz scrollbar */
}
.mapinfo h1,
.mapinfo h2,
.mapinfo h3,
.mapinfo h4 {
	margin-top: 5px;
	margin-bottom: 5px;
}
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
<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDmXOTrEX3FkSkDLLtIzw3Gqc5e2tDFmyI" type="text/javascript"></script>
<script type="text/javascript">// <![CDATA[
var markers = 
[{
	"title": "11-777",
	"lat": "38.461009",
	"lng": "-122.717175",
	"icon": "/img/gmap/red_MarkerP.png",
	"description": `<div class="mapinfo">
  <span id="deviceID">11-777</span>
  <h2 id="InterName">Steele Ln @ Mendocino Ave</h2>
  <h3 id="InterAddr">Santa Rosa, 94501</h3>
  <div class="content row">
    <div class="col-sm-2"><strong>Last Activity</strong></div>
    <div class="col-sm-9">What meaningful thing can we say here? Phase 1 Green or what?</div>
  </div>
</div>`
},{
	"title": "11-778",
	"lat": "37.432306",
	"lng": "-121.899575",
	"icon": "/img/gmap/yellow_MarkerT.png",
	"description": `<div class="mapinfo">
  <div id="deviceID">11-778</div>
  <h2 id="InterName">E Calaveras Blvd @ N Milpitas Blvd</h2>
  <h3 id="InterAddr">Milpitas, 95035</h3>
  <div class="content row">
    <div class="col-sm-2"><strong>Last Activity</strong></div>
    <div class="col-sm-9">What meaningful thing can we say here? Phase 1 Green or what?</div>
  </div>
</div>`
},{
	"title": "12-002",
	"lat": "37.537158",
	"lng": "-122.297343",
	"icon": "/img/gmap/green_MarkerJ.png",
	"description": `<div class="mapinfo">
  <div id="deviceID">12-002</div>
  <h2 id="InterName">S El Camino Real @ E Hillsdale Blvd</h2>
  <h3 id="InterAddr">San Mateo, 94403</h3>
  <div class="content row">
    <div class="col-sm-2"><strong>Last Activity</strong></div>
    <div class="col-sm-9">What meaningful thing can we say here? Phase 1 Green or what?</div>
  </div>
</div>`
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
		var marker = new google.maps.Marker(
		{
			position: myLatlng,
			map: map,
			title: data.title,
			icon: data.icon
		});
		latlngbounds.extend(marker.position);
		(function(marker, data) 
		{
			google.maps.event.addListener(marker, "click", function(e) 
			{
				infoWindow.setContent(data.description);
				infoWindow.open(map, marker);
			});
		})(marker, data);
	}
	
	var goldStar = {
		path: 'M 125,5 155,90 245,90 175,145 200,230 125,180 50,230 75,145 5,90 95,90 z',
		fillColor: 'yellow',
		fillOpacity: 0.8,
		scale: 1,
		strokeColor: 'gold',
		strokeWeight: 14
	};
	var marker = new google.maps.Marker({
		position: map.getCenter(),
		icon: goldStar,
		map: map
	});	
	
	map.setCenter(latlngbounds.getCenter());
	map.fitBounds(latlngbounds);
}

// ]]></script>
</body>
</html>
