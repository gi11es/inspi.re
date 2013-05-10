<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Stops pretending to be another user
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser(false);
$user->setImpersonatedUid(null);

header('Location: '.$PAGE['HOME'].'?lid='.$user->getLid());
?>