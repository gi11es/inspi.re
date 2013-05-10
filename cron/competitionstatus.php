#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Transitions competitions into their next stage
 */

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/competitionlist.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/system.php');
require_once(dirname(__FILE__).'/../constants.php');

if (System::isOtherCopyRunning('competitionstatus.php')) {
	echo 'Had to abort competition status cron job, it was already running';
} else {
	$competitionlist = CompetitionList::getByStatus($COMPETITION_STATUS['OPEN']);
	
	$competitioncache = Competition::getArray(array_keys($competitionlist));
	
	foreach ($competitioncache as $cid => $competition) if ($competition->getVoteTime() <= gmmktime())
		$competition->setStatus($COMPETITION_STATUS['VOTING']);
	
	$competitionlist = CompetitionList::getByStatus($COMPETITION_STATUS['VOTING']);
	
	$competitioncache = Competition::getArray(array_keys($competitionlist));
	
	foreach ($competitioncache as $cid => $competition) {
		$end_time = $competition->getEndTime();
		
		if ($end_time <= gmmktime()) {
			$competition->calculateRankings();
			$competition->setStatus($COMPETITION_STATUS['CLOSED']);
		
			// Send a rank alert to all participants
			$entrylist = EntryList::getByCidAndStatus($cid, $ENTRY_STATUS['POSTED']);
			
			$entrycache = Entry::getArray(array_values($entrylist));
			$usercache = User::getArray(array_keys($entrylist));
			
			foreach ($entrylist as $uid => $eid) if (isset($usercache[$uid]) && isset($entrycache[$eid])) {
				$user = $usercache[$uid];
				$entry = $entrycache[$eid];
				$rank = $entry->getRank();
				
				if ($rank !== null && $rank > 0 && $user->getStatus() == $USER_STATUS['ACTIVE']) {			
					$alert = new Alert($ALERT_TEMPLATE_ID['RANK']);
					$aid = $alert->getAid();
					$alert_variable = new AlertVariable($aid, 'href', $PAGE['ENTRY'].'?lid='.$user->getLid().'&home=true#eid='.$eid);
					$alert_variable = new AlertVariable($aid, 'entries_count', $competition->getEntriesCount());
					$alert_variable = new AlertVariable($aid, 'rank', $rank);
					$alert_variable = new AlertVariable($aid, 'tid', $competition->getTid());
					$alert_instance = new AlertInstance($aid, $uid, $ALERT_INSTANCE_STATUS['ASYNC']);
				}
			}
		}
	}
}

?>
