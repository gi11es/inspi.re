#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Delete user block whose source or destination has deleted his/her account
 */

require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/userblock.php');
require_once(dirname(__FILE__).'/../entities/userblocklist.php');
require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');

if (System::isOtherCopyRunning('userblockcleanup.php')) {
	echo 'Had to abort user block cleanup cron job, it was already running';
} else {
	$userblocklist = UserBlockList::getAll();
	
	foreach ($userblocklist as $userblock) {
		try {
			$user = User::get($userblock['uid']);
		} catch (UserException $e) {
			$block = UserBlock::get($userblock['uid'], $userblock['blocked_uid']);
			$block->delete();
		}
		
		try {
			$user = User::get($userblock['blocked_uid']);
		} catch (UserException $e) {
			$block = UserBlock::get($userblock['uid'], $userblock['blocked_uid']);
			$block->delete();
		}
	}
}

?>
