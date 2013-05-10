<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Block a user from private messages
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userblock.php');
require_once(dirname(__FILE__).'/../entities/userblocklist.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');

$user = User::getSessionUser();

$uid = (isset($_REQUEST['uid'])?$_REQUEST['uid']:null);

if ($uid !== null) {
	$userblocklist = UserBlockList::getByUid($user->getUid());
	
	if (!in_array($uid, $userblocklist))
		$userblock = new UserBlock($user->getUid(), $uid);

	header('Location: '.UI::RenderUserLink($uid, true).'-ytrue');
} else header('Location: /Members/s3-l'.$user->getLid());

?>