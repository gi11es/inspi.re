#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Send a reminder email to people who've stopped using the website
 */

require_once(dirname(__FILE__).'/../entities/emailcampaign.php');
require_once(dirname(__FILE__).'/../entities/emailcampaignlist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevel.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../templates/emailtemplate.php');
require_once(dirname(__FILE__).'/../utilities/email.php');
require_once(dirname(__FILE__).'/../settings.php');
require_once(dirname(__FILE__).'/../constants.php');

$remindercount = 0;
$miacount = 0;

$activeuserlist = UserList::getByStatus($USER_STATUS['ACTIVE'], false);
$recentlyactiveuserlist = UserList::getActive30Days();

$userlist = array_diff(array_keys($activeuserlist), array_keys($recentlyactiveuserlist));

$emailcampaignlist = array_keys(EmailCampaignList::getByETid($EMAIL_TEMPLATE['REMINDER'], false));

$mialist = UserLevelList::getByLevel($USER_LEVEL['MIA']);

$usercache = User::getArray($userlist, false);

foreach ($usercache as $uid => $user) {
	if (!in_array($user->getUid(), $emailcampaignlist)) {
		$user->setAlertEmail(true);
		Email::mail($user->getEmail(), $user->getLid(), 'REMINDER', array('username' => $user->getUniqueName()));
		$emailcampaign = new EmailCampaign($user->getUid(), $EMAIL_TEMPLATE['REMINDER']);
		$remindercount ++;
	}
	
	if (!in_array($user->getUid(), $mialist) && $user->getUid() != $GOOGLE_UID) {
		$userlevel = new UserLevel($user->getUid(), $USER_LEVEL['MIA']);
		$miacount++;
	}
}

if ($remindercount > 0) echo $remindercount.' user(s) have been reminded about the website by email'."\r\n";
if ($miacount > 0) echo $miacount.' user(s) have been added to the M.I.A. list';

?>