#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Force-join everyone on the website to the prize community
 */

require_once(dirname(__FILE__).'/../entities/communitymembership.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');

if (System::isOtherCopyRunning('joinprizecommunity.php')) {
	echo 'Had to abort cron job, it was already running';
} else {
	$userlist = UserList::getByStatus($USER_STATUS['ACTIVE']);
	foreach ($userlist as $uid => $dontcare) {
	    $membership = new CommunityMembership(267, $uid, $COMMUNITY_MEMBERSHIP_STATUS['ACTIVE']);
	}
}

?>
