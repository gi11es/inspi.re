#!/usr/bin/php
<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Check how the votes are spread over time
*/

require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/entryvotelist.php');
require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$activeuserlist = UserList::getByStatus($USER_STATUS['ACTIVE']);
$lazyvoterlist = array();

foreach ($activeuserlist as $uid => $user_creation_time) {
	$votelist = EntryVoteList::getByUidAndStatus($uid, $ENTRY_VOTE_STATUS['CAST'], false);
	$orderedvotelist = array();
	foreach ($votelist as $eid => $data) {
		$orderedvotelist[$eid] = $data['creation_time'];
	}
	
	arsort($orderedvotelist);
	$last_vote = 0;
	$same_vote_counter = 0;
	foreach ($orderedvotelist as $eid => $creation_time) if ($creation_time > 0) {
		if ($last_vote == $votelist[$eid]['points']) {
			$same_vote_counter++;
		} else {
			if ($same_vote_counter >= 30) {
				if (!isset($lazyvoterlist[$uid])) $lazyvoterlist[$uid] = array();
				
				$lazyvoterlist[$uid] []= array('vote' => $last_vote, 'times' => $same_vote_counter);
			}
			$same_vote_counter = 0;
			$last_vote = $votelist[$eid]['points'];
		}
	}
	
	if ($same_vote_counter >= 30) {
		if (!isset($lazyvoterlist[$uid])) $lazyvoterlist[$uid] = array();
		
		$lazyvoterlist[$uid] []= array('vote' => $last_vote, 'times' => $same_vote_counter);
	}
}

print_r($lazyvoterlist);
?>