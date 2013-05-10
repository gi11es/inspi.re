<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Lists eids of winners that qualify for the prize
*/

require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/competitionlist.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/prizewinner.php');
require_once(dirname(__FILE__).'/../entities/prizewinnerlist.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/inml.php');
require_once(dirname(__FILE__).'/../utilities/page.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$competitionlist = CompetitionList::getByStatus($COMPETITION_STATUS['CLOSED']);
$eligiblelist = array();

$competition = Competition::getArray(array_keys($competitionlist));
unset($competitionlist);
$winnerlist = array();

foreach ($competition as $cid => $comp) if ($competition[$cid]->getEndTime() >= gmmktime(0, 0, 0, gmdate('n'), 1) && $competition[$cid]->getEntriesCount() >= 15)
	$winnerlist = array_merge($winnerlist, array_values(EntryList::getByCidAndRank($cid, 1)));

$entrycache = Entry::getArray($winnerlist);
unset($winnerlist);
foreach ($entrycache as $eid => $entry) if ($entry->getStatus() == $ENTRY_STATUS['POSTED'])
	$eligiblelist[]= $eid;

if (!empty($eligiblelist)) {
	
	$nextmonth = gmdate('n') % 12 + 1;
	$nextyear =  gmdate('Y');
	
	if ($nextmonth == 1) $nextyear++;
	
	
	asort($eligiblelist);
	
	$userlist = array();
	$themelist = array();
	foreach ($eligiblelist as $eid) {
		$userlist []= $entrycache[$eid]->getUid();
		$themelist []= $competition[$entrycache[$eid]->getCid()]->getTid();
	}
		
	$usercache = User::getArray($userlist);
	$themecache = Theme::getArray($themelist);
	
	$themelist = array();
	
	$orderedeligiblelist = array();
	
	foreach ($eligiblelist as $eid)
		$orderedeligiblelist[$eid] = $competition[$entrycache[$eid]->getCid()]->getEndTime();
	
	asort($orderedeligiblelist);
	
	foreach ($orderedeligiblelist as $eid => $end_time) try {
		$pid = $entrycache[$eid]->getPid();
		
		$entry_user = $usercache[$entrycache[$eid]->getUid()];
		$theme = $themecache[$competition[$entrycache[$eid]->getCid()]->getTid()];
		$competitionname = $theme->getTitle();
					
		if ($pid !== null) echo $eid."\n";
	} catch (UserException $e) {}

}
?>
