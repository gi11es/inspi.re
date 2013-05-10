#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Check which users commented/voted the most in the past 7 days
 */

require_once(dirname(__FILE__).'/../entities/discussionpost.php');
require_once(dirname(__FILE__).'/../entities/discussionpostlist.php');
require_once(dirname(__FILE__).'/../entities/discussionthread.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/entryvotelist.php');
require_once(dirname(__FILE__).'/../entities/specialuser.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../settings.php');
require_once(dirname(__FILE__).'/../constants.php');

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

$duration = 86400 * 30;

$activeuserlist = UserList::getByStatus($USER_STATUS['ACTIVE']);

$receiverlist = array();
$commenterlist = array();

foreach ($activeuserlist as $uid => $user_creation_time) {
	$commentsignatures = array();
	
	$discussionpostlist = DiscussionPostList::getByUidAndStatus($uid, $DISCUSSION_POST_STATUS['POSTED']);

	foreach ($discussionpostlist as $oid => $creation_time) if ($creation_time >= time() - $duration) try {
		$discussionpost = DiscussionPost::get($oid);
		$discussionthread = DiscussionThread::get($discussionpost->getNid());
		
		if ($discussionthread->getEid() !== null)  try {	
			$entry = Entry::get($discussionthread->getEid());
			unset($discussionthread);
			
			if ($entry->getUid() != $uid) {
				$receiver_uid = $entry->getUid();
				unset($entry);
			
				$signature = md5(trim($discussionpost->getText()));
				
				if (!in_array($signature, $commentsignatures)) {
					$commentsignatures []= $signature;				
					$wordcount = str_word_count_utf8($discussionpost->getText(), 0);

					if (!isset($commenterlist[$uid])) $commenterlist[$uid] = $wordcount;
					else $commenterlist[$uid] += $wordcount;
					
					if (!isset($receiverlist[$receiver_uid])) $receiverlist[$receiver_uid] = $wordcount;
					else $receiverlist[$receiver_uid] += $wordcount;
				}
			}
		} catch (EntryException $e) {}
	} catch (DiscussionThreadException $f) {}
}

$viplist = array();

foreach ($commenterlist as $uid => $wordcount) if ($wordcount > 50 && isset($receiverlist[$uid])) {
	if ($receiverlist[$uid] > 0) {
		$ratio = $wordcount / $receiverlist[$uid];
		if ($ratio >= 1.2) $viplist[$uid]= $ratio;
	}
} elseif ($wordcount > 50)  $viplist[$uid]= 1.2;

$oldcommentatorslist = UserLevelList::getByLevel($USER_LEVEL['BIG_COMMENTATOR']);

$i = 0;

foreach (array_diff(array_keys($viplist), $oldcommentatorslist) as $uid) {
	$userlevel = new UserLevel($uid, $USER_LEVEL['BIG_COMMENTATOR']);
	$i++;
}

//if ($i > 0) echo $i.' members added to the big commentators list'."\r\n";

$i = 0;

foreach (array_diff($oldcommentatorslist, array_keys($viplist)) as $uid) try {
	$userlevel = UserLevel::get($uid, $USER_LEVEL['BIG_COMMENTATOR']);
	$userlevel->delete();
	$i++;
} catch (UserLevelException $e) {}

//if ($i > 0) echo $i.' members removed from the big commentators list'."\r\n";

//echo '(count='.count($viplist).', max='.max($viplist).', average='.(array_sum($viplist) / count($viplist)).')'."\r\n";

?>