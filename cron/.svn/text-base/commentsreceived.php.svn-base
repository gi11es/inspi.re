#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Calculates how many comments a person has received so far
 */

require_once(dirname(__FILE__).'/../entities/discussionpost.php');
require_once(dirname(__FILE__).'/../entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/../entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../utilities/system.php');
require_once(dirname(__FILE__).'/../constants.php');

$userlist = UserList::getByStatus($USER_STATUS['ACTIVE']);

$suspiciouslist = array();

foreach ($userlist as $uid => $creation_time) try {
	$user = User::get($uid);
	$commentsreceived = 0;
	
	$entryuidlist = EntryList::getByUidAndStatus($uid, $ENTRY_STATUS['POSTED']);
	
	foreach ($entryuidlist as $local_cid => $local_eid) {
		if (!isset($entries[$local_eid])) $entries[$local_eid] = Entry::get($local_eid);
		
		$commentthreadlist = DiscussionThreadList::getByEid($local_eid);
		foreach ($commentthreadlist as $local_nid => $local_creation_time) {
			$commentlist = DiscussionPostList::getByNidAndStatus($local_nid, $DISCUSSION_POST_STATUS['POSTED']);
			foreach ($commentlist as $local_oid => $local_creation_time) try {
				$discussionpost = DiscussionPost::get($local_oid);
				if ($discussionpost->getUid() != $uid) $commentsreceived ++;
			} catch (DiscussionPostException $e) {}
		}
	}
	
	$user->setCommentsReceived($commentsreceived);
} catch (UserException $e) {}

?>