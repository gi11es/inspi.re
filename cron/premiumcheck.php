#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Check the premium membership status of users
 */

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../settings.php');
require_once(dirname(__FILE__).'/../constants.php');

$activeuserlist = UserList::getByStatus($USER_STATUS['ACTIVE']);

$premiumlist = UserLevelList::getByLevel($USER_LEVEL['PREMIUM']);

$usercache = User::getArray(array_keys($activeuserlist));

foreach ($usercache as $uid => $user) try {
	if ($user->getPremiumTime() > 0 && $user->getPremiumTime() < time() && in_array($uid, $premiumlist)) {
		try {
			$level = UserLevel::get($uid, $USER_LEVEL['PREMIUM']);
		} catch (UserLevelException $e) {
			continue;
		}
		
		$level->delete();
		$user->setHideAds(false);
		
		// Send alert about expired premium membership
		if ($user->getStatus() == $USER_STATUS['ACTIVE']) {
			$alert = new Alert($ALERT_TEMPLATE_ID['PREMIUM_EXPIRED']);
			$aid = $alert->getAid();
			$alert_variable = new AlertVariable($aid, 'href', $PAGE['PREMIUM'].'?lid='.$user->getLid());
			$alert_instance = new AlertInstance($aid, $user->getUid(), $ALERT_INSTANCE_STATUS['ASYNC']);
		}
	} elseif ($user->getPremiumTime() >= time() && !in_array($uid, $premiumlist)) {
		$level = new UserLevel($uid, $USER_LEVEL['PREMIUM']);
	}
} catch (UserException $e) {}

?>