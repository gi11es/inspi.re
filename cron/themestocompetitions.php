#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Detect themes that were selected by popular vote and "convert" them into competitions
 */

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/communitylist.php');
require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/competitionlist.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/themelist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/cache.php');
require_once(dirname(__FILE__).'/../utilities/system.php');
require_once(dirname(__FILE__).'/../utilities/url.php');
require_once(dirname(__FILE__).'/../constants.php');

if (System::isOtherCopyRunning('themestocompetitions.php')) {
	echo 'Had to abort thumbnails cron job, it was already running';
} else {
	$communitylist = CommunityList::getByStatus($COMMUNITY_STATUS['ACTIVE']);
	$communitylist = array_merge($communitylist, CommunityList::getByStatus($COMMUNITY_STATUS['INACTIVE']));
	
	$communitycache = Community::getArray($communitylist);
	
	foreach ($communitycache as $xid => $community) {
		// Let's start looking for the end time of the latest competition that happened, if any
		$competitionlist = CompetitionList::getByXid($xid);
		
		$next_start_time = $community->getCreationTime() - ($community->getCreationTime() % 3600) + $community->getFrequency() * 86400;

		if (!empty($competitionlist)) {
			arsort($competitionlist);
			$real_last_time = array_shift($competitionlist);
			$last_start_time = $real_last_time - ($real_last_time % 3600);
			$next_start_time = $last_start_time + $community->getFrequency() * 86400;
		}
		
		$next_start_time = $community->getNextCompetitionTime($next_start_time);
		
		// If the time now is greater than the expected next competition start and within one hour of the official start hour
		if ($next_start_time <= gmmktime() && ((gmmktime() - $next_start_time) % 86400) <= 3600) {
			$community->startNextCompetition();
		}
	}
}

?>