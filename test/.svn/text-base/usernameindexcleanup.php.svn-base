#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Delete user name index whose user has deleted his/her account
 */

require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/usernameindex.php');
require_once(dirname(__FILE__).'/../entities/usernameindexlist.php');
require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');

if (System::isOtherCopyRunning('usernameindexcleanup.php')) {
	echo 'Had to abort user name index cleanup cron job, it was already running';
} else {
	$usernameindexlist = UserNameIndexList::getAll();
	
	foreach ($usernameindexlist as $usernameindex) {
		try {
			$user = User::get($usernameindex['uid']);
		} catch (UserException $e) {
			$nameindex = UserNameIndex::get($usernameindex['chunk'], $usernameindex['uid']);
			$nameindex->delete();
		}
	}
}

?>
