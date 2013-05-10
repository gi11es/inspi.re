<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Block a user from private messages
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userblock.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');

$user = User::getSessionUser();

$uid = (isset($_REQUEST['uid'])?$_REQUEST['uid']:null);

if ($uid !== null) {
	try {
		$userblock = UserBlock::get($user->getUid(), $uid);
		$userblock->delete();
	} catch (UserBlockException $e) {}
	header('Location: '.UI::RenderUserLink($uid, true).'-vtrue');
} else header('Location: /Members/s3-l'.$user->getLid());

?>