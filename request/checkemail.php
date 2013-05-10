<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Checks if the specified email address has already been registered
*/

require_once(dirname(__FILE__).'/../entities/user.php');

if (isset($_REQUEST['email'])) {

	try {
		$user = User::getByEmail(trim(mb_strtolower($_REQUEST['email'])));
		if ($user->getStatus() != $USER_STATUS['UNREGISTERED'])
			echo 'unavailable '.$_REQUEST['email'];
		else
			echo 'available '.$_REQUEST['email'];
	} catch (UserException $e) {
		echo 'available '.$_REQUEST['email'];
	}
}

?>