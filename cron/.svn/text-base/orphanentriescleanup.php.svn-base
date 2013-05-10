#!/usr/bin/php
<?php

/* 
 * Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 *       
 * Cron job to fix data issues with some competition entries. These inconsistencies can appear 
 * if a process dies prematurely. They're harmless but can cause to orphan or dead-end 
 * navigation paths.
 */

require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$user = User::getSessionUser();

$entrylist = EntryList::getByStatus($ENTRY_STATUS['POSTED']);
$brokencompetitions = array();
$brokenentries = array();

foreach ($entrylist as $eid) {
	$entry = Entry::get($eid);
	
	try {
		$competition = Competition::get($entry->getCid());
	} catch (CompetitionException $e) {
		$competition = null;
		if (!in_array($entry->getEid(), $brokenentries)) $brokenentries []= $entry->getEid();
	}
	
	if ($competition !== null) try {
		Community::get($competition->getXid());
	} catch (CommunityException $e) {
		if (!in_array($competition->getCid(), $brokencompetitions)) {
			$brokencompetitions []= $competition->getCid();
		}
	}
}

foreach ($brokenentries as $eid) {
	$entry = Entry::get($eid);
	$entry->delete();
}

foreach ($brokencompetitions as $cid) {
	$competition = Competition::get($cid);
	$competition->delete();
}

if (count($brokenentries) > 0) echo count($brokenentries).' broken entries deleted'."\r\n";
if (count($brokencompetitions) > 0) echo count($brokencompetitions).' broken competitions deleted'."\r\n";
?>