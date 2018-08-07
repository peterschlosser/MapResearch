<!DOCTYPE html>
<html>
<head>
<title>Test 19</title>
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
#mpanel {
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
<div id="mpanel">
	<button type="button" id="btnRotLL" class="btn btn-secondary" title="rotate left 10">&lt;&lt;</button>
	<button type="button" id="btnRotL" class="btn btn-secondary" title="rotate left 2">&lt;</button>
	<button type="button" id="btnRotR" class="btn btn-secondary" title="rotate right 2">&gt;</button>
	<button type="button" id="btnRotRR" class="btn btn-secondary" title="rotate right 10">&gt;&gt;</button>

	<button type="button" id="btnScaleDD" class="btn btn-secondary" title="scale down 10">&lt;&lt;</button>
	<button type="button" id="btnScaleD" class="btn btn-secondary" title="scale down 2">&lt;</button>
	<button type="button" id="btnScaleU" class="btn btn-secondary" title="scale up 2">&gt;</button>
	<button type="button" id="btnScaleUU" class="btn btn-secondary" title="scale up 10">&gt;&gt;</button>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script type="text/javascript">// <![CDATA[
var map;
var minfo;
var marker2;

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
		zoom: 20,
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
	marker.addListener('dblclick', showMarkerInfo);
	marker.addListener('dragend', showMarkerInfo);
	google.maps.event.addListener(map, 'zoom_changed', function() {
		marker.setMap(null);
		var newMarker = createMarker(marker.getPosition(), map, marker.uiangle, marker.uiscale);
		newMarker.addListener('dblclick', showMarkerInfo);
		newMarker.addListener('dragend', showMarkerInfo);
		delete marker;
		marker = null;
		marker = newMarker;
	});
	
//	map.setCenter(latlngbounds.getCenter());
//	map.fitBounds(latlngbounds);
	
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
*/
		draggable: true,
		editable: true,
		geodesic: false,
		uiangle: 0
	});
	shape.setMap(map);

	document.getElementById('btnRotLL').onclick = function() 
	{
		rotatePolygon(shape, -10);
		marker = rotateMarker(marker, -10);
	};
	document.getElementById('btnRotL').onclick = function() 
	{
		rotatePolygon(shape, -2);
		marker = rotateMarker(marker, -2);
	};
	document.getElementById('btnRotR').onclick = function() 
	{
		rotatePolygon(shape, 2);
		marker = rotateMarker(marker, 2);
	};
	document.getElementById('btnRotRR').onclick = function() 
	{
		rotatePolygon(shape, 10);
		marker = rotateMarker(marker, 10);
	};
	
	document.getElementById('btnScaleDD').onclick = function() 
	{
		marker = scaleMarker(marker, -0.10);
	};
	document.getElementById('btnScaleD').onclick = function() 
	{
		marker = scaleMarker(marker, -0.02);
	};
	document.getElementById('btnScaleU').onclick = function() 
	{
		marker = scaleMarker(marker, 0.02);
	};
	document.getElementById('btnScaleUU').onclick = function() 
	{
		marker = scaleMarker(marker, 0.10);
	};
}

function createMarker(position, map, angle=0, scale=1.0)
{
	var status = "RGRRRGRR";
	angle |= 0;
	var zoom = map.getZoom();
	var mscale = getScale(position, zoom + 1); //meters per pixel
	var width = 10 / mscale * scale; 
	
	var icon = {
//		url: "https://openclipart.org/download/82549/blue-circle.svg",
		url: 'data:image/svg+xml;charset=utf-8,' + getSVG(status, angle),
		anchor: new google.maps.Point(width/2, width/2),
		rotation: angle,	// info - no effect
		scaledSize: new google.maps.Size(width, width),
		status: status		// info - no effect
	};
	
	var result = new google.maps.Marker({
		position: position,
		map: map,
		icon: icon,
		draggable: true
	});
	result.uiangle = angle;
	result.uiscale = scale;
	return result;
}

function getScale(latLng, zoom)
{
	return 156543.03392 * Math.cos(latLng.lat() * Math.PI / 180) / Math.pow(2, zoom)
}

function showMarkerInfo(event) 
{
	var contentString = formatMarkerInfo(this);
	contentString += 'Click: { lat: ' + event.latLng.lat() + ', lng: ' + event.latLng.lng() + '}<br>';

	minfo.setContent(contentString);
	minfo.setPosition(event.latLng);
	
	minfo.open(map);
}

