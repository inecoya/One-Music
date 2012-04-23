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
	
	$master = json_decode(file_get_contents("../db/master.json"));
	
	$mp3 = $default_mp3;
	$title = $default_title;
	
	if (isset($master[$hour])) {
		if (preg_match("/fine/i", $weather) || preg_match("/clear/i", $weather)) {
			$url = $master[$hour]->fine->url;
			$mp3 = $master[$hour]->fine->mp3;
			$title = $master[$hour]->fine->title;
		} else if (preg_match("/rain/i", $weather) || preg_match("/thunder/i", $weather)) {
			$url = $master[$hour]->rain->url;
			$mp3 = $master[$hour]->rain->mp3;
			$title = $master[$hour]->rain->title;
		} else if (preg_match("/cloud/i", $weather)) {
			$url = $master[$hour]->cloud->url;
			$mp3 = $master[$hour]->cloud->mp3;
			$title = $master[$hour]->cloud->title;
		} else if (preg_match("/snow/i", $weather)) {
			$url = $master[$hour]->snow->url;
			$mp3 = $master[$hour]->snow->mp3;
			$title = $master[$hour]->snow->title;
		} else {
			$url = $master[$hour]->fine->url;
			$mp3 = $master[$hour]->fine->mp3;
			$title = $master[$hour]->fine->title;
		}
	}
	
	echo json_encode(array("url" => $url, "mp3" => $mp3, "title" => $title));
} else {
	echo 'error';
}

?>