<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Delete an existing team member's membership
*/

require_once(dirname(__FILE__).'/../entities/teammembership.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$levels = UserLevelList::getByUid($user->getUid());

if (isset($_REQUEST['uid']) && in_array($USER_LEVEL['ADMINISTRATOR'], $levels)) {
	$teammembership = TeamMembership::get($_REQUEST['uid']);
	$teammembership->delete();
	header('Location: /Members/s3-u'.$_REQUEST['uid'].'-l'.$user->getLid());
} else header('Location: /Members/s3-l'.$user->getLid());

?>