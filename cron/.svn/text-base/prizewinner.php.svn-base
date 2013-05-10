#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Pick a winner for the monthly cash prize
 */

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
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

// we need to make sure that the cron job isn't run multiple times in the same month accidentally

$prizewinnerlist = PrizeWinnerList::getAll();
$prizewinnercache = PrizeWinner::getArray($prizewinnerlist);
$prizewinnerdate = array();

foreach ($prizewinnercache as $eid => $prizewinner)
	$prizewinnerdate[$eid] = $prizewinner->getCreationTime();

arsort($prizewinnerdate);

if (!empty($prizewinnerdate)) {
	$latest_date = array_pop($prizewinnerdate);
	
	if (time() - $latest_date < 25 * 86400) {
		echo 'This cron job is premature, less than 25 days have gone by since the last winner';
		exit(0);
	}
}

$competitionlist = CompetitionList::getByStatus($COMPETITION_STATUS['CLOSED']);
$eligiblelist = array();

$competition = Competition::getArray(array_keys($competitionlist));
unset($competitionlist);

$winnerlist = array();

$startofmonth = gmmktime(0, 0, 0, gmdate('n', time() - 86400), 1);
$endofmonth = gmmktime(0, 0, 0, gmdate('n', time() + 86400 * 2), 1);

foreach ($competition as $cid => $comp) {
	$endtime = $competition[$cid]->getEndTime();
	
	if ($endtime >= $startofmonth && $endtime < $endofmonth  && $competition[$cid]->getEntriesCount() >= 15)
		$winnerlist = array_merge($winnerlist, array_values(EntryList::getByCidAndRank($cid, 1)));
}

$entrycache = Entry::getArray($winnerlist);
unset($winnerlist);

foreach ($entrycache as $eid => $entry) if ($entry->getStatus() == $ENTRY_STATUS['POSTED'])
	$eligiblelist[]= $eid;
	
do {	
	$winner_eid = $eligiblelist[array_rand($eligiblelist)];

	$user = User::get($entrycache[$winner_eid]->getUid());
} while (in_array($user->getUid(), $PRIZE_BLACKLIST));

$winner = new PrizeWinner($winner_eid, $user->getUid());

$alert = new Alert($ALERT_TEMPLATE_ID['PRIZE_WINNER']);
$aid = $alert->getAid();
$alert_variable = new AlertVariable($aid, 'prize_href', '/Prize/s4-l'.$user->getLid());
$alert_instance = new AlertInstance($aid, $user->getUid(), $ALERT_INSTANCE_STATUS['NEW']);

echo 'This month\'s prize winner has uid='.$user->getUid();

mail('support@inspi.re', date('F', time() - 86400).' prize winner', '', 'Reply-to: '.$user->getUniqueName().' <'.$user->getEmail().'>'."\r\n");

?>