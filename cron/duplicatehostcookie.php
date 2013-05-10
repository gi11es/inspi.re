#!/usr/bin/php
<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Look for people who have been using the same computer
*/

require_once(dirname(__FILE__).'/../entities/entryvoteblocked.php');
require_once(dirname(__FILE__).'/../entities/entryvoteblockedlist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

function addVoteBlock($voter_uid, $author_uid) {
	$entryvoteblockedlist = EntryVoteBlockedList::getByVoterUid($voter_uid);
	if (!in_array($author_uid, $entryvoteblockedlist)) {
		$entryvoteblocked = new EntryVoteBlocked($voter_uid, $author_uid);
		//echo 'uid= '.$voter_uid.' and uid='.$author_uid.' used the same computer'."\r\n";
	}
}

$hostcookielist = UserList::getDuplicateHostCookie();

foreach ($hostcookielist as $hostcookie) {
	$userlist = UserList::getByHostCookie($hostcookie, false);
	
	// Check that it's not an admin through impersonation
	if (!in_array('59', $userlist) && !in_array('4a251163c6eb0', $userlist)) {
		foreach ($userlist as $voter_uid) foreach ($userlist as $author_uid) {
			if ($voter_uid != $author_uid) addVoteBlock($voter_uid, $author_uid);
		}
	}
}
?>