#!/usr/bin/php
<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Find people who are lazy commenting and voting-wise so that they get more ads thrown at them
*/

require_once(dirname(__FILE__).'/../entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/entryvotelist.php');
require_once(dirname(__FILE__).'/../entities/pointsvalue.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$activeuserlist = UserList::getByStatus($USER_STATUS['ACTIVE'], false);

$pointsvalue = PointsValue::get($POINTS_VALUE_ID['ENTRY_POSTING']);
$entrycost = - $pointsvalue->getValue();

$twoweeksago = time() - 1209600;

// We only deal with people who have been on the website for more than 2 weeks
foreach ($activeuserlist as $uid => $user_creation_time) if ($user_creation_time < $twoweeksago)  {
	$user = User::get($uid);

	$entrycount = count(EntryList::getByUidAndStatus($uid, $ENTRY_STATUS['POSTED'], false));
	$commentcount = count(DiscussionPostList::getByUidAndStatus($uid, $DISCUSSION_POST_STATUS['POSTED'], false));
	$votecount = count(EntryVoteList::getByUidAndStatus($uid, $ENTRY_VOTE_STATUS['CAST'], false));

	$lazy = ($entrycount > 5 && $commentcount < $entrycount * 1.5 && $votecount < $entrycount * $entrycost * 1.5);
		
	if ($user->getLazy() != $lazy) $user->setLazy($lazy);
}
?>