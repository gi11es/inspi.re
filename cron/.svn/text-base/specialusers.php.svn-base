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

$duration = 86400;

$activeuserlist = UserList::getByStatus($USER_STATUS['ACTIVE']);

$receiverlist = array();
$commenterlist = array();
$prolificlist = array();
$voterlist = array();

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
	
	$entrylist = EntryList::getByUidAndStatus($uid, $ENTRY_STATUS['POSTED']);
	
	foreach ($entrylist as $cid => $eid) {
		$entry = Entry::get($eid);
		if ($entry->getCreationTime() >= time() - $duration) {
			if (!isset($prolificlist[$uid])) $prolificlist[$uid] = 1;
			else $prolificlist[$uid]++;
		}
	}
	
	$entryvotelist = EntryVoteList::getByUidAndStatus($uid, $ENTRY_VOTE_STATUS['CAST'], false);
	
	foreach ($entryvotelist as $eid => $properties) {
		if ($properties['creation_time'] >= time() - $duration) {
			if (!isset($voterlist[$uid])) $voterlist[$uid] = 1;
			else $voterlist[$uid]++;
		}
	}
}

asort($commenterlist);
$mosthelpful = array_pop(array_keys($commenterlist));
$commentcount = array_pop($commenterlist);

try {
	$specialuser = SpecialUser::get($SPECIAL_USER['MOST_HELPFUL']);
	$specialuser->setUid($mosthelpful);
	$specialuser->setValue($commentcount);
} catch (SpecialUserException $e) {
	echo 'Most helpful not found in DB!';
	$specialuser = new SpecialUser($SPECIAL_USER['MOST_HELPFUL'], $mosthelpful, $commentcount);
}

try {
	$helpfuluser = User::get($mosthelpful);
	$referencetime = max(time(), $helpfuluser->getPremiumTime());
	$helpfuluser->setPremiumTime($referencetime + 86400);
	
	$levels = UserLevelList::getByUid($mosthelpful);
	if (!in_array($USER_LEVEL['PREMIUM'], $levels))
		$userlevel = new UserLevel($mosthelpful, $USER_LEVEL['PREMIUM']);
	
	$alert = new Alert($ALERT_TEMPLATE_ID['MOST_HELPFUL']);
	$aid = $alert->getAid();
	$alert_variable = new AlertVariable($aid, 'href', '/Members/s3-l'.$helpfuluser->getLid());
	$alert_instance = new AlertInstance($aid, $mosthelpful, $ALERT_INSTANCE_STATUS['NEW']);
} catch (UserException $e) {}

//echo 'Most helpful user = '.$mosthelpful.' with '.$commentcount.' words of comments'."\r\n";

asort($prolificlist);
$mostprolific = array_pop(array_keys($prolificlist));
$entriescount = array_pop($prolificlist);

try {
	$specialuser = SpecialUser::get($SPECIAL_USER['MOST_PROLIFIC']);
	$specialuser->setUid($mostprolific);
	$specialuser->setValue($entriescount);
} catch (SpecialUserException $e) {
	echo 'Most prolific not found in DB!';
	$specialuser = new SpecialUser($SPECIAL_USER['MOST_PROLIFIC'], $mostprolific, $entriescount);
}

//echo 'Most prolific user = '.$mostprolific.' with '.$entriescount.' entries'."\r\n";

asort($voterlist);
$biggestvoter = array_pop(array_keys($voterlist));
$votecount = array_pop($voterlist);

try {
	$specialuser = SpecialUser::get($SPECIAL_USER['BIGGEST_VOTER']);
	$specialuser->setUid($biggestvoter);
	$specialuser->setValue($votecount);
} catch (SpecialUserException $e) {
	echo 'Biggest voter not found in DB!';
	$specialuser = new SpecialUser($SPECIAL_USER['BIGGEST_VOTER'], $biggestvoter, $votecount);
}

//echo 'Biggest voter = '.$biggestvoter.' with '.$votecount.' votes'."\r\n";

?>