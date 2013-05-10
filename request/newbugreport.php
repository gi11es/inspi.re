<?php

/* 
	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
	
	Sends a bug report to the support email address
*/

require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../constants.php');

$user = User::getSessionUser();

if (isset($_REQUEST['text']) && $user->getStatus() != $USER_STATUS['BANNED']) {
	mail('bugs@inspi.re', 'Bug report '.gmmktime().' from '.$user->getUniqueName(), stripslashes($_REQUEST['text']), 'Reply-To: '.$user->getUniqueName().' <'.$user->getEmail().">\n");
}

?>
0