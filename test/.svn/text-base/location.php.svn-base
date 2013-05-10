#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Finds users near a specific location
 */

require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../utilities/system.php');
require_once(dirname(__FILE__).'/../constants.php');

function distance($lat1, $lon1, $lat2, $lon2, $unit) { 

  $theta = $lon1 - $lon2; 
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
  $dist = acos($dist); 
  $dist = rad2deg($dist); 
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344); 
  } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
        return $miles;
      }
}


$userlist = UserList::getByStatus($USER_STATUS['ACTIVE']);

$usercache = User::getArray(array_keys($userlist));

$memberlocation = array();

foreach ($usercache as $uid => $user) {
	$iphistory = $user->getIPHistory();
	arsort($iphistory);
	$lastip = array_shift(array_keys($iphistory));
	
	if (array_shift($iphistory) > time() - 86400 * 30) {
		$record = @geoip_record_by_name($lastip);
		if (isset($record['latitude']) && isset($record['longitude'])) 
			$memberlocation[$uid] = $record['longitude'].','.$record['latitude'];
	}
}

header('Content-Type: application/force-download');  
header('Content-Transfer-Encoding: application/octet-stream'); 
header('Content-disposition: filename=inspi.re_members.kml');

$doc = new DOMDocument('1.0', 'UTF-8'); 
$doc->formatOutput = true;

$kml = $doc->createElement('kml');
$kml = $doc->appendChild($kml);

$kml->setAttribute('xmlns', 'http://www.opengis.net/kml/2.2');

$document = $doc->createElement('Document');
$document = $kml->appendChild($document);

foreach ($memberlocation as $uid => $latlong) {
	$placemark = $doc->createElement('Placemark');
	$placemark = $document->appendChild($placemark);
	
	$name = $doc->createElement('name');
	$name = $placemark->appendChild($name);
	$nametext = $doc->createTextNode($usercache[$uid]->getUniqueName());
	$nametext = $name->appendChild($nametext);
	
	$point = $doc->createElement('Point');
	$point = $placemark->appendChild($point);
	
	$coordinates = $doc->createElement('coordinates');
	$coordinates = $point->appendChild($coordinates);
	$coordinatestext = $doc->createTextNode($latlong);
	$coordinatestext = $coordinates->appendChild($coordinatestext);
}

echo $doc->saveXML();

?>