function updateMarkerInfo(marker) 
{
	minfo.setContent(formatMarkerInfo(marker));
}


function formatMarkerInfo(marker) 
{
	var result = '<b>Marker Icon</b><br>';
	result += 'Location: { lat: ' + marker.position.lat() + ', lng: ' + marker.position.lng() + '}<br>';
	result += 'Rotation: ' + marker.uiangle + '<br>';
	result += 'Scale: ' + marker.uiscale + '<br>';
	result += 'Zoom: ' + map.getZoom() + '<br>';
	result += 'scale: ' + getScale(marker.position, map.getZoom() + 1) + '<br>';
	result += 'status: ' + marker.getIcon().status + '<br>';
	result += '<br>';
	return result;
}

function rotatePolygon(polygon, angle) 
{
	var map = polygon.getMap();
	var prj = map.getProjection();
	
	// average verticies to find center
	var center = getPolygonCenter(polygon.getPath());
	var origin = prj.fromLatLngToPoint(center);
	var coords = polygon.getPath().getArray().map(function(latLng)
	{
		var point = prj.fromLatLngToPoint(latLng);
		var rotatedLatLng =  prj.fromPointToLatLng(rotatePoint(point, origin, angle));
		return {lat: rotatedLatLng.lat(), lng: rotatedLatLng.lng()};
	});
	polygon.setPath(coords);
	polygon.uiangle |= 0;
	polygon.uiangle += angle;
//	updatePolyInfo(polygon);
}

function rotateMarker(targetMarker, angle)
{
	targetMarker.setMap(null);
	targetMarker.uiangle |= 0;
	targetMarker.uiangle += angle;
	var result = createMarker(targetMarker.getPosition(), map, targetMarker.uiangle, targetMarker.uiscale);
	delete targetMaker;
	updateMarkerInfo(result);
	return result;
}

function scaleMarker(targetMarker, scale)
{
	targetMarker.setMap(null);
	targetMarker.uiscale = +(targetMarker.uiscale + scale).toFixed(3);
	var result = createMarker(targetMarker.getPosition(), map, targetMarker.uiangle, targetMarker.uiscale);
	delete targetMaker;
	updateMarkerInfo(result);
	return result;
}

function rotatePoint(point, origin, angle) 
{
	var angleRad = angle * Math.PI / 180.0;
	return {
		x: Math.cos(angleRad) * (point.x - origin.x) - Math.sin(angleRad) * (point.y - origin.y) + origin.x,
		y: Math.sin(angleRad) * (point.x - origin.x) + Math.cos(angleRad) * (point.y - origin.y) + origin.y
	};
}

function getPolygonCenter(verts)
{
//	var verts = polygon.getPath();
	var minmax = {
		latmin: 90,
		latmax: -90,
		lngmin: 180,
		lngmax: -180
	};
	for (var i =0; i < verts.getLength(); i++)
	{
		var xy = verts.getAt(i);
		minmax.latmin = Math.min(minmax.latmin, xy.lat());
		minmax.latmax = Math.max(minmax.latmax, xy.lat());
		minmax.lngmin = Math.min(minmax.lngmin, xy.lng());
		minmax.lngmax = Math.max(minmax.lngmax, xy.lng());
	}
	return new google.maps.LatLng(
		(minmax.latmin + minmax.latmax)/2,
		(minmax.lngmin + minmax.lngmax)/2
	);
}

var svgPath = [
	/* all off */
	"M14 20 z",
	/* rfu */
	"",
	/* Phase Odd (1,3,5,7)  Other */
	"M14 20 L17 20 L17 25 L14 25 z",
	/* Phase Odd (1,3,5,7)  Green */
	"M14 20 C14 18 14 14 10 14 L10 10 C16 10 17 14 17 20 L17 30 L14 30 Z",
	/* Phase Even (2,4,6,8)  Other */
	"M17 20 L20 20 L20 30 L17 30 z",
	/* Phase Even (2,4,6,8)  Green */
	"M17 20 L17 10 L20 10 L20 30 L17 30 z",
];
var PhaseKey = { "Off":0, "RFU":1, "O":2, "OG":3, "E": 4, "EG": 5 };
Object.freeze(PhaseKey);
var PhaseLink = { 'E': false, 'EG': false, 'O': false, 'OG': false };
function getSVGObject(key)
{
	var id = "";
	var path = "";
	switch (key)
	{
		default:
			id = "off" + key;
			path = svgPath[PhaseKey.Off];
			break;
		case PhaseKey.E:
			id = "phe";
			path = svgPath[PhaseKey.E];
			break;
		case PhaseKey.EG:
			id = "pheg";
			path = svgPath[PhaseKey.EG];
			break;
		case PhaseKey.O:
			id = "pho";
			path = svgPath[PhaseKey.O];
			break;
		case PhaseKey.OG:
			id = "phog";
			path = svgPath[PhaseKey.OG];
			break;
	}
	if (PhaseLink[key])
	{
		return "<use xlink:href='#" + id + "' />";
	}
	PhaseLink[key] = true;
	return "<path id='" + id + "' d='" + path + "' />";
}

