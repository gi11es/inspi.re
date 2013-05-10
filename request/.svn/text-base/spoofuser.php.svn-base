<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Logs out the user from his/her current session
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../entities/userlevellist.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

$levels = UserLevelList::getByUid($user->getUid());

if (in_array($USER_LEVEL['ADMINISTRATOR'], $levels) && isset($_REQUEST['uid']))
	$user->setImpersonatedUid($_REQUEST['uid']);
	
header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid());
?>