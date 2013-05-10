#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Looks through people's votes to add them to a sympathy vote blacklist
 */

require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/entryvote.php');
require_once(dirname(__FILE__).'/../entities/entryvotelist.php');
require_once(dirname(__FILE__).'/../entities/entryvoteblocked.php');
require_once(dirname(__FILE__).'/../entities/entryvoteblockedlist.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../utilities/system.php');
require_once(dirname(__FILE__).'/../constants.php');

$userlist = UserList::getByStatus($USER_STATUS['ACTIVE'], false);

$suspiciouslist = array();

foreach ($userlist as $uid => $creation_time) {
	$votelist = EntryVoteList::getByUidAndStatus($uid, $ENTRY_VOTE_STATUS['CAST'], false);
	$votestotal = array();
	$votescount = array();
	$votesaverage = array();
	$total = 0;
	$count = 0;
	
	$blockedlist = EntryVoteBlockedList::getByVoterUid($uid, false);
	
	foreach ($votelist as $eid => $array) {
		try {
			/*if ($array['author_uid'] === null) {
				$entryvote = EntryVote::get($eid, $uid);
				$entry = Entry::get($eid);
				
				$array['author_uid'] = $entry->getUid();
				$entryvote->setAuthorUid($array['author_uid']);
				unset($entry);
				unset($entryvote);
			}*/
			if (!isset($votestotal[$array['author_uid']])) $votestotal[$array['author_uid']] = 0;
			if (!isset($votescount[$array['author_uid']])) $votescount[$array['author_uid']] = 0;
			
			$votestotal[$array['author_uid']] += $array['points'];
			$votescount[$array['author_uid']]++;
			$total += $array['points'];
			$count++;
		} catch (EntryException $e) {}
	}
	
	if ($count > 0) {	
		$averagevote = $total / $count;
		
		foreach ($votescount as $author_uid => $local_count) {
			if (!in_array($author_uid, $blockedlist) && $local_count > 3 && $local_count > ($count / 4)) {
				$percent = round(100*$local_count/$count, 2);
				//echo $percent.'% of user with uid='.$uid.'\'s votes were on user with uid='.$author_uid."\r\n";
				$block = new EntryVoteBlocked($uid, $author_uid);
			} elseif (!in_array($author_uid, $blockedlist) && $local_count > 3) {
				$average = $votestotal[$author_uid] / $local_count;
				if (abs($averagevote - $average) > 2.0) {
					if (!isset($suspiciouslist[$uid])) $suspiciouslist[$uid] = array();
					$suspiciouslist[$uid][$author_uid] = $average;
				}
			}
		}
	}
}

unset($userlist);

foreach ($suspiciouslist as $uid => $array) {
	foreach ($array as $author_uid => $average) {
		$authorvotelist = EntryVoteList::getByAuthorUidAndStatus($author_uid, $ENTRY_VOTE_STATUS['CAST'], false);
		$author_total = 0;
		$author_count = 0;
		foreach ($authorvotelist as $eid => $votes) {
			$author_total += array_sum($votes);
			$author_count += count($votes);
		}
		$author_average = $author_total / $author_count;
		
		if (abs($author_average - $average) > 1.5) {
			//echo 'User with uid='.$uid.' voted an average of '.$average.' on user with uid='.$author_uid.'\'s entries'."\r\n";
			$block = new EntryVoteBlocked($uid, $author_uid);
		}
	}
}

?>