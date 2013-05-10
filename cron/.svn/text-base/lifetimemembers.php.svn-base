#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Automatically extend lifetime members' membership cutoff date, forever
 */

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../settings.php');
require_once(dirname(__FILE__).'/../constants.php');

$premiumlist = UserLevelList::getByLevel($USER_LEVEL['PREMIUM'], false);

$usercache = User::getArray($premiumlist, false);

foreach ($usercache as $uid => $user) if ($user->getPremiumTime() - time() > 94608000) 
	$user->setPremiumTime(time() + 315360000);

?>