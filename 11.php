<!DOCTYPE html>
<html>
<head>
<title>Test 11</title>
<meta charset="utf-8">
<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
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
#map {
	height: 100%;
}
#floating-panel {
	position: absolute;
	top: 10px;
	left: 25%;
	z-index: 5;
	background-color: #fff;
	padding: 5px;
	border: 1px solid #999;
	text-align: center;
	font-family: 'Roboto','sans-serif';
	line-height: 30px;
	padding-left: 10px;
}
-->
</style>
</head>
<body>
<div id="map"></div>
<div id="floating-panel"><input type="button" id="btnRotate" value="Auto Rotate"></div>

<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script type="text/javascript">// <![CDATA[
var map;
var minfo;

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

function initMap() 
{
	var mapOptions = 
	{
		center: new google.maps.LatLng(markers[0].lat, markers[0].lng),
		zoom: 9,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	map = new google.maps.Map(document.getElementById("map"), mapOptions);
	minfo = new google.maps.InfoWindow;

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
				minfo.setContent(data.description);
				minfo.open(map, marker);
			});
		})(marker, data);
	}
	
	marker = createMarker(new google.maps.LatLng(markers[0].lat, markers[0].lng), map);
	google.maps.event.addListener(map, 'zoom_changed', function() {
		marker.setMap(null);
		marker = createMarker(marker.getPosition(),map);
	});
	
	map.setCenter(latlngbounds.getCenter());
	map.fitBounds(latlngbounds);
	
	var rectCoords = {
		north: 38.461140,	// + 0.000131
		south: 38.460875,	// - 0.000134
		east: -122.717325,	// + 0.000150
		west: -122.717011	// - 0.000164
	};

	var shapeCoords = [
		{lat: rectCoords.north, lng: rectCoords.west},
		{lat: rectCoords.north, lng: rectCoords.east},
		{lat: rectCoords.south, lng: rectCoords.east},
		{lat: rectCoords.south, lng: rectCoords.west}
	];
	
	// Define a rectangle and set its editable property to true.
	var shape = new google.maps.Polygon(
	{
		paths: shapeCoords,
/*		strokeColor: '#FF0000',
		strokeOpacity: 0.8,
		strokeWeight: 3,
		fillColor: '#FF0000',
		fillOpacity: 0.35
*/		rotation: 45,
		editable: true
	});
	shape.setMap(map);
	shape.addListener('click', showPolyInfo);
	
}

function createMarker(position, map)
{
	var zoom = map.getZoom();
	var scale = getScale(position, zoom + 1); //meters per pixel
	var width = 10 / scale; 
	var height = width;
	
	var icon = {
		url: "https://openclipart.org/download/82549/blue-circle.svg",
		anchor: new google.maps.Point(width/2, height/2),
		scaledSize: new google.maps.Size(width, height)
	};
	
	return new google.maps.Marker({
		position: position,
		map: map,
		icon: icon
	});
}

function getScale(latLng, zoom)
{
	return 156543.03392 * Math.cos(latLng.lat() * Math.PI / 180) / Math.pow(2, zoom)
}

function showPolyInfo(event) 
{
	var vertices = this.getPath();
	var contentString = '<b>Polygon</b><br>' +
		'Click location: <br>' + event.latLng.lat() + ',' + event.latLng.lng() + '<br>' +
		'Zoom: ' + map.getZoom() + '<br>';

	for (var i =0; i < vertices.getLength(); i++) 
	{
		var xy = vertices.getAt(i);
		contentString += '<br>' + 'Coordinate ' + i + ':' + xy.lat() + ',' + xy.lng();
	}
	
	// Replace the info window's content and position.
	minfo.setContent(contentString);
	minfo.setPosition(event.latLng);
	
	minfo.open(map);
}

function rotatePolygon(polygon, angle) 
{
	var map = polygon.getMap();
	var prj = map.getProjection();
	var origin = prj.fromLatLngToPoint(polygon.getPath().getAt(0)); // rotate around first point
	
	var coords = polygon.getPath().getArray().map(function(latLng)
	{
		var point = prj.fromLatLngToPoint(latLng);
		var rotatedLatLng =  prj.fromPointToLatLng(rotatePoint(point, origin, angle));
		return {lat: rotatedLatLng.lat(), lng: rotatedLatLng.lng()};
	});
	polygon.setPath(coords);
}

function rotatePoint(point, origin, angle) 
{
	var angleRad = angle * Math.PI / 180.0;
	return {
		x: Math.cos(angleRad) * (point.x - origin.x) - Math.sin(angleRad) * (point.y - origin.y) + origin.x,
		y: Math.sin(angleRad) * (point.x - origin.x) + Math.cos(angleRad) * (point.y - origin.y) + origin.y
	};
}
// ]]></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDmXOTrEX3FkSkDLLtIzw3Gqc5e2tDFmyI&callback=initMap" type="text/javascript"></script>
</body>
</html>
