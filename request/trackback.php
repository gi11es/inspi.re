<?php

set_time_limit(3600);

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Sends an alert to members when there is a new post on the official blog
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/trackback.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../constants.php');

header( "content-type: application/xml; charset=UTF-8" );
$xml = new DomDocument('1.0', 'UTF-8');
$xml_response = $xml->createElement('response');
$xml->appendChild($xml_response);

if (isset($_REQUEST['url'])) {
	try {
		$trackback = Trackback::get($_REQUEST['url']);
		$xml_error = $xml->createElement('error', 1);
	} catch (TrackbackException $e) {
		$trackback = new Trackback($_REQUEST['url']);
		if (isset($_REQUEST['title']) && isset($_REQUEST['code']) && strcasecmp($_REQUEST['code'], 'tr4ckb4ck') == 0) {
			$alert = new Alert($ALERT_TEMPLATE_ID['NEW_BLOG_POST']);
			$aid = $alert->getAid();
			
			$alert_variable = new AlertVariable($aid, 'title', $_REQUEST['title']);
			
			foreach (UserList::getByStatus($USER_STATUS['ACTIVE']) as $uid => $last_time)
			//foreach (UserList::getActive30Days() as $uid => $last_time)
				$alert_instance = new AlertInstance($aid, $uid, $ALERT_INSTANCE_STATUS['ASYNC']);
		
			$xml_error = $xml->createElement('error', 0);
		} else $xml_error = $xml->createElement('error', 1);
	}
} else $xml_error = $xml->createElement('error', 1);

$xml_response->appendChild($xml_error);

print $xml->saveXML();

?>