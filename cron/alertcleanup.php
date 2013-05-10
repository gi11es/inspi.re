#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Delete alerts that are more than a month old since the member's last connection
 */

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertlist.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertinstancelist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../constants.php');

if (System::isOtherCopyRunning('alertcleanup.php')) {
	echo 'Had to abort alert cleanup cron job, it was already running';
} else {
	$uids = array_keys(UserList::getByStatus($USER_STATUS['ACTIVE'], false));

	$usercache = User::getArray($uids, false);
	
	foreach ($usercache as $uid => $user) {
		$ip_history = $user->getIPHistory();
		if (!empty($ip_history)) {
			$last_login = max(array_values($ip_history));
			$limit = $last_login - 2592000;
			
			$alertinstancelist = AlertInstanceList::getByUid($uid, false);
			
			$alertcache = Alert::getArray($alertinstancelist, false);
			
			foreach ($alertcache as $aid => $alert) {
				if ($alert->getCreationTime() < $limit) {
					$alertinstance = AlertInstance::get($aid, $uid);
					$alertinstance->delete();
				}
			}
		}
	}
	
	// Look for orphan alerts
	
	$aids = AlertList::getAll();
	foreach ($aids as $aid) {
		$alertinstancelist = AlertInstanceList::getByAid($aid, false);
		if (empty($alertinstancelist)) try {
			$alert = Alert::get($aid);
			$alert->delete();
		} catch (AlertException $e) {}
		
		foreach ($alertinstancelist as $uid) try {
			$user = User::get($uid);
		} catch (UserException $e) {
			try {
				$alert = Alert::get($aid);
				$alert->delete();
			} catch (AlertException $e) {}
		}
	}
}

?>
