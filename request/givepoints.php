<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Returns the current amount of points for the user
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');

$user = User::getSessionUser();

$uid = (isset($_REQUEST['uid'])?$_REQUEST['uid']:null);
$points = (isset($_REQUEST['points'])?intval($_REQUEST['points']):0);

$levels = UserLevelList::getByUid($user->getUid());

if ($uid === null || !in_array($USER_LEVEL['ADMINISTRATOR'], $levels)) {
	header('Location: /Members/s3-l'.$user->getLid());
	exit(0);
}

$member = User::get($uid);

$member->givePoints($points);

header('Location: '.UI::RenderUserLink($uid));

?>