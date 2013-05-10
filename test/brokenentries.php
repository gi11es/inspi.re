#!/usr/bin/php
<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Regenerate huge thumbnails to new size
*/

require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$user = User::getSessionUser();

$entrylist = EntryList::getByStatus($ENTRY_STATUS['POSTED']);

foreach ($entrylist as $eid) {
	$entry = Entry::get($eid);
	
	try {
		$picture = Picture::get($entry->getPid());
	} catch (PictureException $e) {
		echo 'Broken entry with eid='.$eid.' deleted'."\r\n";
	
		try {
			$author = User::get($entry->getUid());
			$author->givePoints($entry->getDeletionPoints());
		} catch (UserException $e) {}
		
		//$entry->delete();
	}
}
?>