<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Send a private message to another user
*/

require_once(dirname(__FILE__).'/../entities/privatemessage.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userblocklist.php');
require_once(dirname(__FILE__).'/../entities/userlevel.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../utilities/email.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$destination_uid = isset($_REQUEST['destination_uid'])?$_REQUEST['destination_uid']:null;
$text = isset($_REQUEST['text'])?stripslashes($_REQUEST['text']):'';

$mialist = UserLevelList::getByLevel($USER_LEVEL['MIA']);
$miaappealedlist = UserLevelList::getByLevel($USER_LEVEL['MIA_APPEALED']);

$mialist = array_diff($mialist, $miaappealedlist);

if ($destination_uid !== null && $user->getStatus() != $USER_STATUS['UNREGISTERED'] && in_array($destination_uid, $mialist)) {
	try {
		$destination = User::get($destination_uid);
		Email::mail($destination->getEmail(), $destination->getLid(), 
					'MIA_APPEAL', 
					array('username' => $destination->getUniquename(),
						'appealname' => $user->getUniquename(),
						'appealtext' => stripslashes($_REQUEST['text'])
						));
						
		Email::mail('gilles@inspi.re', $destination->getLid(), 
					'MIA_APPEAL', 
					array('username' => $destination->getUniquename(),
						'appealname' => $user->getUniquename(),
						'appealtext' => stripslashes($_REQUEST['text'])
						));
					
		$destination->setAffiliateUid($user->getUid());
		$userlevel = new UserLevel($destination_uid, $USER_LEVEL['MIA_APPEALED']);
	} catch (UserException $e) {}		
	
	header('Location: '.UI::RenderUserLink($destination_uid, true).'-dtrue');
} else header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid());

?>