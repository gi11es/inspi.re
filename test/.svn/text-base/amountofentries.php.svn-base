#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Calculates how many entries users have entered on the website so far
 */

require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../utilities/system.php');
require_once(dirname(__FILE__).'/../constants.php');

$userlist = UserList::getByStatus($USER_STATUS['ACTIVE']);

$suspiciouslist = array();

$cutoff = 70;
$count = 0;
$donatorscount = 0;

foreach ($userlist as $uid => $creation_time) try {
	$entrylist = EntryList::getByUidAndStatus($uid, $ENTRY_STATUS['POSTED']);
	if (count($entrylist) >= $cutoff) {
		$levellist = UserLevelList::getByUid($uid);
		if (in_array($USER_LEVEL['DONATOR'], $levellist)) $donatorscount++;
		$count++;
	}
} catch (UserException $e) {}

echo $count.' users have more than '.$cutoff.' entries ('.(100 * round($count / count($userlist), 4)).'% of active users)'."\r\n";
echo $donatorscount.' of which have donated in the past';
?>