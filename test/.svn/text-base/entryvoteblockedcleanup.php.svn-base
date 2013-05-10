#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Delete entry vote blocking for users who have left the website
 */

require_once(dirname(__FILE__).'/../entities/entryvoteblocked.php');
require_once(dirname(__FILE__).'/../entities/entryvoteblockedlist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');

if (System::isOtherCopyRunning('entryvoteblockedcleanup.php')) {
	echo 'Had to abort entry vote block cleanup cron job, it was already running';
} else {
	$entryvoteblockedlist = EntryVoteBlockedList::getAll();
	foreach ($entryvoteblockedlist as $entryvoteblocked) {
		try {
			$voter = User::get($entryvoteblocked['voter_uid']);
		} catch (UserException $e) {
			try {
				$evb = EntryVoteBlocked::get($entryvoteblocked['voter_uid'], $entryvoteblocked['author_uid']);
				$evb->delete();
			} catch (EntryVoteBlockedException $f) {}
		}
		
		try {
			$author = User::get($entryvoteblocked['author_uid']);
		} catch (UserException $e) {
			try {
				$evb = EntryVoteBlocked::get($entryvoteblocked['voter_uid'], $entryvoteblocked['author_uid']);
				$evb->delete();
			} catch (EntryVoteBlockedException $f) {}
		}
	}
}

?>
