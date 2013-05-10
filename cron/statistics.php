#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Generate statistics about the website
*/

require_once(dirname(__FILE__).'/../entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/../entities/discussionthread.php');
require_once(dirname(__FILE__).'/../entities/discussionthreadlist.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/entryvote.php');
require_once(dirname(__FILE__).'/../entities/entryvotelist.php');
require_once(dirname(__FILE__).'/../entities/statistic.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/page.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

define("WORD_COUNT_MASK", "/\p{L}[\p{L}\p{Mn}\p{Pd}'\x{2019}]*/u");

function str_word_count_utf8($string, $format = 0)
{
	switch ($format) {
	case 1:
		preg_match_all(WORD_COUNT_MASK, $string, $matches);
		return $matches[0];
	case 2:
		preg_match_all(WORD_COUNT_MASK, $string, $matches, PREG_OFFSET_CAPTURE);
		$result = array();
		foreach ($matches[0] as $match) {
			$result[$match[1]] = $match[0];
		}
		return $result;
	}
	return preg_match_all(WORD_COUNT_MASK, $string, $matches);
}

$recent_users = UserList::getRegistered24Hours(false);

$statistic = new Statistic($STATISTIC['REGISTRATIONS'], count($recent_users));

unset($recent_users);

$active_users_24_hours = UserList::getActive24Hours(false);

$statistic = new Statistic($STATISTIC['ACTIVE_MEMBERS'], count($active_users_24_hours));

unset($active_users_24_hours);

$entries = EntryList::getByStatus($ENTRY_STATUS['POSTED'], false);

$comments = DiscussionPostList::getByCreationTimeAndStatus(time() - 86400, $DISCUSSION_POST_STATUS['POSTED'], false);

$comments_wordcount = 0;

$discussionpostcache = DiscussionPost::getArray(array_keys($comments), false);

foreach ($discussionpostcache as $oid => $discussionpost) {
	try {
		$discussionthread = DiscussionThread::get($discussionpost->getNid(), false);
		if ($discussionthread->getEid() !== null)
			$comments_wordcount += str_word_count_utf8($discussionpost->getText());
	} catch (DiscussionThreadException $f) {}
}
unset($entries);

$statistic = new Statistic($STATISTIC['COMMENTS_WORDCOUNT'], $comments_wordcount);

$entries = EntryList::getByCreationTimeAndStatus(time() - 86400, $ENTRY_STATUS['POSTED'], false);

$statistic = new Statistic($STATISTIC['ENTRIES'], count($entries));

$votes_count = 0;
$votes = EntryVoteList::getByCreationTimeAndStatus(time() - 86400, $ENTRY_VOTE_STATUS['CAST'], false);
foreach ($votes as $eid => $pointslist) $votes_count += count($pointslist);

$statistic = new Statistic($STATISTIC['VOTES'], $votes_count);

?>