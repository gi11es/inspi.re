#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Send a reminder email to people who've stopped using the website
 */

require_once(dirname(__FILE__).'/../entities/communitymembershiplist.php');
require_once(dirname(__FILE__).'/../entities/emailcampaign.php');
require_once(dirname(__FILE__).'/../entities/emailcampaignlist.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../templates/emailtemplate.php');
require_once(dirname(__FILE__).'/../utilities/email.php');
require_once(dirname(__FILE__).'/../settings.php');
require_once(dirname(__FILE__).'/../constants.php');

$remindercount = 0;

$activeuserlist = UserList::getActive30Days(false);
$recentlyactiveuserlist = UserList::getActive24Hours(false);

$userlist = array_diff(array_keys($activeuserlist), array_keys($recentlyactiveuserlist));

$emailcampaignlist = array_keys(EmailCampaignList::getByETid($EMAIL_TEMPLATE['NO_COMMUNITY'], false));

$usercache = User::getArray($userlist, false);

foreach ($usercache as $uid => $user) {
	$membershiplist = CommunityMembershipList::getByUid($uid, false);
	
	if (empty($membershiplist) && !in_array($user->getUid(), $emailcampaignlist)) try {
		Email::mail($user->getEmail(), $user->getLid(), 'NO_COMMUNITY', array('username' => $user->getUniqueName()));
		$emailcampaign = new EmailCampaign($user->getUid(), $EMAIL_TEMPLATE['NO_COMMUNITY']);
		$remindercount ++;
	} catch (EmailException $e) {}
}

echo $remindercount.' user(s) have been asked why they aren\'t part of any community by email';

?>