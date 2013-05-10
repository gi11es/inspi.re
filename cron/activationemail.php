#!/usr/bin/php
<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Receives activation emails and processes them
 */

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlist.php');
require_once(dirname(__FILE__).'/../utilities/email.php');
require_once(dirname(__FILE__).'/../utilities/log.php');
require_once(dirname(__FILE__).'/../utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/../settings.php');
require_once(dirname(__FILE__).'/../constants.php');


$stdin = fopen('php://stdin', 'r');
$whole_mail = '';
while (!feof ($stdin))
{
	$line = trim(fgets($stdin, 4096));
	$whole_mail .= "\r\n".$line;
}

fclose ($stdin); 

preg_match("/^From: (.*)$/im", $whole_mail, $matches1);
$from = isset($matches1[1])?strtolower($matches1[1]):'';
preg_match("/^Sender: (.*)$/im", $whole_mail, $matches2);
$sender = isset($matches2[2])?strtolower($matches2[1]):'';
preg_match("/^To: (.*)@activation\.inspi\.re/im", $whole_mail, $matches3);
$code = isset($matches3[1])?trim(strtolower($matches3[1])):null;

if ($code != null) {
	try {
		$code = PersistentToken::get($code);
	} catch (PersistentTokenException $e) {}

	try {
		$user = User::getByActivationCode($code);
		$usermail = strtolower($user->getEmail());
		if (strstr($from, $usermail) || strstr($sender, $usermail)) {
			$affiliate_uid = $user->getAffiliateUid();
			if ($affiliate_uid !== null) try {
				// Send an alert to the person who told him/her about the website
				$affiliate = User::get($affiliate_uid);
				
				$alert = new Alert($ALERT_TEMPLATE_ID['AFFILIATE_JOIN']);
				$aid = $alert->getAid();
				$alert_variable = new AlertVariable($aid, 'uid', $user->getUid());
				$alert_instance = new AlertInstance($aid, $affiliate_uid, $ALERT_INSTANCE_STATUS['NEW']);
			} catch (UserException $e) {}
			$user->setStatus($USER_STATUS['ACTIVE']);
			$user->setCreationTime(time());
			UserList::addRecentlyRegistered($user->getUid(), $user->getCreationTime(), 14);
			UserList::addRegistered($user->getUid(), time());
			$user->setActivationCode(null);
			Email::mail($user->getEmail(), $user->getLid(), 'ACTIVATED', array());
		}
	} catch (UserException $e) {}
}

?>