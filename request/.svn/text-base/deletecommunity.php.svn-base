<?php

set_time_limit(3600);

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Deletes an existing community
*/

require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');

$user = User::getSessionUser();
$levels = UserLevelList::getByUid($user->getUid());
$isadmin = in_array($USER_LEVEL['ADMINISTRATOR'], $levels);

$xid = isset($_REQUEST['xid'])?$_REQUEST['xid']:null;

if ($xid !== null) {
	// We're updating an existing community
	try {
		$community = Community::get($xid);
	} catch (CommunityException $e) {
		header('Location: '.$PAGE['COMMUNITIES'].'?lid='.$user->getLid());
		exit(0);
	}
	if ($community->getUid() == $user->getUid() || $isadmin) { // Check that this is not a forged request
		$user->givePoints($community->getDeletionPoints());
		$community->delete();
		
		header('Location: '.$PAGE['COMMUNITIES'].'?lid='.$user->getLid());
	} else header('Location: '.$PAGE['COMMUNITY'].'?lid='.$user->getLid().'&xid='.$xid);
} else header('Location: '.$PAGE['COMMUNITIES'].'?lid='.$user->getLid());

?>