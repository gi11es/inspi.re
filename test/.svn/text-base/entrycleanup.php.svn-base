#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Delete entries whose author has deleted his/her account
 */

require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');

if (System::isOtherCopyRunning('entrycleanup.php')) {
	echo 'Had to abort alert cleanup cron job, it was already running';
} else {
	$eids = EntryList::getByStatus($ENTRY_STATUS['POSTED'], false);
	$eids = array_merge($eids, EntryList::getByStatus($ENTRY_STATUS['DELETED'], false));
	$eids = array_merge($eids, EntryList::getByStatus($ENTRY_STATUS['ANONYMOUS'], false));
	$eids = array_merge($eids, EntryList::getByStatus($ENTRY_STATUS['BANNED'], false));
	$eids = array_merge($eids, EntryList::getByStatus($ENTRY_STATUS['DISQUALIFIED'], false));
	
	$userlist = array();

	foreach ($eids as $eid) if ($eid == 108859) {
		$entry = Entry::get($eid, false);
		try {
			$author = User::get($entry->getUid(), false);
		} catch (UserException $e) {

			$cid = $entry->getCid();
			$competition = Competition::get($cid);
			if ($competition->getStatus() == $COMPETITION_STATUS['CLOSED']) {

				$pid = $entry->getPid();
				try {
					$picture = Picture::get($pid);
					$picture->delete();
				} catch (PictureException $e) {}
				
				$entry->setPid(null);
				if ($entry->getStatus() != $ENTRY_STATUS['BANNED'] && $entry->getStatus() != $ENTRY_STATUS['DISQUALIFIED'])
					$entry->setStatus($ENTRY_STATUS['DELETED']);
			} else $entry->delete();
		}
	}
}

?>
