<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Adds a user to the list of donators
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevel.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');

$user = User::getSessionUser();

$uid = (isset($_REQUEST['uid'])?$_REQUEST['uid']:null);

$levels = UserLevelList::getByUid($user->getUid());

if ($uid === null || !in_array($USER_LEVEL['ADMINISTRATOR'], $levels)) {
	header('Location: /Members/s3-l'.$user->getLid());
	exit(0);
}

$levels = UserLevelList::getByUid($uid);
if (!in_array($USER_LEVEL['DONATOR'], $levels))
	$level = new UserLevel($uid, $USER_LEVEL['DONATOR']);

header('Location: '.UI::RenderUserLink($uid));

?>