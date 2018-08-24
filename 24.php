<?php 
	require_once($_SERVER['DOCUMENT_ROOT'] . '/settings.php');
	require_once('24-lib.php');
	
	$GLOBALS['DOCUMENT_ROOT_ICON'] = "/img/gmap";

?>
<!DOCTYPE html>
<html>
<head>
<title>Test 24</title>
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
	display: none;

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
    <br>
	<button type="button" id="btnScaleDD" class="btn btn-secondary" title="scale down 10">&lt;&lt;</button>
	<button type="button" id="btnScaleD" class="btn btn-secondary" title="scale down 2">&lt;</button>
	<button type="button" id="btnScaleU" class="btn btn-secondary" title="scale up 2">&gt;</button>
	<button type="button" id="btnScaleUU" class="btn btn-secondary" title="scale up 10">&gt;&gt;</button>
	<br>
	<button type="button" id="btnPhaseChange" class="btn btn-secondary">Next</button>
</div>

<?php

// latlngDeviceBounds controls the (startup) geolocation
// rectangle from which devices are displayed.
$GLOBALS['latlngDeviceBounds'] = array(
	'N' => '37.417000',
	'E' => '-121.896076',
	'S' => '37.411079',
	'W' => '-121.906000'
);
$marker_data = get_devices(
	$GLOBALS['latlngDeviceBounds']['N'], 
	$GLOBALS['latlngDeviceBounds']['E'],
	$GLOBALS['latlngDeviceBounds']['S'],
	$GLOBALS['latlngDeviceBounds']['W']
);
$marker_func = function($m) 
{
	$m['markerMarker'] = NULL;
	$m['uiMarker'] = NULL;
	$m['uiData'] = $m['uiData'] == NULL ? 'RKKKKKKK' : $m['uiData'];
	$m['uiUpdate'] = false;
	return $m;
};
$marker_data = array_map($marker_func, $marker_data);
$marker_json = json_encode($marker_data, JSON_PRETTY_PRINT);
$marker_json = substr($marker_json, 1, -1);	// trim \[ \]

?>
<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="/js/date.format.js"></script>
<script type="text/javascript">// <![CDATA[
var map;
var mapReady = false;
var minfo;
var marker;
var focusMarker;
var arrPOI = [<?= $marker_json ?>];
var mapBounds;
var OnBoundsChangedCooldown = null;
var strStatusSince = "";
var logDebug = false;

