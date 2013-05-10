#!/usr/bin/php
<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Give X days of premium membership to premium members to compensate for the downtime
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../settings.php');

$duration = 0; // 86400 * x

$premiumuserlist = UserLevelList::getByLevel($USER_LEVEL['PREMIUM']);
$premiumusercache = User::getArray($premiumuserlist);

foreach ($premiumusercache as $uid => $user) if ($duration > 0) {
	$referencetime = max(time(), $user->getPremiumTime());
	$user->setPremiumTime($referencetime + $duration);
}
?>