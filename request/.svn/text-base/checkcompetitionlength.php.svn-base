<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/competitionlist.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../constants.php');

$competitionlist = CompetitionList::getByStatus($COMPETITION_STATUS['CLOSED']);

foreach ($competitionlist as $cid => $start_time) {
	$entrylist = Entrylist::getByCidAndStatus($cid, $ENTRY_STATUS['POSTED']);
	$entrylist += Entrylist::getByCidAndStatus($cid, $ENTRY_STATUS['DELETED']);
	
	$maxrank = 0;
	foreach ($entrylist as $uid => $eid) {
		$entry = Entry::get($eid);
		$rank = $entry->getRank();
		if ($rank > $maxrank) $maxrank = $rank;
	}
	
	$total_amount = max(count($entrylist), $maxrank);
	$competition = Competition::get($cid);
	$competition->setEntriesCount($total_amount);
}

?>