<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Send a private message to another user
*/

require_once(dirname(__FILE__).'/../entities/privatemessage.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userblocklist.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../utilities/email.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$destination_uid = isset($_REQUEST['destination_uid'])?$_REQUEST['destination_uid']:null;
$title = isset($_REQUEST['title'])?stripslashes($_REQUEST['title']):'';
$text = isset($_REQUEST['text'])?stripslashes($_REQUEST['text']):'';
$home = isset($_REQUEST['home'])?strcasecmp($_REQUEST['home'], 'true') == 0:false;

$blockedlist = UserBlockList::getByUid($destination_uid);

if ($destination_uid !== null && $user->getUid() != $destination_uid) {
	if ($user->getStatus() != $USER_STATUS['ACTIVE'] || in_array($user->getUid(), $blockedlist))
		$status = $PRIVATE_MESSAGE_STATUS['BLOCKED'];
	else
		$status = $PRIVATE_MESSAGE_STATUS['NEW'];
		
	$privatemessage = new PrivateMessage($user->getUid(), $destination_uid, $title, $text, $status);
	
	$levels = UserLevelList::getByUid($destination_uid);
	if (in_array($USER_LEVEL['ADMINISTRATOR'], $levels))
		mail('gilles@inspi.re', $title, $text, 'From: '.$user->getUniqueName().' <'.$user->getEmail().'>'."\r\n");
	
	if ($home)
		header('Location: '.$PAGE['OUTBOX'].'?lid='.$user->getLid().'&successpm=true');
	else
		header('Location: '.UI::RenderUserLink($destination_uid, true).'-wtrue');
} else header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid());

?>