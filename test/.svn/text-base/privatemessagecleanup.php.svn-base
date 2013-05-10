#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Delete private message whose source or destination has deleted his/her account
 */

require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/privatemessage.php');
require_once(dirname(__FILE__).'/../entities/privatemessagelist.php');
require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');

if (System::isOtherCopyRunning('privatemessagecleanup.php')) {
	echo 'Had to abort private message cleanup cron job, it was already running';
} else {
	$pmids = PrivateMessageList::getAll();
	
	foreach ($pmids as $pmid) {
		$privatemessage = PrivateMessage::get($pmid);
		try {
			$user = User::get($privatemessage->getSourceUid());
		} catch (UserException $e) {
			$privatemessage->delete();
		}
		
		try {
			$user = User::get($privatemessage->getDestinationUid());
		} catch (UserException $e) {
			$privatemessage->delete();
		}
	}
}

?>
