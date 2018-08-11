<?php

	function sql_log_error($text, $file, $line)
	{
		$error = sprintf("SQL ERROR(%0d) %s<br>\nquery (%s)<br>\nin: <b>%s</b> at line: <b>%s</b><br>\n", 
			$GLOBALS['SQL_PRIMARY_RESOURCE']->errno,
			sql_escape_string($GLOBALS['SQL_PRIMARY_RESOURCE']->error),
			$text, 
			$file, 
			$line); 
		return $error;
	}
	
	function get_devices($latN, $lngE, $latS, $lngW)
	{
		$devices = array();
		$query = sprintf("SELECT `d`.*, `s`.`uiData` 
			FROM %s `d`
			LEFT JOIN ( SELECT `deviceID`, CONCAT(
				CASE WHEN `B0C0` = 1 THEN 'R' WHEN `B0C1` = 1 THEN 'Y' WHEN `B0C2` = 1 THEN 'G' END,
				CASE WHEN `B0C3` = 1 THEN 'R' WHEN `B0C4` = 1 THEN 'Y' WHEN `B0C5` = 1 THEN 'G' END,
				CASE WHEN `B0C8` = 1 THEN 'R' WHEN `B0C9` = 1 THEN 'Y' WHEN `B0CA` = 1 THEN 'G' END,
				CASE WHEN `B0CB` = 1 THEN 'R' WHEN `B0CC` = 1 THEN 'Y' WHEN `B0CD` = 1 THEN 'G' END,
				CASE WHEN `B1C0` = 1 THEN 'R' WHEN `B1C1` = 1 THEN 'Y' WHEN `B1C2` = 1 THEN 'G' END,
				CASE WHEN `B1C3` = 1 THEN 'R' WHEN `B1C4` = 1 THEN 'Y' WHEN `B1C5` = 1 THEN 'G' END,
				CASE WHEN `B1C8` = 1 THEN 'R' WHEN `B1C9` = 1 THEN 'Y' WHEN `B1CA` = 1 THEN 'G' END,
				CASE WHEN `B1CB` = 1 THEN 'R' WHEN `B1CC` = 1 THEN 'Y' WHEN `B1CD` = 1 THEN 'G' END
				) AS `uiData`
				FROM %s
				GROUP BY `deviceID`
				ORDER BY  `deviceID` DESC
			) `s` ON `d`.`id`=`s`.`deviceID`
			WHERE (%0.06f < %0.06f AND `deviceLat` BETWEEN %0.06f AND %0.06f)
			OR (%0.06f < %0.06f AND `deviceLat` BETWEEN %0.06f AND %0.06f)
			AND (%0.06f < %0.06f AND `deviceLng` BETWEEN %0.06f AND %0.06f)
			OR (%0.06f < %0.06f AND `deviceLng` BETWEEN %0.06f AND %0.06f)",
			constant('DB_DEVICE'),
			constant('DB_DATA'),
			$latN, $latS, $latN, $latS,
			$latS, $latN, $latS, $latN,
			$lngE, $lngW, $lngE, $lngW,
			$lngW, $lngE, $lngW, $lngE
			);
		$result = sql_query($query);
		if ($result == FALSE)
			exit(sql_log_error($query, __FILE__, __LINE__));
		while ( ($row = $result->fetch_assoc()) )
		{
			array_push($devices, format_device_info($row));
		}
		return $devices;
	}
	function format_device_info($device)
	{
		$info = array();
		$info['markerID'] = $device['id'];
		$info['deviceID'] = $device['deviceID'];
		$info['name'] = $device['name'];
		$info['locality'] = $device['locality'];
		$info['markerLat'] = $device['deviceLat'];
		$info['markerLng'] = $device['deviceLng'];
		$info['markerIcon'] = $device['uiIcon'];
		$info['uiLat'] = $device['uiLat'];
		$info['uiLng'] = $device['uiLng'];
		$info['uiData'] = $device['uiData'];
		$info['uiHeading'] = $device['uiHeading'];
		$info['uiScale'] = $device['uiScale'];
		return $info;
	}

	function get_status($latN, $lngE, $latS, $lngW, $since)
	{
		$status = array();
		$latest = false;
		$query = sprintf("SELECT `id` 
			FROM %s
			WHERE (%0.06f < %0.06f AND `deviceLat` BETWEEN %0.06f AND %0.06f)
			OR (%0.06f < %0.06f AND `deviceLat` BETWEEN %0.06f AND %0.06f)
			AND (%0.06f < %0.06f AND `deviceLng` BETWEEN %0.06f AND %0.06f)
			OR (%0.06f < %0.06f AND `deviceLng` BETWEEN %0.06f AND %0.06f)",
			constant('DB_DEVICE'),
			$latN, $latS, $latN, $latS,
			$latS, $latN, $latS, $latN,
			$lngE, $lngW, $lngE, $lngW,
			$lngW, $lngE, $lngW, $lngE
			);
		$WHERE = $since ? sprintf(" AND `modified` > '%s'", sql_escape_string($since)) : "";
		$query = sprintf("SELECT `deviceID`, CONCAT(
				CASE WHEN `B0C0` = 1 THEN 'R' WHEN `B0C1` = 1 THEN 'Y' WHEN `B0C2` = 1 THEN 'G' END,
				CASE WHEN `B0C3` = 1 THEN 'R' WHEN `B0C4` = 1 THEN 'Y' WHEN `B0C5` = 1 THEN 'G' END,
				CASE WHEN `B0C8` = 1 THEN 'R' WHEN `B0C9` = 1 THEN 'Y' WHEN `B0CA` = 1 THEN 'G' END,
				CASE WHEN `B0CB` = 1 THEN 'R' WHEN `B0CC` = 1 THEN 'Y' WHEN `B0CD` = 1 THEN 'G' END,
				CASE WHEN `B1C0` = 1 THEN 'R' WHEN `B1C1` = 1 THEN 'Y' WHEN `B1C2` = 1 THEN 'G' END,
				CASE WHEN `B1C3` = 1 THEN 'R' WHEN `B1C4` = 1 THEN 'Y' WHEN `B1C5` = 1 THEN 'G' END,
				CASE WHEN `B1C8` = 1 THEN 'R' WHEN `B1C9` = 1 THEN 'Y' WHEN `B1CA` = 1 THEN 'G' END,
				CASE WHEN `B1CB` = 1 THEN 'R' WHEN `B1CC` = 1 THEN 'Y' WHEN `B1CD` = 1 THEN 'G' END
			) AS `status`,
			`modified`
			FROM %s
			WHERE `deviceID` IN (%s) $WHERE
			GROUP BY `deviceID`
			ORDER BY `modified` DESC",
			constant('DB_DATA'),
			$query
		);
		$result = sql_query($query);
		if ($result == FALSE)
			exit(sql_log_error($query, __FILE__, __LINE__));
		while ( ($row = $result->fetch_assoc()) )
		{
			$status[intval($row['deviceID'])] = $row['status'];
			$latest = max($latest, $row['modified']);
		}
		return array($status, $latest);
	}

?>