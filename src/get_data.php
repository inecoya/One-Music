<?php

if (isset($_POST['lat']) && isset($_POST['lon']) && isset($_POST['h'])) {
	foreach ($_REQUEST as $key => $value) {
		$_REQUEST[$key] = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
	}
	
	$city = "";
	$weather = "";
	$url = "";
	$mp3 = "";
	$title = "";
	
	$hour = $_REQUEST['h'];
	$lat = $_REQUEST['lat'];
	$lon = $_REQUEST['lon'];
	$default_mp3 = "http://yourdomain.com/sample.mp3";
	$default_title = "your song";
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$lon.'&hl=en&sensor=false');
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$res = curl_exec($ch);
	curl_close($ch);
	
	$json = json_decode($res);
	if ($json->status == "OK") {
		foreach($json->results[0]->address_components as $value) {
			if ($value->types[0] == 'locality' && $value->types[1] == 'political') {
				$city = $value->long_name;
			}
		}
	}
	
	if ($city != "") {
		$root = simplexml_load_file('http://www.google.com/ig/api?weather='.urlencode($city).'&hl=en');
		$weather = $root->weather->current_conditions->condition['data'];
	}
	
	$link = sqlite_open('../db/master.db', 0666, $sqliteerror);
	
	$mp3 = $default_mp3;
	$title = $default_title;
	
	if ($link) {
		if (preg_match("/fine/i", $weather) || preg_match("/clear/i", $weather)) {
			$type=1;
		} else if (preg_match("/rain/i", $weather) || preg_match("/thunder/i", $weather)) {
			$type=2;
		} else if (preg_match("/cloud/i", $weather)) {
			$type=3;
		} else if (preg_match("/snow/i", $weather)) {
			$type=4;
		} else {
			$type=1;
		}
		
		$sql = sprintf("select mp3, title, url from music where id=%d and type=%d", $hour, $type);
		$result = sqlite_query($link, $sql, SQLITE_BOTH, $sqliteerror);
		if ($result) {
			if (sqlite_num_rows($result) > 0) {
				sqlite_rewind($result);
				$rows = sqlite_fetch_array($result, SQLITE_ASSOC);
				$mp3 = $rows['mp3'];
				$url = $rows['url'];
				$title = $rows['title'];
			}
		}
		
		sqlite_close($link);
	}
	
	echo json_encode(array("url" => $url, "mp3" => $mp3, "title" => $title));
} else {
	echo 'error';
}

?>