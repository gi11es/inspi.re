<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Logs out the user from his/her current session
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/log.php');

$user = User::getSessionUser();
Log::xmpp('USER_OFF', $user->getUid());
$user->logout();
?>