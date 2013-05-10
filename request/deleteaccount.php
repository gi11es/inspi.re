<?php

set_time_limit(3600);

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Deletes an existing user account
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../utilities/token.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$levels = UserLevelList::getByUid($user->getUid());

if (in_array($USER_LEVEL['ADMINISTRATOR'], $levels)) {
	if (!isset($_REQUEST['uid'])) {
		echo('Location: '.$PAGE['INDEX']);
		exit(0);
	}
	
	$uid = $_REQUEST['uid'];
	$token = $uid;
} else {
	$uid = $user->getUid();
	$hash = isset($_REQUEST['token'])?$_REQUEST['token']:null;
	$token = Token::get($hash);
}

try {
	$member = User::get($uid);
} catch (UserException $e) {
	echo('Location: '.$PAGE['INDEX']);
	exit(0);
}


if ($token != $uid || $member->getStatus() == $USER_STATUS['UNREGISTERED']) {
	echo('Location: '.$PAGE['INDEX']);
	exit(0);
}

$member->delete();
$member->logout();

?>