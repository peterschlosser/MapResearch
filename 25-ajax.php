<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . '/settings.php');
	require_once('25-lib.php');
	
	// given LatLongBounds
	// SELECT device info (markers)
	// SELECT data since YYYYMMDDHHMMSS (long poll)

	// action = markers
	//	bounds = latN,longE,latS,longW
	//	returns list of markerInfo with deviceID
	//
	// action = status (long poll five seconds)
	//	since = YYYYMMDDhhmmss
	//	bounds = latN,longE,latS,longW
	//	returns list of deviceID => "GRRRGRRR"

	$GLOBALS['ACTION'] = array_key_exists('action', $_REQUEST) ? $_REQUEST['action'] : false;
	$GLOBALS['SINCE'] = array_key_exists('since', $_REQUEST) ? $_REQUEST['since'] : false;
	$GLOBALS['BOUNDS'] = array_key_exists('bounds', $_REQUEST) ? $_REQUEST['bounds'] : false;

	$reply = array(
		'status' => 'ok',
		'message' => 'operation complete.'
	);
	switch(strtolower($ACTION))
	{
		default:
			$reply = array(
				'status' => 'error',
				'message' => 'bad command.'
			);
			break;
		case 'status':
			$data = array();
			$since = false;
			$bounds = parse_bounds($GLOBALS['BOUNDS']);
			$since = parse_since($GLOBALS['SINCE']);	// Y-m-d H:i:s.u
			if (false !== $bounds)
			{
				 list($data, $since) = get_status($bounds[0], $bounds[1], $bounds[2], $bounds[3], $since);
			}
			$reply['since'] = $since;
			$reply['data'] = $data;
			break;
		case 'markers':
			$data = array();
			$bounds = parse_bounds($GLOBALS['BOUNDS']);
			if (false !== $bounds)
			{
				 $data = get_devices($bounds[0], $bounds[1], $bounds[2], $bounds[3]);
			}
			$reply['data'] = $data;
			break;
	}

	header('Content-type: application/json');
	$reply['sent'] = date('Y-m-d H:i:s');
	$json = json_encode($reply);
	if ($json === false) 
	{
	    $json = json_encode(array(
			'status' => "json_error",
			'message' => json_last_error_msg(),
			'sent' => date('YYYY-MM-DD HH:ii:ss')
		));
	}
	exit($json);

	function parse_bounds($bounds)
	{
		$list = explode(',', $bounds);
		if (count($list) != 4)
			return false;

		$parsed = array(90,180,90,180);
		for ($i=0; $i < count($list); $i++)
		{
			$parsed[$i] = floatval($list[$i]);
		}
		return $parsed;
	}
	
	function parse_since($since)
	{
		$since = trim($since);
		if (empty($since))
			return false;
		$date = date_create_from_format("Y-m-d H:i:s.u", $since);
		if (false === $date)
			return false;
		$parsed = date_format($date, 'Y-m-d H:i:s.u');
		return $parsed;
	}
	
?>