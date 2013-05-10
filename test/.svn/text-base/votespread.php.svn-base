#!/usr/bin/php
<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Check how the votes are spread over time
*/

require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/entryvote.php');
require_once(dirname(__FILE__).'/../entities/entryvotelist.php');
require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');


$user = User::getSessionUser();

$entrylist = EntryList::getByStatus($ENTRY_STATUS['POSTED'], false);
$entrylist = array_merge($entrylist, EntryList::getByStatus($ENTRY_STATUS['DELETED'], false));

$entrycache = Entry::getArray($entrylist, false);

$votespread = array();

foreach ($entrycache as $eid => $entry) {
	$day = date('m/Y', $entry->getCreationTime());
	if (!isset($votespread[$day])) $votespread[$day] = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);
	
	$entryvotelist = EntryVoteList::getByEid($eid, false);
	foreach ($entryvotelist as $uid => $points) {
		$entryvote = EntryVote::get($eid, $uid, false);
		if ($entryvote->getStatus() == $ENTRY_VOTE_STATUS['CAST'])
			$votespread[$day][$points]++;
	}
}

//header('Content-Type: application/force-download');  
//header('Content-Transfer-Encoding: application/octet-stream'); 
//header('Content-disposition: filename=votespread.csv');

foreach ($votespread as $day => $votes) {
	echo '"',$day.'"';
	$total = array_sum(array_values($votes));
	foreach ($votes as $points => $vote)
		echo ','.$vote / $total * 100;
	echo "\r\n";
}
?>