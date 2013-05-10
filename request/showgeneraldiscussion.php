<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Changes a user's preference to see the general discussion board in the recent activity
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');
	
$user = User::getSessionUser();
$user->setDisplayGeneralDiscussion(true);

header('Location: '.$PAGE['DISCUSS'].'?lid='.$user->getLid());

?>