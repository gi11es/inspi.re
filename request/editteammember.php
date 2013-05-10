<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Create a new team membership or udpate an existing one
*/

require_once(dirname(__FILE__).'/../entities/teammembership.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/ui.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$title = isset($_REQUEST['title'])?trim(stripslashes($_REQUEST['title'])):'';
$uid = isset($_REQUEST['uid'])?$_REQUEST['uid']:false;

$levels = UserLevelList::getByUid($user->getUid());

if ($uid !== false && in_array($USER_LEVEL['ADMINISTRATOR'], $levels)) {
	try {
		$membership = TeamMembership::get($uid);
		$membership->setTitle($title);
	} catch (TeamMembershipException $e) {
		$membership = new TeamMembership($uid, $title);
	}
	header('Location: '.UI::RenderUserLink($uid));
} else header('Location: /Members/s3-l'.$user->getLid());

?>