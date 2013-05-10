<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Recalculates the entry rankings in a competition
*/

require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/competitionlist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$user = User::getSessionUser();

$levels = UserLevelList::getByUid($user->getUid());

if (in_array($USER_LEVEL['ADMINISTRATOR'], $levels)) {
	if (isset($_REQUEST['cid'])) {
		$competition = Competition::get($_REQUEST['cid']);
		$competition->calculateRankings();
	} else {
		$competitionlist = CompetitionList::getByStatus($COMPETITION_STATUS['CLOSED']);
		foreach ($competitionlist as $cid => $start_time) {
			$competition = Competition::get($cid);
			$competition->calculateRankings();
		}
	}
}

if (isset($_SERVER['HTTP_REFERER']))
header('Location: '.$_SERVER['HTTP_REFERER']);

?>