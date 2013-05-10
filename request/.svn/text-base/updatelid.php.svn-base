<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Updates the language preference of a given user
*/

require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../entities/user.php');

if (isset($_REQUEST['lid'])) {
	$user = User::getSessionUser();
	
	$user->setLid($_REQUEST['lid']);
	echo '0';
}

?>