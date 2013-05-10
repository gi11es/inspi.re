#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Delete moderators who have deleted their account
 */

require_once(dirname(__FILE__).'/../entities/communitymoderator.php');
require_once(dirname(__FILE__).'/../entities/communitymoderatorlist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');

if (System::isOtherCopyRunning('moderatorcleanup.php')) {
	echo 'Had to abort moderator cleanup cron job, it was already running';
} else {
	$count = 0;

	$communitymoderatorlist = CommunityModeratorList::getAll();
	
	foreach ($communitymoderatorlist as $communitymoderator) {
		try {
			$user = User::get($communitymoderator['uid']);
		} catch (UserException $e) {
			$moderator = CommunityModerator::get($communitymoderator['xid'], $communitymoderator['uid']);
			$moderator->delete();
			$count++;
		}
	}
	
	if ($count > 0) echo $count.' moderator(s) cleaned up';
}

?>
