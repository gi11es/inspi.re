#!/usr/bin/php
<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Check how many users display their rank publically
*/

require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$userlist = UserList::getActive30Days();

$usercache = User::getArray(array_keys($userlist));

$total = 0;
$sharerank = 0;

foreach ($usercache as $uid => $user) {
	$entrylist = EntryList::getByUidAndStatus($uid, $ENTRY_STATUS['POSTED']);
	if (count($entrylist) > 0) {
		if ($user->getDisplayRank()) $sharerank ++;
		$total++;
	}
}

echo (100 * $sharerank / $total).'% share their rank';

?>