function svgGetColor(key)
{
	key += "";
	key.toUpperCase();
	switch(key)
	{
		default:	return "none";
		case "G":	return "green";
		case "R":	return "red";
		case "Y":	return "yellow";
	}
}


function getSVG(key="", angle=0)
{
	angle |= 0;
	key = "         ".substr(0, 9 - Math.min(8, key.length)).concat(key.toUpperCase());

	PhaseLink[PhaseKey.O] = false;
	PhaseLink[PhaseKey.OG] = false;
	PhaseLink[PhaseKey.E] = false;
	PhaseLink[PhaseKey.EG] = false;

	var result = "<svg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'>";
	result += "  <g transform='rotate(" + angle + ",15,15)'>";

	result += "    <g fill='" + svgGetColor(key[1]) + "'>";
	result += "      " + (key[1] == "G" ? getSVGObject(PhaseKey.OG) : getSVGObject(PhaseKey.O));
	if (key[1] != key[2])
	{
		result += "    </g>";
		result += "    <g fill='" + svgGetColor(key[2]) + "'>";
	}
	result += "      " + (key[2] == "G" ? getSVGObject(PhaseKey.EG) : getSVGObject(PhaseKey.E));
	result += "    </g>";

	result += "    <g fill='" + svgGetColor(key[3]) + "' transform='rotate(90,15,15)'>";
	result += "      " + (key[3] == "G" ? getSVGObject(PhaseKey.OG) : getSVGObject(PhaseKey.O));
	if (key[3] != key[4])
	{
		result += "    </g>";
		result += "    <g fill='" + svgGetColor(key[4]) + "' transform='rotate(90,15,15)'>";
	}
	result += "      " + (key[4] == "G" ? getSVGObject(PhaseKey.EG) : getSVGObject(PhaseKey.E));
	result += "    </g>";

	result += "    <g fill='" + svgGetColor(key[5]) + "' transform='rotate(180,15,15)'>";
	result += "      " + (key[5] == "G" ? getSVGObject(PhaseKey.OG) : getSVGObject(PhaseKey.O));
	if (key[5] != key[6])
	{
		result += "    </g>";
		result += "    <g fill='" + svgGetColor(key[6]) + "' transform='rotate(180,15,15)'>";
	}
	result += "      " + (key[6] == "G" ? getSVGObject(PhaseKey.EG) : getSVGObject(PhaseKey.E));
	result += "    </g>";

	result += "    <g fill='" + svgGetColor(key[7]) + "' transform='rotate(-90,15,15)'>";
	result += "      " + (key[7] == "G" ? getSVGObject(PhaseKey.OG) : getSVGObject(PhaseKey.O));
	if (key[7] != key[8])
	{
		result += "    </g>";
		result += "    <g fill='" + svgGetColor(key[8]) + "' transform='rotate(-90,15,15)'>";
	}
	result += "      " + (key[8] == "G" ? getSVGObject(PhaseKey.EG) : getSVGObject(PhaseKey.E));
	result += "    </g>";
	result += "  </g>";
	result += "</svg>";

	return encodeURIComponent(result).replace(/%[\dA-F]{2}/g, function(match)
	{
		switch (match)
		{
			case '%20': return ' ';
			case '%3D': return '=';
			case '%3A': return ':';
			case '%2F': return '/';
			default: return match.toLowerCase();
		}
	});
}


// ]]></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDmXOTrEX3FkSkDLLtIzw3Gqc5e2tDFmyI&callback=initMap" type="text/javascript"></script>
</body>
</html>
