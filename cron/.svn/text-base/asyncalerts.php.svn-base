#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Sends alerts that would be too big to send during a user request
 */

require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertinstancelist.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/system.php');
require_once(dirname(__FILE__).'/../utilities/url.php');
require_once(dirname(__FILE__).'/../constants.php');

if (System::isOtherCopyRunning('asyncalerts.php')) {
	echo 'Had to abort async alerts cron job, it was already running';
} else {
	$alertinstancelist = AlertInstanceList::getByStatus($ALERT_INSTANCE_STATUS['ASYNC']);
	
	foreach ($alertinstancelist as $info) try {
	    $alertinstance = AlertInstance::get($info['aid'], $info['uid']);
	    $alertinstance->send();
	} catch (AlertInstanceException $e) {}
}

?>