<?php /*

POINTS OF INTEREST - each POI represents a node on the reporting network.  Each note has 
up to two google.maps.Marker, #1 (uiMarker) the data image, #2 (markerMarker) the optional pushpin icon.

*/ ?>
function initMap() 
{
	var mapCenter = arrPOI.length > 0 
		? new google.maps.LatLng(arrPOI[0].markerLat, arrPOI[0].markerLng)
		: new google.maps.LatLng("38.461009" /* lat: santa rosa */, "-122.717175"/* lng: santa rosa */);
	var mapOptions = 
	{
		center: mapCenter,
		zoom: 18,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	map = new google.maps.Map(document.getElementById("map"), mapOptions);
	minfo = new google.maps.InfoWindow;
	mapBounds = new google.maps.LatLngBounds();

	// create markers
	for (i = 0; i < arrPOI.length; i++) 
	{
		// delete existing marker pin
		if (arrPOI[i].markerMarker != null)
		{
			if (arrPOI[i].markerMarker.hasOwnProperty('setMap'))
				arrPOI[i].markerMarker.setMap(null);
			delete arrPOI[i].markerMarker;
			arrPOI[i].markerMarker = null;
		}

		setup_poi(arrPOI[i]);
		mapBounds.extend(arrPOI[i].markerMarker.position);
	}
	map.setCenter(mapBounds.getCenter());
	map.fitBounds(mapBounds);
	
	google.maps.event.addListener(map, 'zoom_changed', function()
	{
		for (i = 0; i < arrPOI.length; i++)
		{
			arrPOI[i].uiMarker = recreateMarker(arrPOI[i].uiMarker);
		}
	});

	OnBoundsChangedCooldown = null;
	google.maps.event.addListener(map, 'bounds_changed', function()
	{
		if (OnBoundsChangedCooldown)
		{
			clearInterval(OnBoundsChangedCooldown);
			OnBoundsChangedCooldown = null;
		}
		OnBoundsChangedCooldown = setTimeout(DoBoundsChanged, 1000 /* ms: 1 sec */);
	});

	document.getElementById('btnRotLL').onclick = function() 
	{
		arrPOI[0].markerMarker = rotateMarker(arrPOI[0].markerMarker, -10);
	};
	document.getElementById('btnRotL').onclick = function() 
	{
		arrPOI[0].markerMarker = rotateMarker(arrPOI[0].markerMarker, -2);
	};
	document.getElementById('btnRotR').onclick = function() 
	{
		arrPOI[0].markerMarker = rotateMarker(arrPOI[0].markerMarker, 2);
	};
	document.getElementById('btnRotRR').onclick = function() 
	{
		arrPOI[0].markerMarker = rotateMarker(arrPOI[0].markerMarker, 10);
	};
	
	document.getElementById('btnScaleDD').onclick = function() 
	{
		arrPOI[0].markerMarker = scaleMarker(arrPOI[0].markerMarker, -0.10);
	};
	document.getElementById('btnScaleD').onclick = function() 
	{
		arrPOI[0].markerMarker = scaleMarker(arrPOI[0].markerMarker, -0.02);
	};
	document.getElementById('btnScaleU').onclick = function() 
	{
		arrPOI[0].markerMarker = scaleMarker(arrPOI[0].markerMarker, 0.02);
	};
	document.getElementById('btnScaleUU').onclick = function() 
	{
		arrPOI[0].markerMarker = scaleMarker(arrPOI[0].markerMarker, 0.10);
	};

	mapReady = true;

//	setTimeout(DoAnimateIcons, 1000 /* ms: 1 sec */);
	waitForStatus();
}

function DoAnimateIcons()
{
	var hasUpdate = false;
	for (var i = 0; i < arrPOI.length; i++)
	{
		if (! arrPOI[i].hasOwnProperty('animStep'))
		{
			arrPOI[i].animStep = 0;
			arrPOI[i].uiData = anim_next_step(0).replace(/-/g,"R");
			hasUpdate = true;
		}
		else
		{
			if (arrPOI[i].animStep >= 32)
				continue;
			arrPOI[i].animStep++;
			arrPOI[i].uiData = anim_next_step(arrPOI[i].animStep).replace(/-/g,"R");
			hasUpdate = true;
		}
		if (arrPOI[i].markerMarker == null)
		{
			setup_poi(arrPOI[i]);
		}
		arrPOI[i].uiMarker.uiphase = arrPOI[i].uiData;
		arrPOI[i].uiMarker = recreateMarker(arrPOI[i].uiMarker);
		if (arrPOI[i].animStep == 0)
			break;
	}
	if (hasUpdate)
	{
		setTimeout(DoAnimateIcons, 125 /* ms: 1/8 sec */);
		return;
	}
	if (logDebug) console.log("DoAnimateIcons complete.");
	waitForStatus();
}



function DoBoundsChanged()
{
	var bounds = gmapGetBoundsAjax();
	if (bounds == false)
	{
		// wait and try again
		if (logDebug) console.log("map not ready.");
		setTimeout(DoBoundsChanged, 1000 /* ms: 1 sec */);
		return;
	}
	
	$.ajax(
	{
		type: "GET",
		url: "24-ajax.php",
		data: {
			action: 'markers',
			bounds: bounds
		},
		async: true,
		cache: false,
		timeout: 100000, /* ms: 10 sec */
		success: function(data)
		{
			// fill in missing map markers using data
			// delete map markers missing from data
			if (data !== null && typeof data === 'object')
			{
				update_markers(data);
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown)
		{
			console.log("map ajax error: ", textStatus + " (" + errorThrown + ")");
		}
	});
}

function setup_poi(poi)
{
	// delete existing marker pin
	if (poi.markerMarker != null)
	{
		if (poi.markerMarker.hasOwnProperty('setMap'))
			poi.markerMarker.setMap(null);
		delete poi.markerMarker;
		poi.markerMarker = null;
	}

	// create marker pin with infowindow listener
	var markerOptions = {
		position: new google.maps.LatLng(poi.markerLat, poi.markerLng),
		map: map,
		title: poi.deviceID,
		zIndex: 2
	};
	if (String(poi.markerIcon).length > 0)
	{
		markerOptions.icon = '<?= $GLOBALS['DOCUMENT_ROOT_ICON']; ?>/' + poi.markerIcon;
	}
	poi.markerMarker = new google.maps.Marker(markerOptions);
	mapBounds.extend(poi.markerMarker.position);
	(function(target, object) 
	{
		google.maps.event.addListener(target, "click", function(e) 
		{
			minfo.setContent(markerGetInfoContent(object));
			minfo.open(map, target);
		});
	})(poi.markerMarker, poi);
	
	// create marker data image
	poi.uiMarker = createMarker(new google.maps.LatLng(poi.uiLat, poi.uiLng), map, poi.uiData, poi.uiHeading, poi.uiScale);
}


// creates UI image icon as Google Maps Marker (uiMarker)
function createMarker(position, map, status = "RRGGGGGG", angle=0, scale=1.0)
{
	angle |= 0;
	var zoom = map.getZoom();
	var mscale = getScale(position, zoom + 1); //meters per pixel
	var width = 10 / mscale * scale; 
	
	var icon = {
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
		draggable: false,
		zIndex: 1
	});
	result.uiangle = angle;
	result.uiphase = status;
	result.uiscale = scale;
	return result;
}

function recreateMarker(target)
{
	if (typeof target == 'undefined')
		return target;
	target.setMap(null);
	var newMarker = createMarker(target.getPosition(), map, target.uiphase, target.uiangle, target.uiscale);
	delete target;
	newMarker.addListener('dblclick', showMarkerInfo);
	newMarker.addListener('dragend', showMarkerInfo);
	return newMarker;
}

function gmapGetBoundsAjax()
{
	if (mapReady == false || typeof map === 'undefined')
	{
		return false;
	}
	var mb = map.getBounds();
	if (typeof mb === 'undefined')
	{
		return false;
	}
	var ne = mb.getNorthEast();
	var sw = mb.getSouthWest();
	var bounds = "" + 
		ne.lat().toFixed(6) + "," + 
		ne.lng().toFixed(6) + "," + 
		sw.lat().toFixed(6) + "," +
		sw.lng().toFixed(6);
	return bounds;
}

function waitForStatus()
{
	var bounds = gmapGetBoundsAjax();
	if (bounds === false)
	{
		// wait and try again
		
		console.log("map not ready.");
		setTimeout(waitForStatus, 1000 /* ms: 1 sec */);
		return;
	}
	
	if (logDebug) console.log("map ajax status");
	$.ajax(
	{
		type: "GET",
		url: "24-ajax.php",
		data: {
			action: 'status',
			bounds: bounds,
			since: strStatusSince
		},
		async: true,
		cache: false,
		timeout: 50000, /* ms: 5 sec */
		success: function(data)
		{
			// fill in missing map markers using data
			// delete map markers missing from data
			if (data !== null && typeof data === 'object')
			{
				if (logDebug) console.log("map ajax status: result");
				update_status(data);
			}
			else
			{
				if (logDebug) console.log("map ajax status: no result");
			}
			setTimeout(waitForStatus, 1000 /* ms: 1 sec */);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown)
		{
			console.log("map ajax error: ", textStatus + " (" + errorThrown + ")");
			setTimeout(waitForStatus, 15000 /* ms: 15 sec */);
		}
	});
};

$(document).ready(function()
{
	//waitForStatus();
});

function update_status(data)
{
	if (data === null || typeof data !== 'object')
	{
		if (logDebug) console.log("update_status: bad or missing data");
		return false;
	}
	if (!data.hasOwnProperty('data') || !data.hasOwnProperty('sent'))
	{
		if (logDebug) console.log("update_status: bad or missing data property");
		return false;
	}
	if (data.hasOwnProperty('since'))
	{
		strStatusSince = data.since;
	}
	var newStatus = data.data;
	if (newStatus.length == 0)
	{
		if (logDebug) console.log("update_status: no data");
		return false;
	}
	for (var markerID in newStatus) 
	{
		var marker = GetPOIByMarkerID(markerID);
		if (marker)
		{
			marker.uiUpdate = (marker.uiData != newStatus[markerID]);
			marker.uiData = newStatus[markerID];
if (logDebug) console.log("ID(" + markerID + ") => " 
	+ marker.uiData + " " 
	+ marker.uiUpdate);			
		}
	}
	update_ui();
}

function update_markers(data)
{
	if (data === null || typeof data !== 'object')
	{
		return false;
	}
	if (!data.hasOwnProperty('data'))
	{
		return false;
	}
	var newMarkers = data.data;
	if (!newMarkers || !newMarkers.hasOwnProperty('length') || newMarkers.length == 0)
	{
		return false;
	}

	var added = 0;
	var found = 0;

	for (var i = 0; i < arrPOI.length; i++)
	{
		arrPOI[i].inBounds = false;
	}

	for (var i = 0; i < newMarkers.length; i++)
	{
		var nm = newMarkers[i];
		var marker = GetPOIByMarkerID(nm.markerID);
		if (marker)
		{
			marker.inBounds = true;
			found++;
		}
		else
		{
			nm.inBounds = true;
			arrPOI.push(format_marker(nm));
			added++;
			if (logDebug) console.log('new marker: ' + nm.markerLat + ", " + nm.markerLng);
		}
	}
	
	if (added)
	{
		update_ui();
	}
}


function format_marker(newMarker) 
{
	newMarker.markerMarker = null;
	newMarker.uiMarker = null;
	newMarker.uiData = newMarker.uiData == null ? 'RRKKKKKK' : newMarker.uiData;
	newMarker.uiUpdate = true;
	return newMarker;
}

function update_ui()
{
	for (var i = 0; i < arrPOI.length; i++)
	{
		if (arrPOI[i].markerMarker == null)
		{
			setup_poi(arrPOI[i]);
		}
		if (arrPOI[i].uiUpdate)
		{
			arrPOI[i].uiUpdate = false;
			arrPOI[i].uiMarker.uiphase = arrPOI[i].uiData;
			arrPOI[i].uiMarker = recreateMarker(arrPOI[i].uiMarker);
		}
	}
}


function GetPOIByMarkerID(id)
{
	for (var i = 0; i < arrPOI.length; i++)
	{
		if (arrPOI[i].markerID == id)
			return arrPOI[i];
	}
	return null;
}
function GetPOIByMarkerObj(marker)
{
	for (var i = 0; i < arrPOI.length; i++)
	{
		if (arrPOI[i].markerMarker.getPosition().equals(marker.getPosition()))
			return arrPOI[i];
	}
	return null;
}
function GetPOIByMarkerPos(position)
{
	for (var i = 0; i < arrPOI.length; i++)
	{
		if (arrPOI[i].markerMarker.getPosition().equals(position))
			return arrPOI[i];
	}
	return null;
}

function markerGetInfoContent(m)
{
	return `<div id="mapinfo${ m.markerID }" class="mapinfo">
  <div id="deviceID">${ m.deviceID }</div>
  <h2 id="InterName">${ m.name }</h2>
  <h3 id="InterAddr">${ m.locality }</h3>
</div>`;
}

function getScale(latLng, zoom)
{
	return 156543.03392 * Math.cos(latLng.lat() * Math.PI / 180) / Math.pow(2, zoom)
}

function showMarkerInfo(event) 
{
	var contentString = formatMarkerInfo(this);
	contentString += 'Click: { lat: ' + event.latLng.lat().toFixed(6) + ', lng: ' + event.latLng.lng().toFixed(6) + '}<br>';

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
	var poi = GetPOIByMarkerObj(marker);
	poi = (poi == null ? { "deviceID": "", "markerID": "" } : poi);
	var result = '<b>Device: ' + poi.deviceID + '(' + poi.markerID + ')</b><br>';
	result += 'Location: { lat: ' + marker.position.lat().toFixed(6) + ', lng: ' + marker.position.lng().toFixed(6) + '}<br>';
	result += 'Rotation: ' + marker.uiangle + '<br>';
	result += 'Scale: ' + marker.uiscale + '<br>';
	result += 'Phase: ' + marker.uiphase + '<br>';
	result += 'Zoom: ' + map.getZoom() + '<br>';
	result += 'scale: ' + getScale(marker.position, map.getZoom() + 1) + '<br>';
	result += 'status: ' + marker.getIcon().status + '<br>';
	result += '<br>';
	return result;
}

function rotateMarker(targetMarker, angle)
{
	targetMarker.uiangle = targetMarker.uiangle || 0;
	targetMarker.uiangle += angle;
	var result = recreateMarker(targetMarker);
	updateMarkerInfo(result);
	return result;
}

function scaleMarker(targetMarker, scale)
{
	targetMarker.uiscale = targetMarker.uiscale || 1.0;
	targetMarker.uiscale = +(targetMarker.uiscale + scale).toFixed(3);
	var result = recreateMarker(targetMarker);
	updateMarkerInfo(result);
	return result;
}

function svgGetColor(key)
{
	key += "";
	key.toUpperCase();
	switch(key)
	{
		default:	return "none";
		case "G":	return "green";
		case "K":	return "black";
		case "R":	return "red";
		case "Y":	return "yellow";
	}
}

//	input key defines colors of eight (8) phase objects by position and code, where
//	G=green, Y=yellow, R=red, *=hidden
function getSVG(key="RKRKRKRK", angle=0)
{
	if (typeof key == 'undefined' || key == null)
		key = "KKKKKKKK";
	key = "         ".substr(0, 9 - Math.min(8, key.length)).concat(key.toUpperCase());
	angle = angle || 0;

/* saved version 1 defs
   <defs>
      <path id='pho' d='M14 20 L17 20 L17 25 L14 25 z' />
      <path id='phog' d='M14 20 C14 18 14 14 10 14 L10 10 C16 10 17 14 17 20 L17 30 L14 30 Z' />
      <path id='phe' d='M17 20 L20 20 L20 30 L17 30 z' />
      <path id='pheg' d='M17 20 L17 10 L20 10 L20 30 L17 30 z' />
   </defs>
*/


	// icon defined by SVG as 30x30 region centered on 32x32 view area.
	// odd/even phases share same display object, rotated into position.
	// green objects may be differing size and shape over red-yellow.
	var content = `
<svg viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'>
   <defs>
      <path id='pho' stroke="black" stroke-width="0.125" d='M14 20 L17 20 L17 25 L14 25 z' />
      <path id='phog' stroke="black" stroke-width="0.125" d='M14 20 L17 20 L17 25 L14 25 z' />
      <path id='phe' stroke="black" stroke-width="0.125" d='M17 20 L20 20 L20 30 L17 30 z' />
      <path id='pheg' stroke="black" stroke-width="0.125" d='M17 20 L20 20 L20 30 L17 30 z' />
   </defs>
   <g transform='translate(1,1) rotate(${ angle },15,15)' fill-opacity="0.5">
      <g fill='${ svgGetColor(key[1]) }'>
          <use xlink:href='#pho${ key[1] == "G" ? 'g' : '' }' />
      </g>
      <g fill='${ svgGetColor(key[6]) }'>
          <use xlink:href='#phe${ key[6] == "G" ? 'g' : '' }' />
      </g>
      <g transform='rotate(-90,15,15)'>
        <g fill='${ svgGetColor(key[7]) }'>
          <use xlink:href='#pho${ key[7] == "G" ? 'g' : '' }' />
        </g>
        <g fill='${ svgGetColor(key[4]) }'>
          <use xlink:href='#phe${ key[4] == "G" ? 'g' : '' }' />
        </g>
      </g>
      <g transform='rotate(180,15,15)'>
        <g fill='${ svgGetColor(key[5]) }'>
          <use xlink:href='#pho${ key[5] == "G" ? 'g' : '' }' />
        </g>
        <g fill='${ svgGetColor(key[2]) }'>
          <use xlink:href='#phe${ key[2] == "G" ? 'g' : '' }' />
        </g>
      </g>
      <g transform='rotate(90,15,15)'>
        <g fill='${ svgGetColor(key[3]) }'>
          <use xlink:href='#pho${ key[3] == "G" ? 'g' : '' }' />
        </g>
        <g fill='${ svgGetColor(key[8]) }'>
          <use xlink:href='#phe${ key[8] == "G" ? 'g' : '' }' />
        </g>
      </g>
   </g>
</svg>`;

	return encodeURIComponent(content).replace(/%[\dA-F]{2}/g, function(match)
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

function anim_step_color(cstep)
{
	var c = parseInt(cstep) || 0;
	switch (c)
	{
		default:	return "X";
		case 0:		return "R";
		case 1:		return "Y";
		case 2:		return "G";
		case 3:		return "K";
	}
}
function anim_step_phase(step)
{
	var s = parseInt(step) || 0;
	var i = Math.floor(s / 4);	// 4=# of states per phase
	var phase = new Array(1,6,7,4,5,2,3,8);
	if (i < phase.length)
		return phase[i];
	return 0;
}

function anim_set_char_at(str,index,chr)
{
	return (index > str.length-1)
		? str
		: str.substr(0,index) + chr + str.substr(index+1);
}

function anim_next_step(step)
{
	var s = parseInt(step) || 0;
	var color = anim_step_color(s % 4);	// 4=# of states per phase
	var phase = anim_step_phase(s) - 1;
	var result = "--------";
	return anim_set_char_at(result,phase,color); 
}


// ]]></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDmXOTrEX3FkSkDLLtIzw3Gqc5e2tDFmyI&callback=initMap" type="text/javascript"></script>
</body>
</html>
